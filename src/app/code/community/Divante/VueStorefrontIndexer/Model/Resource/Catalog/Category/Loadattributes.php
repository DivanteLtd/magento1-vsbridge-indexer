<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Loadattributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Loadattributes
{
    /**
     * @var array
     */
    private $attributesById;

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

            $attributes = Mage::getResourceModel('catalog/category_attribute_collection')
                ->setEntityTypeFilter($entityType->getEntityTypeId());

            /** @var  $attribute */
            foreach ($attributes as $attribute) {
                $this->attributesById[$attribute->getAttributeId()] = $attribute;
            }
        }

        return $this->attributesById;
    }

    /**
     * @return Mage_Eav_Model_Entity_Type
     */
    private function getEntityType()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_category');

        return $entityType;
    }
}
