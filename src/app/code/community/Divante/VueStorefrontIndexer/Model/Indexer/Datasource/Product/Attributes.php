<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;
use Divante_VueStorefrontIndexer_Model_Sluggenerator as SlugGenerator;

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
    protected $resourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
     */
    protected $settings;

    /**
     * @var SlugGenerator
     */
    protected $slugGenerator;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Attributes constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_attributes');
        $this->settings = Mage::getSingleton('vsf_indexer/config_catalogsettings');
        $this->slugGenerator = Mage::getSingleton('vsf_indexer/sluggenerator');
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $attributes = $this->resourceModel->loadAttributesData($storeId, array_keys($indexData));

        foreach ($attributes as $entityId => $attributesData) {
            $productData = array_merge($indexData[$entityId], $attributesData);

            if ($this->settings->useMagentoUrlKeys()) {
                $productData['slug'] = $productData['url_key'];
            } else {
                $slug = $this->slugGenerator->generate($productData['name'], $entityId);
                $productData['slug'] = $slug;
            }

            $indexData[$entityId] = $productData;
        }

        $attributes = null;

        return $indexData;
    }
}
