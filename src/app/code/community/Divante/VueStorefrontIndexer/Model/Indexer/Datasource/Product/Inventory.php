<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;
use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;

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
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category_Full constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_inventory');
        $this->generalMapping = Mage::getSingleton('vsf_indexer/index_mapping_generalmapping');
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
        $stockMapping = $this->generalMapping->getStockMapping();

        foreach ($stockData as $key => $value) {
            if (isset($stockMapping[$key]['type'])) {
                $type = $stockMapping[$key]['type'];

                if ($type === FieldInterface::TYPE_BOOLEAN) {
                    settype($stockData[$key], 'bool');
                }

                if ($type === FieldInterface::TYPE_LONG) {
                    settype($stockData[$key], 'int');
                }
            }
        }
        
        return $stockData;
    }
}
