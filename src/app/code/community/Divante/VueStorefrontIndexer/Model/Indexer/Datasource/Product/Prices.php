<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Prices
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Prices implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Prices
     */
    protected $resource;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Tiers
     */
    protected $tiersResource;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Groupprices
     */
    protected $groupPriceResource;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Tiers constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/catalog_product_prices');
        $this->tiersResource = Mage::getResourceModel('vsf_indexer/catalog_product_tiers');
        $this->groupPriceResource = Mage::getResourceModel('vsf_indexer/catalog_product_groupprices');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $priceData = $this->resource->loadPriceData($storeId, $productIds);

        foreach ($priceData as $priceDataRow) {
            $productId = $priceDataRow['entity_id'];
            $indexData[$productId]['final_price'] = $priceDataRow['final_price'];
            $indexData[$productId]['regular_price'] = $priceDataRow['price'];
            $indexData[$productId]['min_price'] = $priceDataRow['min_price'];
            $indexData[$productId]['max_price'] = $priceDataRow['max_price'];
        }

        return $this->applyTierGroupPrices($indexData, $storeId);
    }

    /**
     * @param array $indexData
     * @param       $storeId
     *
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function applyTierGroupPrices(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $websiteId = $this->getWebsiteId($storeId);

        $tierPrices = $this->tiersResource->loadTierPrices($websiteId, $productIds);
        $groupPrices = $this->groupPriceResource->loadGroupPrices($websiteId, $productIds);
        /** @var Mage_Catalog_Model_Product_Attribute_Backend_Tierprice $backend */
        $backend = $this->getTierPriceAttribute()->getBackend();

        foreach ($groupPrices as $productId => $groupRowData) {
            $groupRowData = $backend->preparePriceData(
                $groupRowData,
                $indexData[$productId]['type_id'],
                $websiteId
            );

            foreach ($groupRowData as $groupPriceData) {
                if (Mage_Customer_Model_Group::NOT_LOGGED_IN_ID === $groupPriceData['cust_group']) {
                    /*vsf does not group prices so we are setting it in */
                    $price = (float)$indexData[$productId]['price'];
                    $price = min((float)$groupPriceData['price'], $price);
                    $indexData[$productId]['price'] = $price;
                }

                $indexData[$productId]['tier_prices'][] = $this->prepareTierPrices($groupPriceData);
            }
        }

        foreach ($tierPrices as $productId => $tierRowData) {
            $productTierPrices = $backend->preparePriceData(
                $tierRowData,
                $indexData[$productId]['type_id'],
                $websiteId
            );

            foreach ($productTierPrices as $productTierPrice) {
                $indexData[$productId]['tier_prices'][] = $this->prepareTierPrices($productTierPrice);
            }
        }

        return $indexData;
    }

    /**
     * @param array $productTierPrice
     *
     * @return array
     */
    protected function prepareTierPrices(array $productTierPrice)
    {
        return [
            'customer_group_id' => (int)$productTierPrice['cust_group'],
            'value' => (float)$productTierPrice['price'],
            'qty' => (float)$productTierPrice['price_qty'],
            'extension_attributes' => [
                'website_id' => (int)$productTierPrice['website_id'],
            ],
        ];
    }

    /**
     * @param int $storeId
     *
     * @return int
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getWebsiteId($storeId)
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
    protected function getTierPriceAttribute()
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attributeModel */
        $attributeModel = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'tier_price');

        return $attributeModel;
    }
}
