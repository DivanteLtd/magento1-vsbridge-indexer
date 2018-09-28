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
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Attributes implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes
     */
    private $resourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    private $dataFilter;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Attributes constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_attributes');
        $this->dataFilter = Mage::getSingleton('vsf_indexer/data_filter');
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $attributes = $this->resourceModel->loadAttributesData($storeId, array_keys($indexData));

        foreach ($attributes as $entityId => $attributesData) {
            $productData = array_merge($indexData[$entityId], $attributesData);
            $indexData[$entityId] = $this->dataFilter->execute($productData);
        }

        $attributes = null;

        return $indexData;
    }
}