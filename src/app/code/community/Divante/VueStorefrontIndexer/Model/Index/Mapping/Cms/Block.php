<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Attribute
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Cms_Block implements MappingInterface
{
    /**
     * @var string
     */
    private $type;

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
            'identifier' => [
                'type' => FieldInterface::TYPE_TEXT,
                'fields' => ['keyword' => ['type' => FieldInterface::TYPE_KEYWORD]]
            ]
        ];

        $mapping = ['properties' => $properties];

        $mappingObject = new Varien_Object($mapping);
        Mage::dispatchEvent('elasticsearch_cms_block_mapping_properties', ['mapping' => $mappingObject]);

        return $mappingObject->getData();
    }
}
