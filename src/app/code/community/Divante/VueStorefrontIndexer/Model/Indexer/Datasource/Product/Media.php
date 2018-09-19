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
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_media');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $gallerySet = $this->resource->loadGallerySet($productIds, $storeId);

        foreach ($gallerySet as $mediaImage) {
            $productId = $mediaImage['product_id'];
            $image['typ'] = 'image';
            $image = [
                'typ' => 'image',
                'image' => $mediaImage['file'],
                'lab' => $this->getValue('label', $mediaImage),
                'pos' => intval($this->getValue('position', $mediaImage)),
            ];

            if (!isset($indexData[$productId]['media_gallery']) || !is_array($indexData[$productId]['media_gallery'])) {
                $indexData[$productId]['media_gallery'] = [];
            }

            $indexData[$productId]['media_gallery'][] = $image;
        }

        return $indexData;
    }

    /**
     * @param string $fieldKey
     * @param array $image
     *
     * @return string
     */
    private function getValue($fieldKey, array $image)
    {
        if (isset($image[$fieldKey]) && (null !== $image[$fieldKey])) {
            return $image[$fieldKey];
        }

        if (isset($image[$fieldKey . '_default'])) {
            return $image[$fieldKey . '_default'];
        }

        return '';
    }
}
