<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract as AbstractMapping;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Product extends AbstractMapping implements MappingInterface
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
     * @return string
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
        return 'vsf_indexer/catalog_product_attributes';
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        if (null === $this->properties) {
            $attributes = $this->getAttributes();
            $attributesMapping  = [];

            foreach ($attributes as $attribute) {
                $attributesMapping = array_merge($attributesMapping, $this->getAttributeMapping($attribute));
            }

            $attributesMapping['media_gallery'] = [
                'properties' => [
                    'type' => ['type' => 'text'],
                    'image' => ['type' => 'text'],
                    'lab' => ['type' => 'text'],
                    'pos' => ['type' => 'text'],
                    'disabled' => ['type' => 'boolean'],
                ]
            ];

            $properties = [
                'bundle_options' => [
                    'properties' => [
                        'option_id' => ['type' => 'long'],
                        'position' => ['type' => 'long'],
                        'sku' => ['type' => 'keyword'],
                        'product_links' => [
                            'properties' => [
                                'id' => ['type' => 'long'],
                                'is_default' => ['type' => 'boolean'],
                                'qty' => ['type' => 'long'],
                                'can_change_quantity' => ['type' => 'boolean'],
                                'price' => ['type' => 'long'],
                                'price_type' => ['type' => 'string'],
                                'position' => ['type' => 'long'],
                                'sku' => ['type' => 'keyword'],
                            ]
                        ],
                    ]
                ],
                'product_links' => [
                    'properties' => [
                        'linked_product_type' => ['type' => 'text'],
                        'linked_product_sku' => ['type' => 'keyword'],
                        'sku' => ['type' => 'keyword'],
                        'position' => ['type' => 'long'],
                    ]
                ],
                'configurable_options' => [
                    'properties' => [
                        'label' => ['type' => 'text'],
                        'id' => ['type' => 'long'],
                        'product_id' => ['type' => 'long'],
                        'attribute_code' => ['type' => 'text'],
                        'attribute_id' => ['type' => 'long'],
                        'position' => ['type' => 'text'],
                        'values' => [
                            'properties' => [
                                'value_index' => ['type' => 'keyword'],
                            ]
                        ],
                    ],
                ],
                'category' => [
                    'properties' => [
                        'category_id' => ['type' => 'long'],
                        'name' => ['type' => 'text'],
                    ]
                ],
                'configurable_children' => ['properties' => $attributesMapping]
            ];

            $properties = array_merge($properties, $attributesMapping);

            /**
             * @var $generalMapping GeneralMapping
             */
            $generalMapping = Mage::getModel('vsf_indexer/index_mapping_generalmapping');
            $properties = array_merge($properties, $generalMapping->getCommonProperties());

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_product_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }
}
