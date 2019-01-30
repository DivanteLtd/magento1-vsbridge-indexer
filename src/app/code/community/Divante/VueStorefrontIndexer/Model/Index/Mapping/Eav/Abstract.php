<?php

use Mage_Eav_Model_Entity_Attribute as Attribute;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
abstract class Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav
     */
    private $resourceModel;

    /**
     * @return string
     */
    abstract public function getAttributeResourceModel();

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        $this->resourceModel = Mage::getResourceModel($this->getAttributeResourceModel());

        return $this->resourceModel->getAttributesById();
    }

    /**
     * Return mapping for an attribute.
     *
     * @param Attribute $attribute Attribute we want the mapping for.
     *
     * @return array
     */
    public function getAttributeMapping(Attribute $attribute)
    {
        $mapping = [];

        $attributeCode = $attribute->getAttributeCode();

        $type = $this->getAttributeType($attribute);

        if ($type === 'text' && !$attribute->getBackendModel() && $attribute->getFrontendInput() != 'media_image' && $attribute->getAttributeCode() != 'description') {
            $fieldName = $attributeCode;
            $mapping[$fieldName] = [
                'type' => $type,
                'fielddata' => true,
                'analyzer' => "autocomplete",
                'search_analyzer'=> "autocomplete_search",
                'fields' => [
                    'keyword' => [
                        'type' => FieldInterface::TYPE_KEYWORD,
                        'ignore_above' => 256,
                    ]
                ]
            ];
        } 
        else if ($type === 'text' && $attribute->getAttributeCode() != 'image' && $attribute->getAttributeCode() != 'description') {
            $fieldName = $attributeCode;
            $mapping[$fieldName] = [
                'type' => $type,
                'fielddata' => true
            ];
        }
        else if ($type === 'date') {
            $mapping[$attributeCode] = [
                'type' => $type,
                'format' => implode(
                    '||',
                    [
                        Varien_Date::DATETIME_INTERNAL_FORMAT,
                        Varien_Date::DATE_INTERNAL_FORMAT,
                    ]
                )
            ];
        } else {
            $mapping[$attributeCode] = ['type' => $type];
        }
        return $mapping;
    }

    /**
     * Returns attribute type for indexation.
     *
     * @param Attribute $attribute
     *
     * @return string
     */
    public function getAttributeType(Attribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();

        if ('visibility' === $attributeCode) {
            return FieldInterface::TYPE_INT;
        }

        if ('status' === $attributeCode) {
            return FieldInterface::TYPE_INT;
        }

        if (('select' === $attribute->getFrontendInput() || 'multiselect' === $attribute->getFrontendInput()) && 'eav/entity_attribute_source_boolean' !== $attribute->getSourceModel()) {
            return FieldInterface::TYPE_TEXT;
        }

        if ('category_ids' === $attributeCode) {
            return FieldInterface::TYPE_LONG;
        }

        if ('sku' === $attributeCode) {
            return FieldInterface::TYPE_KEYWORD;
        }

        if ('level' === $attributeCode) {
            return FieldInterface::TYPE_LONG;
        }

        if (strstr($attributeCode, 'is_')) {
            return FieldInterface::TYPE_BOOLEAN;
        }

        $type = FieldInterface::TYPE_TEXT;

        if ($attribute->getBackendType() == 'int' || $attribute->getFrontendClass() == 'validate-digits') {
            $type = FieldInterface::TYPE_LONG;
        } elseif ($attribute->getBackendType() == 'decimal' || $attribute->getFrontendClass() == 'validate-number') {
            $type = FieldInterface::TYPE_DOUBLE;
        } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
            $type = FieldInterface::TYPE_BOOLEAN;
        } elseif ($attribute->getBackendType() == 'datetime') {
            $type = FieldInterface::TYPE_DATE;
        } elseif ($attribute->usesSource()) {
            $type = $attribute->getSourceModel() ? FieldInterface::TYPE_KEYWORD : FieldInterface::TYPE_LONG;
        }

        return $type;
    }
}
