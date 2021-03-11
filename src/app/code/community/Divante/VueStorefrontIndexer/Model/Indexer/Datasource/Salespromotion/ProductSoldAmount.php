<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Salespromotion_ProductSoldAmount
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Salespromotion_ProductSoldAmount implements DataSourceInterface
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Salespromotion
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Salespromotion_ProductSoldAmount constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_salespromotion');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $promotions = $this->resourceModel->getPromotionsForProducts($storeId, $productIds);

        foreach ($indexData as $productId => $product) {
            $indexData[$productId]['salespromotion_sold_amount'] = $promotions[$productId]['sold_amount'] ?? 0;
        }

        return $indexData;
    }
}
