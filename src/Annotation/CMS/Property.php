<?php

namespace App\Annotation\CMS;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("listField", type="boolean"),
 *   @Attribute("listable", type="boolean"),
 *   @Attribute("viewable", type="boolean"),
 *   @Attribute("editable", type="boolean"),
 *   @Attribute("searchable", type="boolean"),
 *   @Attribute("required", type="boolean"),
 *   @Attribute("joined", type="boolean"),
 *   @Attribute("inputType", type="string"),
 *   @Attribute("label", type="string")
 * })
 */
class Property {

    public bool $listField = false;
    public bool $listable = true;
    public bool $viewable = true;
    public bool $editable = true;
    public bool $searchable = true;
    public bool $required = false;
    public bool $joined = false;

    /**
     * @Enum({"text", "textarea", "editor", "integer", "decimal", "select", "checkbox", "radio", "date", "datetime", "subtable", "custom"})
     */
    public string $inputType;

    public string $label;

}