<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Salespromotion_ProductData implements DataSourceInterface
{

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Salespromotion_ProductData constructor.
     */
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $productsById = $this->collectProductsById($indexData);
        $productsById = $this->loadProductData($productsById, $storeId);

        foreach ($indexData as $promotionId => $promotion) {
            $indexData[$promotionId]['product'] = $productsById[$promotion['product_id']];
        }

        return $indexData;
    }

    /**
     * Collect all referenced products in the given index Data and adds
     * them to a new dictionary. The product data can then be fetched
     * more efficiently.
     *
     * @param array $indexData The raw index data.
     */
    private function collectProductsById(array $indexData)
    {
        $productsById = [];

        foreach ($indexData as $promotionId => $promotion) {
            $productId = $promotion['product_id'];

            if (is_null($productId)) {
                continue;
            }

            if (!array_key_exists($productId, $productsById)) {
                $productsById[$productId] = $indexData[$promotionId]['product'] = [
                    'entity_id' => $productId
                ];
            }
        }

        return $productsById;
    }

    /**
     * Load product data for the given products.
     *
     * @param array $products The products.
     * @param string $storeId The store id.
     */
    private function loadProductData(array $products, string $storeId)
    {
        $productsResource = Mage::getResourceModel('vsf_indexer/catalog_product');
        foreach ($products as $productId => $product) {
            $products[$productId] = $productsResource->getProductById($storeId, $productId);
        }

        // Apply product dataSources defined in config.xml

        $indexSettings = Mage::getSingleton('vsf_indexer/index_settings');
        $indicesConfiguration = $indexSettings->getIndicesConfig();
        $dataSources = $indicesConfiguration['vue_storefront_catalog']['types']['product']->getDataSources();

        foreach ($dataSources as $name => $dataSource) {
            $products = $dataSource->addData($products, $storeId);
        }

        return $products;
    }
}
