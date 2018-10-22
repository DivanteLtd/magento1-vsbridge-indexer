<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Product
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product
     */
    private $resourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    private $dataFilter;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category_Full constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product');
        $this->dataFilter = Mage::getSingleton('vsf_indexer/data_filter');
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $productIds = [])
    {
        $lastProductId = 0;

        do {
            $products = $this->resourceModel->getProducts($storeId, $productIds, $lastProductId);

            /** @var array $product */
            foreach ($products as $product) {
                $lastProductId = $product['entity_id'];
                $product['id'] = intval($product['entity_id']);
                unset($product['entity_id']);
                yield $lastProductId => $this->filterData($product);
            }
        } while (!empty($products));
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getDisableProducts($storeId, array $productIds)
    {
        $enabledIds = $this->resourceModel->getEnableProductIds($storeId, $productIds);

        return array_diff($productIds, $enabledIds);
    }

    /**
     * @param array $productData
     *
     * @return mixed
     */
    private function filterData(array $productData)
    {
        return $this->dataFilter->execute($productData);
    }
}