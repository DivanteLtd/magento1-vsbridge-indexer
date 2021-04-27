<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Prices
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Prices
{

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Stock constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $adapter */
        $this->connection = $this->resource->getConnection('catalog_read');
    }

    /**
     * @param int   $storeId
     * @param array $productIds
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function loadPriceData($storeId, array $productIds)
    {
        $websiteId = $this->getStore($storeId)->getWebsiteId();

        $select = $this->connection->select()
            ->from(
                ['p' => $this->resource->getTableName('catalog/product_index_price')],
                [
                    'entity_id',
                    'price',
                    'final_price',
                    'min_price',
                    'max_price'
                ]
            )
            ->where('p.customer_group_id = 0')
            ->where('p.website_id = ?', $websiteId)
            ->where('p.entity_id IN(?)', $productIds);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param int $storeId
     *
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    private function getStore($storeId)
    {
        return Mage::app()->getStore($storeId);
    }
}
