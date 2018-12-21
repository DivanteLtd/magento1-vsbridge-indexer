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
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Media implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Media
     */
    private $resource;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Gallery
     */
    private $galleryData;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_media');
        $this->galleryData = Mage::getModel('vsf_indexer/data_gallery');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $gallerySet = $this->resource->loadGallerySet($productIds, $storeId);

        $galleryPerProduct = $this->galleryData->prepareMediaGallery($gallerySet);

        foreach ($galleryPerProduct as $productId => $mediaGallery) {
            $indexData[$productId]['media_gallery'] = $mediaGallery;
        }

        return $indexData;
    }
}
