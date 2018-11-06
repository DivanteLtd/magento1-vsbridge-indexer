<?php

use Mage_Catalog_Model_Resource_Category_Collection as CategoryCollection;

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
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);

        $select = $this->connection->select()->from(['e' => $this->coreResource->getTableName('catalog/category')]);

        if (!empty($categoryIds)) {
            $select->where('e.entity_id IN (?)', $categoryIds);
        }

        $path = "1/{$rootCategory->getId()}%";
        $select->where('path LIKE ?', $path);
        $select->where('e.entity_id > ?', $fromId);
        $select->limit($limit);
        $select->order('e.entity_id ASC');

        return $this->connection->fetchAll($select);
    }
}
