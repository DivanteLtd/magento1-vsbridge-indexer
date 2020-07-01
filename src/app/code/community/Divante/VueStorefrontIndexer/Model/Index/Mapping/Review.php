<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract as AbstractMapping;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Review
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Jose Castaneda <jose@qbo.tech>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Review implements MappingInterface
{
    /**
     * @var array
     */
    private $properties;

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        if (null === $this->properties) {
            $properties = [
                'created_at' => [
                    'type' => FieldInterface::TYPE_DATE,
                    'format' => FieldInterface::DATE_FORMAT,
                ],
                'id' => ['type' => FieldInterface::TYPE_LONG],
                'product_id' => ['type' => FieldInterface::TYPE_LONG],
                'title' => ['type' => FieldInterface::TYPE_TEXT],
                'detail' => ['type' => FieldInterface::TYPE_TEXT],
                'nickname' => ['type' => FieldInterface::TYPE_TEXT],
                'review_status' => ['type' => FieldInterface::TYPE_INTEGER],
                'customer_id' => ['type' => FieldInterface::TYPE_INTEGER],
                'ratings' => [
                    'properties' => [
                        'percent' => ['type' => FieldInterface::TYPE_SHORT],
                        'value' => ['type' => FieldInterface::TYPE_SHORT],
                        'title' => ['type' => FieldInterface::TYPE_TEXT],
                    ],
                ]
            ];

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_review_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }
}
