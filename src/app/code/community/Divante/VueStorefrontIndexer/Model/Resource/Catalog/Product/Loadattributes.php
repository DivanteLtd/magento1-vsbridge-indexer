<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Loadattributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Loadattributes
{
    /**
     * @var array
     */
    private $attributesById;

    /**
     * Mapping attribute code to id
     * @var array
     */
    private $attributeCodeToId = [];

    /**
     * @return array
     */
    public function execute()
    {
        return $this->initAttributes();
    }

    /**
     * @return array
     */
    private function initAttributes()
    {
        if (null === $this->attributesById) {
            $this->attributesById = [];
            $entityType = $this->getEntityType();

            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setEntityTypeFilter($entityType->getEntityTypeId());

            /** @var  $attribute */
            foreach ($attributes as $attribute) {
                $this->attributesById[$attribute->getAttributeId()] = $attribute;
                $this->attributeCodeToId[$attribute->getAttributeCode()] = $attribute->getId();
            }
        }

        return $this->attributesById;
    }

    /**
     * @param int $attributeId
     *
     * @return Attribute
     */
    public function getAttributeById($attributeId)
    {
        $this->initAttributes();

        if (isset($this->attributesById[$attributeId])) {
            return $this->attributesById[$attributeId];
        }

        throw new Mage_Exception(__('Attribute not found.'));    }

    /**
     * @param string $attributeCode
     *
     * @return Attribute
     */
    public function getAttributeByCode($attributeCode)
    {
        $this->initAttributes();

        if (isset($this->attributeCodeToId[$attributeCode])) {
            $attributeId = $this->attributeCodeToId[$attributeCode];

            return $this->attributesById[$attributeId];
        }

        throw new Mage_Exception(__('Attribute not found.'));
    }

    /**
     * @return Mage_Eav_Model_Entity_Type
     */
    private function getEntityType()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');

        return $entityType;
    }
}
