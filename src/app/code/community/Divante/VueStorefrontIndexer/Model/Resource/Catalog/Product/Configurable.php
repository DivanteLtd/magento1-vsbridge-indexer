<?php

use Mage_Catalog_Model_Product as Product;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Configurable
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Configurable
{

    /**
     * Array of the ids of configurable products from $productCollection
     *
     * @var array
     */
    private $configurableProductIds;

    /**
     * All associated simple products from configurables in $configurableProductIds
     *
     * @var array
     */
    private $simpleProducts;

    /**
     * Array of associated simple product ids.
     * The array index are configurable product ids, the array values are
     * arrays of the associated simple product ids.
     *
     * @var array
     */
    private $associatedSimpleProducts;

    /**
     * Array keys are the configurable product ids,
     * Values: super_product_attribute_id, attribute_id, position
     *
     * @var array
     */
    private $configurableProductAttributes;

    /**
     * @var array
     */
    private $configurableAttributesInfo;

    /**
     * @var array
     */
    private $productsData;
    /**
     * @var
     */
    private $superAttributeOptions;
    /**
     * @var
     */
    private $productSuperAttributeIds;

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        $this->connection = $this->resource->getConnection('read');
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->productsData = null;
        $this->associatedSimpleProducts = null;
        $this->configurableAttributesInfo = null;
        $this->configurableProductAttributes = null;
        $this->simpleProducts = null;
        $this->configurableProductIds = null;
        $this->superAttributeOptions = null;
        $this->productSuperAttributeIds = null;
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products)
    {
        $this->productsData = $products;
    }

    /**
     * Return the attribute values of the associated simple products
     *
     * @param array $product Configurable product.
     *
     * @return array
     */
    public function getProductConfigurableAttributes(array $product)
    {
        if ($product['type_id'] != Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            return [];
        }

        $productId = $product['id'];
        $data = [];

        $superAttributeIds = $this->productSuperAttributeIds[$productId];

        foreach ($superAttributeIds as $superAttributeId) {
            $superAttribute = $this->superAttributeOptions[$superAttributeId];
            $attributeId = $superAttribute['attribute_id'];
            $attributeData = $this->configurableAttributesInfo[$attributeId];
            $code = $attributeData['attribute_code'];
            $superAttributeData = $this->superAttributeOptions[$superAttributeId];
            unset($superAttributeData['attribute_id']);
            $attributeData = array_merge_recursive($attributeData, $superAttributeData);
            $data[$code] = $attributeData;
        }

        return $data;
    }


    /**
     * Load all configurable attributes used in the current product collection.
     *
     * @return array
     */
    private function getConfigurableProductAttributes()
    {
        if (!$this->configurableProductAttributes) {
            $productIds = $this->getConfigurableProductIds();
            $attributes = $this->getConfigurableAttributesForProductsFromResource($productIds);
            $this->configurableProductAttributes = $attributes;
        }

        return $this->configurableProductAttributes;
    }

    /**
     * @param array $superAttributeId
     *
     * @return array
     */
    private function getSuperAttributePricing(array $superAttributeId)
    {
        $select = $this->connection->select()
            ->from(
                $this->resource->getTableName('catalog/product_super_attribute_pricing'),
                [
                    'value_index',
                    'product_super_attribute_id',
                    'is_percent',
                    'pricing_value',
                ]
            )
            ->where('product_super_attribute_id IN (?)', $superAttributeId);

        $attributes = $this->connection->fetchAssoc($select);

        return $attributes;
    }

    /**
     * This method actually would belong into a resource model, but for easier
     * reference I dropped it into the helper here.
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getConfigurableAttributesForProductsFromResource(array $productIds)
    {
        $select = $this->connection->select()
            ->from(
                $this->resource->getTableName('catalog/product_super_attribute'),
                [
                    'product_super_attribute_id',
                    'product_id',
                    'attribute_id',
                    'position',
                ]
            )
            ->where('product_id IN (?)', $productIds);

        $attributes = $this->connection->fetchAssoc($select);

        return $attributes;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getConfigurableAttributeCodes()
    {
        $attributes = $this->prepareConfigurableAttributesFullInfo();

        return array_column($attributes, 'attribute_code');
    }

    /**
     * Return array of all configurable attributes in the current collection.
     * Array indexes are the attribute ids, array values the attribute code
     *
     * @return array
     */
    private function prepareConfigurableAttributesFullInfo()
    {
        if (null === $this->configurableAttributesInfo) {
            // build list of all configurable attribute codes for the current collection
            $this->configurableAttributesInfo = [];
            $this->superAttributeOptions = [];
            $this->productSuperAttributeIds = [];

            foreach ($this->getConfigurableProductAttributes() as $configurableAttribute) {
                $id = intval($configurableAttribute['product_super_attribute_id']);
                $attributeId = intval($configurableAttribute['attribute_id']);

                if ($attributeId && !isset($this->configurableAttributesInfo[$attributeId])) {
                    /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attributeModel */
                    $attributeModel = Mage::getSingleton('eav/config')
                        ->getAttribute('catalog_product', $attributeId);

                    $this->configurableAttributesInfo[$attributeId] = [
                        'attribute_id' => intval($attributeId),
                        'attribute_code' => $attributeModel->getAttributeCode(),
                        'label' => $attributeModel->getStoreLabel(),
                    ];
                }

                $this->superAttributeOptions[$id] = [
                    'attribute_id' => $attributeId,
                    'position' => intval($configurableAttribute['position']),
                    'id' => intval($configurableAttribute['product_super_attribute_id']),
                ];

                $this->productSuperAttributeIds[$configurableAttribute['product_id']][] = $id;
            }

            $superAttributeIds = array_keys($this->superAttributeOptions);
            $pricing = $this->getSuperAttributePricing($superAttributeIds);

            foreach ($pricing as $valuePrice) {
                $superAttributeId = $valuePrice['product_super_attribute_id'];
                $this->superAttributeOptions[$superAttributeId]['pricing'][$valuePrice['value_index']]
                    = $valuePrice;
            }
        }

        return $this->configurableAttributesInfo;
    }

    /**
     * Return array of ids of configurable products in the current product collection
     *
     * @return array
     */
    private function getConfigurableProductIds()
    {
        if (null === $this->configurableProductIds) {
            $this->configurableProductIds = array();
            $products = $this->productsData;

            foreach ($products as $product) {
                if ($product['type_id'] == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                    $this->configurableProductIds[] = $product['id'];
                }
            }
        }

        return $this->configurableProductIds;
    }

    /**
     * Return all associated simple products for the configurable products in
     * the current product collection.
     * Array key is the configurable product
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getSimpleProducts($storeId)
    {
        if (null === $this->simpleProducts) {
            $parentIds = $this->getConfigurableProductIds();
            /** @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product $resource */
            $resource = Mage::getModel('Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product');
            $childProduct = $resource->loadChildrenProducts($parentIds, $storeId);

            /** @var Product $product */
            foreach ($childProduct as $product) {
                $simpleId = $product['entity_id'];
                $parentIds = explode(',', $product['parent_ids']);
                $product['parent_ids'] = $parentIds;
                $this->simpleProducts[$simpleId] = $product;
            }
        }

        return $this->simpleProducts;
    }
}
