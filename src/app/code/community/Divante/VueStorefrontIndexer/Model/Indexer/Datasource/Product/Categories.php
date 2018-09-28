<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Categories
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Categories implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Category
     */
    private $resourceModel;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_category');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $categoryData = $this->resourceModel->loadCategoryData($storeId, array_keys($indexData));

        foreach ($categoryData as $categoryDataRow) {
            $productId = (int)$categoryDataRow['product_id'];
            $categoryData = [
                'category_id' => (int)$categoryDataRow['category_id'],
                'name' => (string)$categoryDataRow['name'],
            ];

            $indexData[$productId]['category'][] = array_filter($categoryData);
        }

        $categoryData = null;

        return $indexData;
    }
}
