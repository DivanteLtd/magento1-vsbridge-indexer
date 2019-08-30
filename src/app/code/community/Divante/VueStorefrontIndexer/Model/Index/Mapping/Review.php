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
 * @author      Jose Castaneda <jose@qbo.tech>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Review implements MappingInterface
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
            ];

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_review_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }
}
