<?php

use Mage_Catalog_Model_Resource_Product_Attribute_Collection as AttributeCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Metaattributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Metaattributes
{

    /**
     * @var array
     */
    protected $requiredColumns = [
        'is_visible_on_front',
        'is_visible',
        'default_frontend_label',
        'attribute_id',
        'entity_type_id',
        'frontend_input',
        'attribute_id',
        'frontend_input',
        'is_user_defined',
        'is_comparable',
        'attribute_code',
    ];

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    /**
     * @var array
     */
    private $cacheAttributes;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');
    }

    /**
     * @return array
     */
    public function execute()
    {
        if (null === $this->cacheAttributes) {
            $select = $this->createBaseLoadSelect();
            $this->cacheAttributes = $this->connection->fetchAll($select);
        }

        return $this->cacheAttributes;
    }

    /**
     * @return Varien_Db_Select
     */
    private function createBaseLoadSelect()
    {
        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $select = $attributeCollection->getSelect();
        $select->where('is_user_defined = ?', 1);
        $select->order('main_table.attribute_id');

        return $select;
    }
}
