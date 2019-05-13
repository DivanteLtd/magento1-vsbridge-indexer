<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Cms_Page
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Sven Ehmer <sven.ehmer@gastro-hero.de>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Cms_Page implements MappingInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        $properties = [
            'id' => ['type' => FieldInterface::TYPE_LONG],
            'content' => ['type' => FieldInterface::TYPE_TEXT],
            'is_active' => ['type' => FieldInterface::TYPE_BOOLEAN],
            'title' => ['type' => FieldInterface::TYPE_TEXT],
            'identifier' => ['type' => FieldInterface::TYPE_KEYWORD]
        ];

        $mapping = ['properties' => $properties];

        $mappingObject = new Varien_Object($mapping);
        Mage::dispatchEvent('elasticsearch_cms_page_mapping_properties', ['mapping' => $mappingObject]);

        return $mappingObject->getData();
    }
}
