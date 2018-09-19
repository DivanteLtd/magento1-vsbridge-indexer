<?php

use Mage_Eav_Model_Entity_Attribute as Attribute;

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

        if ($type === 'string' && !$attribute->getBackendModel() && $attribute->getFrontendInput() != 'media_image') {
            $fieldName = $attributeCode;
            $mapping[$fieldName] = ['type' => $type];
        } else if ($type === 'date') {
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

        if ('sku' === $attributeCode) {
            return 'keyword';
        }

        if ('level' === $attributeCode) {
            return 'long';
        }

        if (strstr($attributeCode, 'is_')) {
            return 'boolean';
        }

        $type = 'string';

        if ($attribute->getBackendType() == 'int' || $attribute->getFrontendClass() == 'validate-digits') {
            $type = 'long';
        } elseif ($attribute->getBackendType() == 'decimal' || $attribute->getFrontendClass() == 'validate-number') {
            $type = 'double';
        } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
            $type = 'boolean';
        } elseif ($attribute->getBackendType() == 'datetime') {
            $type = 'date';
        } elseif ($attribute->usesSource()) {
            $type = $attribute->getSourceModel() ? 'keyword' : 'long' ;
        }

        return $type;
    }
}
