<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Inventory
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Tiers
{

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Stock constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $adapter */
        $this->connection = $this->resource->getConnection('catalog_read');
    }

    public function loadTierPrices($websiteId, array $productIds)
    {
        $columns = [
            'price_id' => 'value_id',
            'website_id' => 'website_id',
            'all_groups' => 'all_groups',
            'cust_group' => 'customer_group_id',
            'price_qty' => 'qty',
            'price' => 'value',
            'product_id' => 'entity_id',
        ];

        $select = $this->connection->select()
            ->from($this->resource->getTableName('catalog/product_attribute_tier_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(
                [
                    'entity_id',
                    'qty',
                ]
            );

        if ($websiteId === 0) {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where(
                'website_id IN (?)',
                [
                    '0',
                    $websiteId,
                ]
            );
        }

        $tierPrices = [];

        foreach ($this->connection->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = array(
                'website_id' => (int)$row['website_id'],
                'cust_group' => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL
                    : (int)$row['cust_group'],
                'price_qty' => (float)$row['price_qty'],
                'price' => (float)$row['price'],
                'website_price' => (float)$row['price'],
            );
        }

        return $tierPrices;
    }
}
