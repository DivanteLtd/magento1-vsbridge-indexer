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
     * @throws Mage_Core_Exception
     */
    public function getProductConfigurableAttributes(array $product)
    {
        if ($product['type_id'] != Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            return [];
        }

        $attributeIds = $this->getProductConfigurableAttributeIds($product);
        $attributes = $this->getConfigurableAttributeFullInfo();
        $data = [];

        foreach ($attributeIds as $attributeId) {
            $code = $attributes[$attributeId]['attribute_code'];
            $data[$code] = $this->configurableAttributesInfo[$attributeId];
        }

        return $data;
    }

    /**
     * Return array of configurable attribute ids of the given configurable product.
     *
     * @param array $product
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    private function getProductConfigurableAttributeIds(array $product)
    {
        $attributes = $this->getConfigurableProductAttributes();
        $productId = $product['id'];

        if (!isset($attributes[$productId])) {
            Mage::throwException(
                sprintf('Product %d is not part of the current product collection', $productId)
            );
        }

        return explode(',', $attributes[$productId]['attribute_ids']);
    }

    /**
     * Load all configurable attributes used in the current product collection.
     *
     * @return array
     * @throws Mage_Core_Exception
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
     * This method actually would belong into a resource model, but for easier
     * reference I dropped it into the helper here.
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getConfigurableAttributesForProductsFromResource(array $productIds)
    {
        /** @var Mage_Core_Model_Resource_Helper_Mysql4 $resourceHelper */
        $resourceHelper = Mage::getResourceHelper('core');

        $select = $this->connection->select()
            ->from(
                $this->resource->getTableName('catalog/product_super_attribute'),
                [
                    'product_id',
                    'product_super_attribute_id',
                    'position',
                ]
            )
            ->group('product_id')
            ->where('product_id IN (?)', $productIds);
        $resourceHelper->addGroupConcatColumn($select, 'attribute_ids', 'attribute_id');

        $attributes = $this->connection->fetchAssoc($select);

        return $attributes;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getConfigurableAttributeCodes()
    {
        $attributes = $this->getConfigurableAttributeFullInfo();

        return array_column($attributes, 'attribute_code');
    }

    /**
     * Return array of all configurable attributes in the current collection.
     * Array indexes are the attribute ids, array values the attribute code
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    private function getConfigurableAttributeFullInfo()
    {
        if (null === $this->configurableAttributesInfo) {
            // build list of all configurable attribute codes for the current collection
            $this->configurableAttributesInfo = [];
            foreach ($this->getConfigurableProductAttributes() as $configurableAttribute) {
                $attributeIds = explode(',', $configurableAttribute['attribute_ids']);

                foreach ($attributeIds as $attributeId) {
                    if ($attributeId && !isset($this->configurableAttributesInfo[$attributeId])) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attributeModel */
                        $attributeModel = Mage::getSingleton('eav/config')
                            ->getAttribute('catalog_product', $attributeId);

                        $this->configurableAttributesInfo[$attributeId] = [
                            'attribute_id' => intval($attributeId),
                            'attribute_code' => $attributeModel->getAttributeCode(),
                            'position' => intval($configurableAttribute['position']),
                            'id' => intval($configurableAttribute['product_super_attribute_id']),
                            'label' => $attributeModel->getStoreLabel(),
                        ];
                    }
                }
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
