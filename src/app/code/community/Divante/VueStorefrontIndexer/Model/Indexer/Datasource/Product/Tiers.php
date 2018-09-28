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
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Tiers implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Tiers
     */
    private $resource;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Tiers constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_tiers');
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array|void
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $websiteId = $this->getWebsiteId($storeId);

        $tierPrices = $this->resource->loadTierPrices($websiteId, $productIds);

        /** @var Mage_Catalog_Model_Product_Attribute_Backend_Tierprice $backend */
        $backend = $this->getTierPriceAttribute()->getBackend();

        foreach ($indexData as $productId => $prodctData) {
            if (isset($tierPrices[$productId])) {
                $productTierPrices = $tierPrices[$productId];

                if (!empty($productTierPrices) && $websiteId) {
                    $productTierPrices = $backend->preparePriceData(
                        $productTierPrices,
                        $indexData['product_id'],
                        $websiteId
                    );

                    foreach ($productTierPrices as $productTierPrice) {
                        $indexData[$productId]['tier_prices'][] = [
                            'customer_group_id' => (int)$productTierPrice['cust_group'],
                            'value' => (float)$productTierPrice['price'],
                            'qty' => (float)$productTierPrice['price_qty'],
                            'extension_attributes' => [
                                'website_id' => (int)$productTierPrice['website_id'],
                            ],
                        ];
                    }
                }
            }
        }

        return $indexData;
    }

    /**
     * @param $storeId
     *
     * @return int
     * @throws Mage_Core_Model_Store_Exception
     */
    private function getWebsiteId($storeId)
    {
        $attribute = $this->getTierPriceAttribute();
        $websiteId = 0;

        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($storeId) {
            $websiteId = intval(Mage::app()->getStore($storeId)->getWebsiteId());
        }

        return $websiteId;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    private function getTierPriceAttribute()
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attributeModel */
        $attributeModel = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'tier_price');

        return $attributeModel;
    }
}
