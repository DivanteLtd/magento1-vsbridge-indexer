<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;

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
            'id' => ['type' => 'long'],
            'content' => ['type' => 'text'],
            'is_active' => ['type' => 'boolean'],
            'title' => ['type' => 'text'],
            'identifier' => [
                'type' => 'text',
                'fields' => ['keyword' => ['type' => 'keyword']]
            ]
        ];

        $mapping = ['properties' => $properties];

        $mappingObject = new Varien_Object($mapping);
        Mage::dispatchEvent('elasticsearch_cms_block_mapping_properties', ['mapping' => $mappingObject]);

        return $mappingObject->getData();
    }
}
