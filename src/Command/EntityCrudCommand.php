<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class EntityCrudCommand extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'entity:crud';
    }
    
    public static function getCommandDescription(): string
    {
        return 'Creates the base views for an entity';
    }
    
    public function getPropExtractor() {
        if(!isset($this->propertyInfo)) {
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
            
            $this->propertyInfo = new PropertyInfoExtractor(
                $listExtractors,
                $typeExtractors,
                $descriptionExtractors,
                $accessExtractors,
                $propertyInitializableExtractors
            );
        }
        
        return $this->propertyInfo;
    }


    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('entity-class', InputArgument::REQUIRED, sprintf('The class name of the entity to create CRUD'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );
        
        dd($entityClassDetails);
        
        $props = $this->getPropExtractor()->getProperties($entityClass);
        
        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));

        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);
        
        dd($props, $entityTwigVarPlural, $entityTwigVarSingular);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

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

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }
}
