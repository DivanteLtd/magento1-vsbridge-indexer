<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract as AbstractMapping;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;
use Mage_Eav_Model_Entity_Attribute as Attribute;

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
     * @var array
     */
    private $properties;

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
            $attributesMapping = [];

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $mapping = $this->getAttributeMapping($attribute);
                $attributesMapping[$attributeCode] = $mapping[$attributeCode];
            }

            /**
             * @var $generalMapping GeneralMapping
             */
            $generalMapping = Mage::getSingleton('vsf_indexer/index_mapping_generalmapping');
            $attributesMapping['stock']['properties'] = $generalMapping->getStockMapping();
            $attributesMapping['slug'] = ['type' => FieldInterface::TYPE_KEYWORD];
            $attributesMapping['brand'] = ['type' => FieldInterface::TYPE_TEXT];

            $attributesMapping['media_gallery'] = [
                'properties' => [
                    'type' => ['type' => FieldInterface::TYPE_TEXT],
                    'image' => ['type' => FieldInterface::TYPE_TEXT],
                    'lab' => ['type' => FieldInterface::TYPE_TEXT],
                    'pos' => ['type' => FieldInterface::TYPE_TEXT],
                ],
            ];

            $attributesMapping['final_price'] = ['type' => FieldInterface::TYPE_DOUBLE];
            $attributesMapping['regular_price'] = ['type' => FieldInterface::TYPE_DOUBLE];

            $properties = $this->getCustomProperties();

            $properties = array_merge($properties, $attributesMapping);
            $properties = array_merge($properties, $generalMapping->getCommonProperties());

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_product_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }

    /**
     * @return array
     */
    public function getCustomProperties()
    {
        return [
            'attribute_set_id' => ['type' => FieldInterface::TYPE_LONG],
            'bundle_options' => [
                'properties' => [
                    'option_id' => ['type' => FieldInterface::TYPE_LONG],
                    'position' => ['type' => FieldInterface::TYPE_LONG],
                    'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                    'product_links' => [
                        'properties' => [
                            'id' => ['type' => FieldInterface::TYPE_LONG],
                            'is_default' => ['type' => FieldInterface::TYPE_BOOLEAN],
                            'qty' => ['type' => FieldInterface::TYPE_DOUBLE],
                            'can_change_quantity' => ['type' => FieldInterface::TYPE_BOOLEAN],
                            'price' => ['type' => FieldInterface::TYPE_DOUBLE],
                            'price_type' => ['type' => FieldInterface::TYPE_TEXT],
                            'position' => ['type' => FieldInterface::TYPE_LONG],
                            'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                        ],
                    ],
                ],
            ],
            'product_links' => [
                'properties' => [
                    'linked_product_type' => ['type' => FieldInterface::TYPE_TEXT],
                    'linked_product_sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                    'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                    'position' => ['type' => FieldInterface::TYPE_LONG],
                ],
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
                        ],
                    ],
                ],
            ],
            'category' => [
                'type' => 'nested',
                'properties' => [
                    'category_id' => ['type' => FieldInterface::TYPE_LONG],
                    'position' => ['type' => FieldInterface::TYPE_LONG],
                    'name' => ['type' => FieldInterface::TYPE_TEXT],
                ],
            ],
            'tier_prices' => [
                'properties' => [
                    'customer_group_d' => ['type' => FieldInterface::TYPE_INTEGER],
                    'qty' => ['type' => FieldInterface::TYPE_DOUBLE],
                    'value' => ['type' => FieldInterface::TYPE_DOUBLE],
                    'extension_attributes' => [
                        'properties' => [
                            'website_id' => ['type' => FieldInterface::TYPE_INTEGER],
                        ],
                    ],
                ],
            ],
            'salespromotion_sold_amount' => [
                'type' => FieldInterface::TYPE_INTEGER
            ]
        ];
    }
}
