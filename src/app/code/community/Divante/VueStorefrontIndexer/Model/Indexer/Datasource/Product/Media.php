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
    protected $resource;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Gallery
     */
    protected $galleryData;

    /**
     * @var Ambimax_LazyCatalogImages_Model_Catalog_Image
     */
    protected $_lazyCatalog;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_media');
        $this->galleryData = Mage::getModel('vsf_indexer/data_gallery');
        $this->_lazyCatalog = Mage::getModel('ambimax_lazycatalogimages/catalog_image');
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
            $indexData[$productId]['media_gallery'] = $this->applyLazyCatalogSettings($mediaGallery);
        }

        return $indexData;
    }

    protected function applyLazyCatalogSettings(array $mediaGallery): array
    {
        foreach ($mediaGallery as $i => $item) {
            $imageName = $this->getImageNameByPath($item);

            $mediaGallery[$i]['image'] = $this->_lazyCatalog
                ->setHeight(0)
                ->setWidth(0)
                ->setImagePath($item['image'])
                ->setImageName($imageName)
                ->getImageUrl();
        }

        return $mediaGallery;
    }

    protected function getImageNameByPath(array $item): string
    {
        // e. g. $item == '/Hund/Bilder/547334_2.jpg'
        $arr = explode('/', $item['image']);
        // get string from last '/' to next '.'
        return explode('.', array_pop($arr))[0];
    }
}
