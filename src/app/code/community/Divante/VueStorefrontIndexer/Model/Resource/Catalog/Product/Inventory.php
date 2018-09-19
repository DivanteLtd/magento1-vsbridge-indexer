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
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Inventory
{

    /**
     * @var array
     */
    private $fields = [
        'product_id',
        'item_id',
        'stock_id',
        'qty',
        'is_in_stock',
        'is_qty_decimal',
        'use_config_min_qty',
        'min_qty',
        'use_config_min_sale_qty',
        'min_sale_qty',
        'use_config_max_sale_qty',
        'max_sale_qty',
        'use_config_backorders',
        'backorders',
        'use_config_notify_stock_qty',
        'notify_stock_qty',
        'use_config_qty_increments',
        'qty_increments',
        'use_config_enable_qty_inc',
        'enable_qty_increments',
        'use_config_manage_stock',
        'manage_stock',
        'low_stock_date',
        'is_decimal_divided',
        'stock_status_changed_auto',
    ];

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

    public function loadInventoryData($storeId, $productIds)
    {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

        $select = $this->connection->select()
            ->from(
                ['main_table' => $this->resource->getTableName('cataloginventory/stock_item')],
                $this->fields
            )
            ->where('main_table.product_id IN (?)', $productIds);

        $select->joinLeft(
            ['status_table' => $this->resource->getTableName('cataloginventory/stock_status')],
            'main_table.product_id=status_table.product_id AND main_table.stock_id=status_table.stock_id'
            . $this->connection->quoteInto(' AND status_table.website_id=?', $websiteId),
            ['stock_status']
        );

        return $this->connection->fetchAssoc($select);
    }
}
