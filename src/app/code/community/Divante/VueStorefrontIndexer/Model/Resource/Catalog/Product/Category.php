<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Category
{

    /**
     * @var Mage_Core_Model_Resource
     */
    private $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * @var array Local cache for category names
     */
    private $categoryNameCache = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('catalog_read');
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function loadCategoryData($storeId, array $productIds)
    {
        $select       = $this->getCategoryProductSelect($productIds);
        $categoryData = $this->connection->fetchAll($select);

        $categoryIds = [];

        foreach ($categoryData as $categoryDataRow) {
            $categoryIds[] = $categoryDataRow['category_id'];
        }

        $storeCategoryName = $this->loadCategoryNames(array_unique($categoryIds), $storeId);

        foreach ($categoryData as &$categoryDataRow) {
            $categoryDataRow['name'] = '';
            if (isset($storeCategoryName[(int)$categoryDataRow['category_id']])) {
                $categoryDataRow['name'] = $storeCategoryName[(int)$categoryDataRow['category_id']];
            }
        }

        return $categoryData;
    }

    /**
     * @param array $categoryIds
     * @param int $storeId
     *
     * @return array|mixed
     * @throws Mage_Core_Exception
     */
    private function loadCategoryNames(array $categoryIds, $storeId)
    {
        $loadCategoryIds = $categoryIds;

        if (isset($this->categoryNameCache[$storeId])) {
            $loadCategoryIds = array_diff($categoryIds, array_keys($this->categoryNameCache[$storeId]));
        }

        $loadCategoryIds  = array_map('intval', $loadCategoryIds);

        if (!empty($loadCategoryIds)) {
            $select = $this->prepareCategoryNameSelect($loadCategoryIds, $storeId);

            foreach ($this->connection->fetchAll($select) as $row) {
                $categoryId = (int) $row['entity_id'];
                $this->categoryNameCache[$storeId][$categoryId] = $row['name'];
            }
        }

        return isset($this->categoryNameCache[$storeId]) ? $this->categoryNameCache[$storeId] : [];
    }

    /**
     * @param array $loadCategoryIds
     * @param int $storeId
     *
     * @return Varien_Db_Select
     * @throws Mage_Core_Exception
     */
    private function prepareCategoryNameSelect(array $loadCategoryIds, $storeId)
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $categoryCollection */
        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $categoryCollection->setStoreId($storeId);
        $categoryCollection->setStore($storeId);
        $categoryCollection->addFieldToFilter('entity_id', ['in' => $loadCategoryIds]);

        $categoryCollection->joinAttribute('name', 'catalog_category/name', 'entity_id');

        $select = $categoryCollection->getSelect();

        return $select;
    }

    /**
     * @param array $productIds
     *
     * @return Varien_Db_Select
     */
    private function getCategoryProductSelect($productIds)
    {
        $table = $this->coreResource->getTableName('catalog/category_product');

        $select = $this->connection->select()
            ->from(['cpi' => $table])
            ->where('cpi.product_id IN(?)', $productIds);

        return $select;
    }
}