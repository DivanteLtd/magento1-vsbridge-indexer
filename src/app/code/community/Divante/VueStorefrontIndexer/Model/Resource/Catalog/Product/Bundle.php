<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Bundle
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Bundle
{

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * @var array
     */
    private $products;

    /**
     * @var array
     */
    private $bundleProductIds;

    /**
     * @var array
     */
    private $bundleOptionsByProduct = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        $this->connection = $this->resource->getConnection('read');
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    /**
     * Clear data
     * @return void
     */
    public function clear()
    {
        $this->products = null;
        $this->bundleOptionsByProduct = [];
        $this->bundleProductIds = null;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function loadBundleOptions($storeId)
    {
        $productIds = $this->getBundleProductIds();

        if (empty($productIds)) {
            return [];
        }

        $this->initOptions($storeId);
        $this->initSelection();

        return $this->bundleOptionsByProduct;
    }

    /**
     * Init Options
     *
     * @param int $storeId
     */
    private function initOptions($storeId)
    {
        $bundleOptions = $this->getBundleOptionsFromResource($storeId);

        foreach ($bundleOptions as $bundleOption) {
            $productId = $bundleOption['parent_id'];
            $optionId = $bundleOption['option_id'];

            $this->bundleOptionsByProduct[$productId][$optionId] = [
                'option_id' => intval($bundleOption['option_id']),
                'position' => intval($bundleOption['position']),
                'type' => $bundleOption['type'],
                'sku' => $this->products[$productId]['sku'],
                'title' => $bundleOption['title'],
                'required' => (bool)$bundleOption['required'],
            ];
        }
    }

    /**
     * Append Selection
     */
    private function initSelection()
    {
        $bundleSelections = $this->getBundleSelections();
        $simpleIds = array_column($bundleSelections, 'product_id');
        $simpleSkuList = $this->getProductSku($simpleIds);

        foreach ($bundleSelections as $selection) {
            $optionId = $selection['option_id'];
            $parentId = $selection['parent_product_id'];
            $productId = $selection['product_id'];
            $bundlePriceType = $this->products[$parentId]['price_type'];

            $selectionPriceType = $bundlePriceType ? $selection['selection_price_type'] : null;
            $selectionPrice = $bundlePriceType ? $selection['selection_price_value'] : null;

            $this->bundleOptionsByProduct[$parentId][$optionId]['product_links'][] = [
                'id' => $selection['selection_id'],
                'is_default' => (bool)$selection['is_default'],
                'qty' => (float)$selection['selection_qty'],
                'can_change_quantity' => (bool)$selection['selection_can_change_qty'],
                'price' => (float)$selectionPrice,
                'price_type' => $selectionPriceType,
                'position' => intval($selection['position']),
                'sku' => $simpleSkuList[$productId],
            ];
        }
    }

    /**
     * @return array
     */
    private function getBundleSelections()
    {
        $productIds = $this->getBundleProductIds();

        $select = $this->connection->select()->from(
            ['selection' => $this->resource->getTableName('bundle/selection')]
        );

        $select->where('parent_product_id IN (?)', $productIds);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getProductSku(array $productIds)
    {
        $select = $this->connection->select();
        $select->from($this->resource->getTableName('catalog/product'), ['entity_id', 'sku']);
        $select->where('entity_id IN (?)', $productIds);

        return $this->connection->fetchPairs($select);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    private function getBundleOptionsFromResource($storeId)
    {
        $productIds = $this->getBundleProductIds();

        $select = $this->connection->select()->from(
            ['main_table' => $this->resource->getTableName('bundle/option')]
        );

        $select->where('parent_id IN (?)', $productIds);
        $select->order('main_table.position asc')
            ->order('main_table.option_id asc');

        $select = $this->joinOptionValues($select, $storeId);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param Varien_Db_Select $select
     * @param int $storeId
     *
     * @return Varien_Db_Select
     */
    private function joinOptionValues(Varien_Db_Select $select, $storeId)
    {
        $select
            ->joinLeft(
                array('option_value_default' => $this->resource->getTableName('bundle/option_value')),
                'main_table.option_id = option_value_default.option_id and option_value_default.store_id = 0',
                array()
            )
            ->columns(array('default_title' => 'option_value_default.title'));

        $title = $this->connection->getCheckSql(
            'option_value.title IS NOT NULL',
            'option_value.title',
            'option_value_default.title'
        );

        $select->columns(['title' => $title])
            ->joinLeft(
                ['option_value' => $this->resource->getTableName('bundle/option_value')],
                $this->connection->quoteInto(
                    'main_table.option_id = option_value.option_id and option_value.store_id = ?',
                    $storeId
                ),
                []
            );

        return $select;
    }

    /**
     * @return array
     */
    private function getBundleProductIds()
    {
        if (null === $this->bundleProductIds) {
            $this->bundleProductIds = [];

            foreach ($this->products as $productData) {
                if ('bundle' === $productData['type_id']) {
                    $this->bundleProductIds[] = $productData['id'];
                }
            }
        }

        return $this->bundleProductIds;
    }
}