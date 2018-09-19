<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Configurable
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Bundle implements DataSourceInterface
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Bundle
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Bundle constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_bundle');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->resourceModel->clear();
        $this->resourceModel->setProducts($indexData);
        $productBundleOptions = $this->resourceModel->loadBundleOptions($storeId);

        foreach ($productBundleOptions as $productId => $bundleOptions) {
            $indexData[$productId]['bundle_options'] = [];

            foreach ($bundleOptions as $option) {
                $indexData[$productId]['bundle_options'][] = $option;
            }
        }

        $this->resourceModel->clear();
        $productBundleOptions = null;

        return $indexData;
    }
}
