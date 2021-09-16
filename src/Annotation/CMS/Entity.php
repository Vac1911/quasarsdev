<?php

namespace App\Annotation\CMS;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("entityLabel", type="string"),
 *   @Attribute("recordLabel", type="string"),
 *   @Attribute("recordSummary", type="string"),
 *   @Attribute("listFields", type="array"),
 *   @Attribute("labelFields", type="array"),
 *   @Attribute("labelTemplate", type="string"),
 *   @Attribute("defaultSortFields", type="array"),
 *   @Attribute("defaultSortField", type="string"),
 *   @Attribute("defaultSortDirection", type="string"),
 * })
 */
class Entity {

 public string $entityLabel;
 public string $recordLabel;
 public string $recordSummary;
 public array $listFields = [];
 public array $labelFields = [];
 public ?string $labelTemplate = null;
 public array $defaultSortFields = [];
 public ?string $defaultSortField = null;
 public string $defaultSortDirection = "ASC";
}