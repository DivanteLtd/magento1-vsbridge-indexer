<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Inventory
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Inventory implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Inventory
     */
    private $resource;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category_Full constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_inventory');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $inventoryData = $this->resource->loadInventoryData($storeId, array_keys($indexData));

        foreach ($inventoryData as $inventoryDataRow) {
            $productId = (int) $inventoryDataRow['product_id'];
            $indexData[$productId]['stock'] = $this->prepareStockData($inventoryDataRow);
        }

        $inventoryData = null;

        return $indexData;
    }

    /**
     * @param array $stockData
     *
     * @return array
     */
    private function prepareStockData(array $stockData)
    {
        foreach ($stockData as $key => $value) {
            if (strstr($key, 'is_') || strstr($key, 'has_')) {
                $stockData[$key] = boolval($value);
            } elseif ('low_stock_date' !== $key) {
                $stockData[$key] = (int)$value;
            }
        }

        return $stockData;
    }
}
