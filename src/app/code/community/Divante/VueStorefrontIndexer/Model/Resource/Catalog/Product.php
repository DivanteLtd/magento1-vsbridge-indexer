<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product
{

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
     */
    protected $catalogSettings;

    /**
     * @var int
     */
    private $isActiveAttributeId;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('catalog_read');
        $this->catalogSettings = Mage::getSingleton('vsf_indexer/config_catalogsettings');
    }

    /**
     * @param int   $storeId
     * @param array $productIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getProducts($storeId = 1, array $productIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->connection->select()->from(['e' => $this->coreResource->getTableName('catalog/product')]);

        if (!empty($productIds)) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $select->limit($limit);
        $select->where('e.entity_id > ?', $fromId);
        $select->order('e.entity_id ASC');
        $select = $this->addStatusFilter($select, $storeId);
        $select = $this->addWebsiteFilter($select, $storeId);
        $select = $this->addProductTypeFilter($select, $storeId);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param array $parentIds
     * @param int $storeId
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function loadChildrenProducts(array $parentIds, $storeId)
    {
        $select = $this->connection->select()->from(
            ['e' => $this->coreResource->getTableName('catalog/product')],
            [
                'entity_id',
                'sku',
            ]
        );

        $select->join(
            ['link_table' => $this->coreResource->getTableName('catalog/product_super_link')],
            'link_table.product_id = e.entity_id',
            []
        );

        $select = $this->addStatusFilter($select, $storeId);

        $select->where('link_table.parent_id IN (?)', $parentIds);
        $select->group('entity_id');

        /** @var Mage_Core_Model_Resource_Helper_Mysql4 $resourceHelper */
        $resourceHelper = Mage::getResourceHelper('core');
        $resourceHelper->addGroupConcatColumn($select, 'parent_ids', 'parent_id');
        $select = $this->addWebsiteFilter($select, $storeId);

        return $this->connection->fetchAll($select);
    }

    /**
     * @param Varien_Db_Select $select
     * @param int $storeId
     *
     * @return Varien_Db_Select
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function addWebsiteFilter(Varien_Db_Select $select, $storeId)
    {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $indexTable = $this->coreResource->getTableName('catalog_product_website');

        $visibilityJoinCond = $this->connection->quoteInto(
            "websites.product_id = e.entity_id AND websites.website_id = ?",
            $websiteId
        );

        $select->useStraightJoin(true)->join(['websites' => $indexTable], $visibilityJoinCond, []);

        return $select;
    }

    /**
     * @param Varien_Db_Select $select
     * @param int $storeId
     *
     * @return Varien_Db_Select
     */
    protected function addProductTypeFilter(Varien_Db_Select $select, $storeId)
    {
        $types = $this->catalogSettings->getAllowedProductTypes($storeId);

        if (!empty($types)) {
            $select->where('type_id IN (?)', $types);
        }

        return $select;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getEnableProductIds($storeId, array $productIds)
    {
        $select = $this->connection->select()->from(['e' => $this->coreResource->getTableName('catalog/product')]);
        $select->where('e.entity_id IN (?)', $productIds);
        $select->order('e.entity_id ASC');
        $select = $this->addStatusFilter($select, $storeId);
        $select = $this->addWebsiteFilter($select, $storeId);
        $select->reset(Varien_Db_Select::COLUMNS);
        $select->columns(['entity_id']);

        return $this->connection->fetchCol($select);
    }

    /**
     * @param Varien_Db_Select $select
     * @param int $storeId
     *
     * @return Varien_Db_Select
     */
    protected function addStatusFilter(Varien_Db_Select $select, $storeId)
    {
        $backendTable = $this->coreResource->getTableName(
            [
                'catalog/product',
                'int',
            ]
        );
        $checkSql = $this->connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $attributeId = (int)$this->getStatusAttributeId();

        $defaultJoinCond = $this->connection->quoteInto(
            "d.attribute_id = ? AND d.store_id = 0 AND d.entity_id = e.entity_id",
            $attributeId
        );

        $storeJoinCond = [
            $this->connection->quoteInto("c.attribute_id = ?", $attributeId),
            $this->connection->quoteInto("c.store_id = ?", $storeId),
            'c.entity_id = e.entity_id',
        ];

        $select->joinLeft(
            ['d' => $backendTable],
            $defaultJoinCond,
            []
        )->joinLeft(
            ['c' => $backendTable],
            implode(' AND ', $storeJoinCond),
            []
        )->where($checkSql . ' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        return $select;
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function getStatusAttributeId()
    {
        if ($this->isActiveAttributeId === null) {
            $bind = array(
                'catalog_product' => Mage_Catalog_Model_Product::ENTITY,
                'status' => 'status',
            );
            $select = $this->connection->select()
                ->from(array('a' => $this->coreResource->getTableName('eav/attribute')), array('attribute_id'))
                ->join(
                    array('t' => $this->coreResource->getTableName('eav/entity_type')),
                    'a.entity_type_id = t.entity_type_id'
                )
                ->where('entity_type_code = :catalog_product')
                ->where('attribute_code = :status');

            $this->isActiveAttributeId = $this->connection->fetchOne($select, $bind);
        }

        return $this->isActiveAttributeId;
    }
}
