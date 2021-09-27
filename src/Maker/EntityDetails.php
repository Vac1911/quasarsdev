<?php

namespace App\Maker;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;
use Qis\Orm\Annotation\CMS\Property;
use Qis\Collections\Collection;

class EntityDetails
{

    /**
     * @param ClassMetadata $metadata
     */
    public function __construct(private ClassMetadata $metadata)
    {
    }

    public function getRepositoryClass(): ?string
    {
        return $this->metadata->customRepositoryClassName;
    }

    public function getIdentifier(): string
    {
        return $this->metadata->identifier[0];
    }

    public function getProps(): Collection
    {
        return collect($this->metadata->fieldMappings)->map(fn($mapping) => new PropertyDetails($mapping['fieldName'], $this->metadata));
    }
}
