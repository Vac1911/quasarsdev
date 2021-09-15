<?php

namespace App\Command;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;

class EntityCrudCommand extends AbstractMaker
{
    public ClassNameDetails $entityClassDetails;
    public ?EntityDetails $entityDoctrineDetails;
    public ClassNameDetails $controllerClassDetails;
    public string $routeName;
    public string $templatesPath;

    protected Input $input;
    protected Inflector $inflector;
    protected PropertyInfoExtractor $propInfo;

    private DoctrineHelper $doctrineHelper;
    private FileManager $fileManager;
    private EntityManagerInterface $em;

    public function __construct(DoctrineHelper $doctrineHelper, FileManager $fileManager, EntityManagerInterface $em)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->inflector = InflectorFactory::create()->build();

        // a full list of extractors is shown further below
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        // list of PropertyListExtractorInterface (any iterable)
        $listExtractors = [$reflectionExtractor];

        // list of PropertyTypeExtractorInterface (any iterable)
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];

        // list of PropertyDescriptionExtractorInterface (any iterable)
        $descriptionExtractors = [$phpDocExtractor];

        // list of PropertyAccessExtractorInterface (any iterable)
        $accessExtractors = [$reflectionExtractor];

        // list of PropertyInitializableExtractorInterface (any iterable)
        $propertyInitializableExtractors = [$reflectionExtractor];

        $this->propInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );
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
        $this->entityClassDetails = $generator->createClassNameDetails(
            $input->getArgument('entity-class'),
            'Entity\\'
        );
        $this->controllerClassDetails = $generator->createClassNameDetails(
            $this->entityClassDetails->getShortName(),
            'Controller\\',
            'Controller'
        );

        $reader = new AnnotationReader();
        $this->entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($this->entityClassDetails->getFullName());

        $this->routeName = Str::asRouteName($this->controllerClassDetails->getRelativeNameWithoutSuffix());
        $this->templatesPath = Str::asFilePath($this->controllerClassDetails->getRelativeNameWithoutSuffix());


        $templates = ['create', 'edit'];
        foreach ($templates as $template) {
            $this->generateTemplate($generator, $template, $vars);
        }
        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        return Command::SUCCESS;
    }

    public function generateTemplate(Generator $generator, string $template, $vars)
    {
        $twigPath = $this->templatesPath . '/' . $template . '.twig';
        $targetPath = $this->fileManager->getPathForTemplate($twigPath);
        if($this->fileManager->fileExists($targetPath)) {
            if($this->input->getOption('overwrite')) {

            }
            else {
                return;
            }
        }
        $generator->generateTemplate(
            $twigPath,
            'src/Command/stubs/' . $template . '.stub.php',
            $vars
        );
    }

    public function getFormFields(): array
    {
        $formFields = array_keys($this->entityDoctrineDetails->getFormFields());
        foreach ($formFields as $i => $field) {
            $formFields[$i] = $this->parseStub(
                'src/Command/stubs/input/text.stub.php',
                $this->entityDoctrineDetails->getDisplayFields()[$field]
            );
        }
        return $formFields;
    }

    public function parseStub(string $templatePath, array $parameters): string
    {
        ob_start();
        extract($parameters, \EXTR_SKIP);
        include $templatePath;

        return ob_get_clean();
    }

    public function getVars(): array
    {
        $entityVarPlural = lcfirst($this->inflector->pluralize($this->entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->inflector->singularize($this->entityClassDetails->getShortName()));
        return [
            'entity_class_name'     => $this->entityClassDetails->getShortName(),
            'entityVarPlural'       => $entityVarPlural,
            'entityVarSingular'     => $entityVarSingular,
            'entityTwigVarPlural'   => Str::asTwigVariable($entityVarPlural),
            'entityTwigVarSingular' => Str::asTwigVariable($entityVarSingular),
            'entity_identifier'     => $this->entityDoctrineDetails->getIdentifier(),
            'entity_fields'         => $this->entityDoctrineDetails->getDisplayFields(),
            'route_name'            => $this->routeName,
            'form_fields'           => $this->getFormFields(),
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
