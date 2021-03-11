<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Eav_Abstract as AbstractMapping;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Salespromotion
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Salespromotion implements MappingInterface
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
                'id' => ['type' => FieldInterface::TYPE_LONG],
                'product_id' => ['type' => FieldInterface::TYPE_LONG],
                'category_id' => ['type' => FieldInterface::TYPE_LONG],
                'sold_amount' => ['type' => FieldInterface::TYPE_LONG]
            ];

            $mapping = ['properties' => $properties];

            $mappingObject = new Varien_Object($mapping);
            Mage::dispatchEvent('elasticsearch_salespromotion_mapping_properties', ['mapping' => $mappingObject]);

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }
}
