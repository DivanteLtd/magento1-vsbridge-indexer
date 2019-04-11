<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;
use Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping as GeneralMapping;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Configurable
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Configurable implements DataSourceInterface
{

    /**
     * @var int
     */
    private $batchSize = 500;
    /**
     * @var array
     */
    private $childBlackListConfig = [
        'entity_id',
        'parent_id',
        'parent_ids',
    ];
    /**
     * We don't have to load all attributes, we have load data for simple products separately
     * If we have lots of configurable products with children, we have to process smaller batches
     * (depends on number of child/parent number, and number of required attributes)
     * @var array
     */
    private $requireChildrenAttributes = [
        'name',
        'small_image',
        'thumbnail',
        'image',
        'url_key',
        'status',
        'visibility',
        'tax_class_id',
    ];
    /**
     * Images
     * @var array
     */
    private $imageAttributes = [
        'image',
        'small_image',
        'thumbnail'
    ];
    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    private $dataFilter;
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Configurable
     */
    private $configurableResource;
    /**
     * @var  Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes
     */
    private $resourceAttributeModel;
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Inventory
     */
    private $inventoryResource;
    /**
     * @var GeneralMapping
     */
    private $generalMapping;
    /**
     * @var Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
     */
    private $configSettings;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category_Full constructor.
     */
    public function __construct()
    {
        $this->resourceAttributeModel = Mage::getResourceModel('vsf_indexer/catalog_product_attributes');
        $this->configurableResource = Mage::getResourceModel('vsf_indexer/catalog_product_configurable');
        $this->dataFilter = Mage::getSingleton('vsf_indexer/data_filter');
        $this->generalMapping = Mage::getSingleton('vsf_indexer/index_mapping_generalmapping');
        $this->inventoryResource = Mage::getResourceModel('vsf_indexer/catalog_product_inventory');
        $this->configSettings = Mage::getSingleton('vsf_indexer/config_catalogsettings');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->configurableResource->clear();
        $this->configurableResource->setProducts($indexData);

        $indexData = $this->prepareConfigurableChildrenAttributes($indexData, $storeId);
        $indexData = $this->addConfigurableAttributes($indexData);

        $this->configurableResource->clear();

        return $indexData;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    private function prepareConfigurableChildrenAttributes(array $indexData, $storeId)
    {
        $allChildren = $this->configurableResource->getSimpleProducts($storeId);

        if (null === $allChildren) {
            return $indexData;
        }

        $notifyStockDefaultValue = $this->getNotifyForQtyBelowDefaultValue($storeId);
        $childIds = array_keys($allChildren);

        $stockRowData = $this->inventoryResource->loadChildrenData($storeId, $childIds);
        $configurableAttributeCodes = $this->configurableResource->getConfigurableAttributeCodes();

        $requiredAttributes = array_merge(
            $this->getRequiredChildrenAttributes(),
            $configurableAttributeCodes
        );

        $requiredAttribute = array_unique($requiredAttributes);
        $allChildren = $this->loadChildrenRawAttributesInBatches($storeId, $allChildren, $requiredAttribute);

        foreach ($allChildren as $child) {
            $childId = $child['entity_id'];
            $child['id'] = intval($child['entity_id']);

            $parentIds = $child['parent_ids'];

            if (isset($stockRowData[$childId])) {
                $productStockData = $stockRowData[$childId];

                if (isset($productStockData['use_config_notify_stock_qty'])
                    && $productStockData['use_config_notify_stock_qty']
                ) {
                    $productStockData['notify_stock_qty'] = $notifyStockDefaultValue;
                }

                unset($productStockData['product_id']);
                $productStockData = $this->generalMapping->prepareStockData($productStockData);
                $child['stock'] = $productStockData;
            }

            foreach ($parentIds as $parentId) {
                $child = $this->filterData($child);

                if (!isset($indexData[$parentId]['configurable_options'])) {
                    $indexData[$parentId]['configurable_options'] = [];
                }

                if ($this->configSettings->useImageInheritanceForConfigurableChildren()) {
                    foreach ($this->imageAttributes as $code) {
                        if ((!array_key_exists($code, $child)
                            || !$child[$code]
                            || $child[$code] == 'no_selection')
                            && array_key_exists($code, $indexData[$parentId])
                        ) {
                            $child[$code] = $indexData[$parentId][$code];
                        }
                    }
                }

                if (!$this->configSettings->useSimplePriceForConfigurableChildren()) {
                    $child['price'] = $indexData[$parentId]['price'];
                    $child['special_price'] = $indexData[$parentId]['special_price'];
                    $child['special_to_date'] = null;
                    $child['special_from_date'] = null;

                    if (isset($indexData[$parentId]['special_to_date'])) {
                        $child['special_to_date'] = $indexData[$parentId]['special_to_date'];
                    }

                    if (isset($indexData[$parentId]['special_from_date'])) {
                        $child['special_from_date'] = $indexData[$parentId]['special_from_date'];
                    }
                }

                $indexData[$parentId]['configurable_children'][] = $child;
            }
        }

        $allChildren = null;

        return $indexData;
    }

    /**
     * @param $storeId
     *
     * @return float
     */
    private function getNotifyForQtyBelowDefaultValue($storeId)
    {
        return (float)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY, $storeId);
    }

    /**
     * @return array
     */
    public function getRequiredChildrenAttributes()
    {
        $attributes = $this->requireChildrenAttributes;

        if ($this->configSettings->useSimplePriceForConfigurableChildren()) {
            $attributes = array_merge(
                $attributes,
                [
                    'price',
                    'special_price',
                    'special_to_date',
                    'special_from_date',
                ]
            );
        }

        return $attributes;
    }

    /**
     * @param array $indexData
     *
     * @return array
     */
    private function addConfigurableAttributes(array $indexData)
    {
        foreach ($indexData as $productId => $productDTO) {
            if (!isset($productDTO['configurable_children'])) {
                $indexData[$productId]['configurable_children'] = [];
                continue;
            }

            $configurableChildren = $indexData[$productId]['configurable_children'];
            $productAttributeOptions =
                $this->configurableResource->getProductConfigurableAttributes($productDTO);

            foreach ($productAttributeOptions as $productAttribute) {
                $attributeCode = $productAttribute['attribute_code'];

                if (!isset($indexData[$productId][$attributeCode . '_options'])) {
                    $indexData[$productId][$attributeCode . '_options'] = [];
                }

                $values = [];
                $areChildInStock = 0;

                foreach ($configurableChildren as $index => $child) {
                    $specialPrice = 0;
                    $value = $child[$attributeCode];

                    if (isset($value)) {
                        $values[] = intval($value);
                    }

                    if ($child['stock']['is_in_stock']) {
                        $areChildInStock = 1;
                    }

                    if (!$this->configSettings->useSimplePriceForConfigurableChildren()
                        && isset($productAttribute['pricing'][$value])) {
                        $childPrice = floatval($child['price']);

                        if (isset($child['special_price']) && $child['special_price'] !== null) {
                            $specialPrice = floatval($child['special_price']);
                        }

                        $priceInfo = $productAttribute['pricing'][$value];
                        $configurablePrice = $this->calcSelectionPrice($priceInfo, $childPrice);
                        $configurableChildren[$index]['price'] = $childPrice + $configurablePrice;

                        if ($specialPrice) {
                            $confSpecialPrice = $this->calcSelectionPrice($priceInfo, $specialPrice);
                            $configurableChildren[$index]['special_price'] = $specialPrice + $confSpecialPrice;
                        }
                    }
                }

                $indexData[$productId]['configurable_children'] = $configurableChildren;
                $values = array_values(array_unique($values));

                foreach ($values as $value) {
                    $productAttribute['values'][] = ['value_index' => $value];
                }

                unset($productAttribute['pricing']);
                $productStockStatus = $indexData[$productId]['stock']['stock_status'];
                $isInStock = $indexData[$productId]['stock']['is_in_stock'];

                if (!$isInStock || ($productStockStatus && !$areChildInStock)) {
                    $indexData[$productId]['stock']['stock_status'] = 0;
                }

                $indexData[$productId]['configurable_options'][] = $productAttribute;
                $indexData[$productId][$productAttribute['attribute_code'] . '_options'] = $values;
            }
        }

        return $indexData;
    }

    /**
     * @param array $priceInfo
     * @param float $productPrice
     *
     * @return float|int
     */
    private function calcSelectionPrice(array $priceInfo, $productPrice)
    {
        if ($priceInfo['is_percent']) {
            $ratio = floatval($priceInfo['pricing_value']) / 100;
            $price = $productPrice * $ratio;
        } else {
            $price = floatval($priceInfo['pricing_value']);
        }

        return $price;
    }

    /**
     * @param int   $storeId
     * @param array $allChildren
     * @param array $requiredAttributes
     *
     * @return mixed
     */
    private function loadChildrenRawAttributesInBatches($storeId, array $allChildren, array $requiredAttributes)
    {
        $requiredAttribute = array_unique($requiredAttributes);
        $childIds = [];

        foreach ($allChildren as $childId => $child) {
            $childIds[] = $childId;

            if (count($childIds) >= $this->batchSize) {
                $attributeData = $this->resourceAttributeModel->loadAttributesData(
                    $storeId,
                    $childIds,
                    $requiredAttribute
                );

                foreach ($attributeData as $productId => $attribute) {
                    $allChildren[$productId] = array_merge(
                        $allChildren[$productId],
                        $attribute
                    );
                }

                $childIds = [];
                $attributeData = null;
            }
        }

        if (count($childIds)) {
            $attributeData = $this->resourceAttributeModel->loadAttributesData(
                $storeId,
                $childIds,
                $requiredAttribute
            );

            foreach ($attributeData as $productId => $attribute) {
                $allChildren[$productId] = array_merge(
                    $allChildren[$productId],
                    $attribute
                );
            }

            $childIds = null;
            $attributeData = null;
        }

        return $allChildren;
    }

    /**
     * @param array $productData
     *
     * @return array
     */
    private function filterData(array $productData)
    {
        return $this->dataFilter->execute($productData, $this->childBlackListConfig);
    }
}
