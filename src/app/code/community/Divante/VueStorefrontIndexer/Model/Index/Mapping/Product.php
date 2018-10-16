<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
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
                    'type' => ['type' => FieldInterface::TYPE_TEXT],
                    'image' => ['type' => FieldInterface::TYPE_TEXT],
                    'lab' => ['type' => FieldInterface::TYPE_TEXT],
                    'pos' => ['type' => FieldInterface::TYPE_TEXT],
                ]
            ];

            $properties = [
                'bundle_options' => [
                    'properties' => [
                        'option_id' => ['type' => FieldInterface::TYPE_LONG],
                        'position' => ['type' => FieldInterface::TYPE_LONG],
                        'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                        'product_links' => [
                            'properties' => [
                                'id' => ['type' => FieldInterface::TYPE_LONG],
                                'is_default' => ['type' => FieldInterface::TYPE_BOOLEAN],
                                'qty' => ['type' => FieldInterface::TYPE_LONG],
                                'can_change_quantity' => ['type' => FieldInterface::TYPE_BOOLEAN],
                                'price' => ['type' => FieldInterface::TYPE_LONG],
                                'price_type' => ['type' => FieldInterface::TYPE_TEXT],
                                'position' => ['type' => FieldInterface::TYPE_LONG],
                                'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                            ]
                        ],
                    ]
                ],
                'product_links' => [
                    'properties' => [
                        'linked_product_type' => ['type' => FieldInterface::TYPE_TEXT],
                        'linked_product_sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                        'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                        'position' => ['type' => FieldInterface::TYPE_LONG],
                    ]
                ],
                'configurable_options' => [
                    'properties' => [
                        'label' => ['type' => FieldInterface::TYPE_TEXT],
                        'id' => ['type' => FieldInterface::TYPE_LONG],
                        'product_id' => ['type' => FieldInterface::TYPE_LONG],
                        'attribute_code' => ['type' => FieldInterface::TYPE_TEXT],
                        'attribute_id' => ['type' => FieldInterface::TYPE_LONG],
                        'position' => ['type' => FieldInterface::TYPE_TEXT],
                        'values' => [
                            'properties' => [
                                'value_index' => ['type' => FieldInterface::TYPE_KEYWORD],
                            ]
                        ],
                    ],
                ],
                'category' => [
                    'properties' => [
                        'category_id' => ['type' => FieldInterface::TYPE_LONG],
                        'name' => ['type' => FieldInterface::TYPE_TEXT],
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
