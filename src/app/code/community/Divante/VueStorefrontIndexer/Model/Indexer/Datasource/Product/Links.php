<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links
     */
    private $linkedProductResource;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
        $this->linkedProductResource = Mage::getResourceModel('vsf_indexer/catalog_product_links');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->linkedProductResource->clear();
        $this->linkedProductResource->setProducts($indexData);

        foreach ($indexData as &$productDTO) {
            $productDTO['product_links'] = $this->linkedProductResource->getLinkedProduct($productDTO);
        }

        $this->linkedProductResource->clear();

        return $indexData;
    }
}
