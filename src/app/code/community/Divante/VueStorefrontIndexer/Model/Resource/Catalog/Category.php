<?php

use Mage_Catalog_Model_Category as Category;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category
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
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('catalog_read');
    }

    /**
     * @param int   $storeId
     * @param array $categoryIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCategories($storeId = 1, array $categoryIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->filterByStore($storeId);

        if (!empty($categoryIds)) {
            $select->where('e.entity_id IN (?)', $categoryIds);
        }

        $select->where('e.entity_id > ?', $fromId);
        $select->limit($limit);
        $select->order('e.entity_id ASC');

        return $this->connection->fetchAll($select);
    }

    /**
     * @param $storeId
     *
     * @return Varien_Db_Select
     * @throws Mage_Core_Model_Store_Exception
     */
    public function filterByStore($storeId)
    {
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

        $rootId = Category::TREE_ROOT_ID;
        $rootCatIdExpr = $this->connection->quote("{$rootId}/{$rootCategoryId}");
        $catIdExpr = $this->connection->quote("{$rootId}/{$rootCategoryId}/%");

        $select = $this->connection->select()->from(
            ['e' => $this->coreResource->getTableName('catalog/category')]
        );

        $select->where(
            "path = {$rootCatIdExpr} OR path like {$catIdExpr}"
        );

        return $select;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCategoryProducts($storeId, array $productIds)
    {
        $select = $this->filterByStore($storeId);
        $table = $this->coreResource->getTableName('catalog_category_product');

        $select->reset(Varien_Db_Select::COLUMNS);
        $select->joinInner(
            ['cpi' => $table],
            "e.entity_id = cpi.category_id",
            [
                'category_id',
                'product_id',
                'position',
            ]
        )->where('cpi.product_id IN (?)', $productIds);

        return $this->connection->fetchAll($select);
    }
}
