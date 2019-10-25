<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Groupprices
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Groupprices
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
     * @param int $websiteId
     * @param array $productIds
     *
     * @return array
     */
    public function loadGroupPrices($websiteId, array $productIds)
    {
        $columns = [
            'price_id' => 'value_id',
            'website_id' => 'website_id',
            'all_groups' => 'all_groups',
            'cust_group' => 'customer_group_id',
            'price' => 'value',
            'product_id' => 'entity_id',
        ];

        $select = $this->connection->select()
            ->from($this->resource->getTableName('catalog/product_attribute_group_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order('entity_id');

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

        $groupPrices = [];

        foreach ($this->connection->fetchAll($select) as $row) {
            $groupPrices[$row['product_id']][] = [
                'website_id' => (int)$row['website_id'],
                'price_qty' => 1,
                'cust_group' => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL
                    : (int)$row['cust_group'],
                'price' => (float)$row['price'],
            ];
        }

        return $groupPrices;
    }
}
