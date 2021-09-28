<?php

namespace App\Maker;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;
use Qis\Orm\Annotation\CMS\Property;
use Qis\Collections\Collection;
use ReflectionProperty;
use function Symfony\Component\String\u;

class PropertyDetails
{
    public array $mapping;
    protected ?ReflectionProperty $reflection;
    protected ?Property $cmsAnnotation;
    protected AnnotationReader $reader;

    /**
     * @param string $propName
     * @param ClassMetadata $metadata
     */
    public function __construct(
        public string $propName,
        protected ClassMetadata $metadata
    )
    {
        $this->reader = new AnnotationReader();
        $this->mapping = $this->metadata->fieldMappings[$this->propName];
        $this->reflection = $this->metadata->getReflectionProperty($this->propName);
        $this->cmsAnnotation = $this->reader->getPropertyAnnotation($this->reflection, Property::class);
    }

    public function getCmsType(): string
    {
        $type = $this->getReflection()->getType()?->getName() ??
            $this->reader->getPropertyAnnotation($this->reflection, 'var') ??
            $this->mapping['type'];

        $type = u($type)->snake()->toString();

        return match($type) {
            'string' => 'string',
            'text' => 'text',
            'int', 'integer', 'float', 'decimal' => 'number',
            'datetime', 'date_time', 'date_time_immutable' => 'datetime',
            default => $type
        };
    }

    /**
     * @return ?Property
     */
    public function getCmsAnnotation(): ?Property
    {
        return $this->cmsAnnotation;
    }

    public function supportsAction(string $action): bool
    {
        if($this->cmsAnnotation)
            return $this->cmsAnnotation->supportsAction($action);
        else
            return false;
    }

    /**
     * @return ?ReflectionProperty
     */
    public function getReflection(): ?ReflectionProperty
    {
        return $this->reflection;
    }
}
