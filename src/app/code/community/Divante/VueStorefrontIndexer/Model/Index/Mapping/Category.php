<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract as AbstractMapping;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Category extends AbstractMapping implements MappingInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $properties;

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeResourceModel()
    {
        return 'vsf_indexer/catalog_category_attributes';
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        if (null === $this->properties) {
            $properties = [];
            $attributes = $this->getAttributes();

            foreach ($attributes as $attribute) {
                $properties = array_merge($properties, $this->getAttributeMapping($attribute));
            }

            /**
             * @var $generalMapping GeneralMapping
             */
            $generalMapping = Mage::getSingleton('vsf_indexer/index_mapping_generalmapping');
            $properties = array_merge($properties, $generalMapping->getCommonProperties());

            $properties['children_count'] = ['type' => FieldInterface::TYPE_LONG];
            $properties['children_data'] = [
                'properties' => $properties
            ];

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_category_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }
}
