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
            $attributes = $this->getAttributes();
            $attributesMapping  = [];

            foreach ($attributes as $attributeCode => $attribute) {
                $attributesMapping = array_merge($attributesMapping, $this->getAttributeMapping($attributeCode, $attribute));
            }
            /**
             * @var $generalMapping GeneralMapping
             */
            $generalMapping = Mage::getModel('vsf_indexer/index_mapping_generalmapping');
            $properties = array_merge($attributesMapping, $generalMapping->getCommonProperties());
 
            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_review_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
       $attributes = array(
           'product_id' => ['type' => FieldInterface::TYPE_INT],
           'detail' => ['type' => FieldInterface::TYPE_TEXT],
           'nickname' => ['type' => FieldInterface::TYPE_TEXT],
           'review_entity' => ['type' => FieldInterface::TYPE_TEXT],
           'review_status' => ['type' => FieldInterface::TYPE_INT],
           'customer_id' => ['type' => FieldInterface::TYPE_INT]
       );
       return $attributes;
    }

    /**
     * Return mapping for an attribute.
     *
     * @param Attribute $attribute Attribute we want the mapping for.
     *
     * @return array
     */
    public function getAttributeMapping($attributeCode, $attribute)
    {
        $mapping = [];

        $type = $attribute['type'];

        if ($type === 'text') {
            $fieldName = $attributeCode;
            $mapping[$fieldName] = [
                'type' => $type,
                'fielddata' => true,
                'fields' => [
                    'keyword' => [
                        'type' => FieldInterface::TYPE_KEYWORD,
                        'ignore_above' => 256
                    ]
                ]
            ];
        
        } else {
            $mapping[$attributeCode] = ['type' => $type];
        }
        return $mapping;
    }




}
