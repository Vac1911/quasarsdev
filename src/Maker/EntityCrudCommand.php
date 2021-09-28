<?php

namespace App\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Collections\Collection;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use function Symfony\Component\String\u;

class EntityCrudCommand extends AbstractMaker
{
    public ClassNameDetails $entityClassDetails;
    public EntityDetails $entityDetails;
    public ClassNameDetails $controllerClassDetails;
    public array $vars;

    protected InputInterface $input;
    protected ConsoleStyle $io;
    protected Inflector $inflector;
    protected PropertyInfoExtractor $propInfo;

    private DoctrineHelper $doctrineHelper;
    private FileManager $fileManager;
    private EntityManagerInterface $em;

    const STUB_DIR = 'stubs/';

    const ACTION_MAP = [
        'list' => [
            'stub' => 'index.stub.php',
            'output' => 'index.twig'
        ],
        'create' => [
            'stub' => 'create.stub.php',
            'output' => 'create.twig'
        ],
        'edit' => [
            'stub' => 'edit.stub.php',
            'output' => 'edit.twig'
        ],
        'view' => [
            'stub' => 'show.stub.php',
            'output' => 'show.twig'
        ],
    ];

    public function __construct(DoctrineHelper $doctrineHelper, FileManager $fileManager, EntityManagerInterface $em)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->inflector = InflectorFactory::create()->build();
    }

    public static function getCommandName(): string
    {
        return 'make:views';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates the base views for an entity';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('entity-class', InputArgument::REQUIRED, sprintf('The class name of the entity to create CRUD'))
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, sprintf('Overwrite exiting views'));

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $this->input = $input;
        $this->io = $io;
        $this->entityClassDetails = $generator->createClassNameDetails(
            $input->getArgument('entity-class'),
            'Entity\\'
        );
        $this->controllerClassDetails = $generator->createClassNameDetails(
            $this->entityClassDetails->getShortName(),
            'Controller\\',
            'Controller'
        );

        $this->entityDetails = new EntityDetails($this->doctrineHelper->getMetadata($this->entityClassDetails->getFullName()));
        $this->buildVars();

        foreach (collect(self::ACTION_MAP)->keys() as $action) {
            $this->generateTemplate($generator, $action);
        }
        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        return Command::SUCCESS;
    }

    public function generateTemplate(Generator $generator, string $action)
    {
        $outputPath = $this->vars['templates_path'] . '/' . self::ACTION_MAP[$action]['output'];

        $this->checkPath($this->fileManager->getPathForTemplate($outputPath));

        $params = collect($this->vars)->merge([
            'fields' => $this->renderFields($action),
        ])->toArray();

        $generator->generateTemplate(
            $outputPath,
            self::STUB_DIR . self::ACTION_MAP[$action]['stub'],
            $params
        );
    }

    protected function checkPath(string $targetPath)
    {
        if ($this->fileManager->fileExists($targetPath)) {
            if ($this->input->getOption('overwrite')) {
                $filesystem = new Filesystem();
                $filesystem->remove($this->fileManager->absolutizePath($targetPath));
                $this->io->warning('removed: ' . $this->fileManager->absolutizePath($targetPath));
            } else {
                throw new RuntimeCommandException(sprintf('The file "%s" can\'t be generated because it already exists.', $targetPath));
            }
        }
    }

    public function renderFields(string $action): Collection
    {
        $fields = collect();
        $props = $this->entityDetails->getProps();

        foreach ($props as $prop) {
            $prop->display = $this->renderField($prop, $action);
            if (!is_null($prop->display)) {
                $fields->push($prop);
            }
        }


        return $fields;
    }

    public function renderField($prop, string $action): ?string
    {
        if (!$prop->getCmsAnnotation()) return null;

        $supportedAction = $prop->supportsAction($action);

        if (!$supportedAction) {
            if ($action == 'edit' && $prop->supportsAction('view')) $action = 'view';
            else return null;
        }

        $file = match ($action) {
            'create', 'edit' => 'input',
            'view', 'list' => 'display',
        };
        $type = $prop->getCmsType();

        return $this->parseStub(sprintf('%sfield/%s/%s.stub.php', self::STUB_DIR, $type, $file), $prop->mapping, compact('action'));
    }

    public function parseStub(string $templatePath, array $mapping, array $extraVars = []): string
    {
        $params = collect($mapping)->merge($extraVars)->merge($this->vars)->toArray();
        ob_start();
        extract($params, \EXTR_SKIP);
        include $templatePath;

        return ob_get_clean();
    }

    public function buildVars()
    {
        $shortName = $this->entityClassDetails->getShortName();
        $entityVarCamelSingular = u($this->inflector->singularize($shortName))->camel();
        $entityVarCamelPlural = u($this->inflector->pluralize($shortName))->camel();
        $this->vars = [
            'entity_class_name'      => $this->entityClassDetails->getShortName(),
            'entityVarCamelSingular' => $entityVarCamelSingular,
            'entityVarCamelPlural'   => $entityVarCamelPlural,
            'entityVarSnakeSingular' => $entityVarCamelSingular->snake(),
            'entityVarSnakePlural'   => $entityVarCamelPlural->snake(),
            'entity_identifier'      => $this->entityDetails->getIdentifier(),
            'route_name'             => Str::asRouteName($this->controllerClassDetails->getRelativeNameWithoutSuffix()),
            'templates_path'         => Str::asFilePath($this->controllerClassDetails->getRelativeNameWithoutSuffix())
        ];

    }

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );
    }
}
