<?php

namespace App\Maker;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;
use Qis\Annotation\CMS\Property;
use Qis\Collections\Collection;

class EntityDetails
{

    /**
     * @param ClassMetadata $metadata
     */
    public function __construct(private ClassMetadata $metadata)
    {
    }

    public function getRepositoryClass()
    {
        return $this->metadata->customRepositoryClassName;
    }

    public function getIdentifier()
    {
        return $this->metadata->identifier[0];
    }

    public function getProps()
    {
        $reader = new AnnotationReader();
        return collect($this->metadata->fieldMappings)->map(function ($mapping) use ($reader) {
            $refl = $this->metadata->getReflectionProperty($mapping['fieldName']);
            $cms = $reader->getPropertyAnnotation($refl, Property::class);
            return (object) compact('mapping', 'refl', 'cms');
        });
    }

    /**
     * @param string $action list|view|edit|create|search
     * @return Collection
     */
    public function getCmsProps(string $action): Collection
    {
        return $this->getProps()->filter(fn($prop) => $prop['cms'] && $prop['cms']->{$action . 'able'});
    }
}
