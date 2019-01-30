<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Jose Castaneda <jose@qbo.tech>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Review
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
    }

    /**
     * @param int   $storeId
     * @param array $productIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getReviews($storeId = 1, array $reviewIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->connection->select()->from(['e' => $this->coreResource->getTableName('review')]);

        if (!empty($reviewIds)) {
            $select->where('e.review_id IN (?)', $reviewIds);
        }

        $select->limit($limit);
        $select->columns(['e.status_id AS review_status', 'e.entity_pk_value AS product_id']);
        $select->where('e.review_id > ?', $fromId)
               ->where('d.store_id = ?', $storeId);
        $select->order('e.review_id ASC');
        $select = $this->addStatusFilter($select);

        return $this->connection->fetchAll($select);
    }
    /**
     * @param Varien_Db_Select $select
     * @param int $storeId
     *
     * @return Varien_Db_Select
     */
    private function addStatusFilter(Varien_Db_Select $select)
    {
        $backendTable = 'review_detail';

        $defaultJoinCond = "d.review_id = e.review_id";

        $select->joinLeft(
            ['d' => $backendTable],
            $defaultJoinCond,
            ['d.title', 'd.nickname', 'd.customer_id', 'd.detail']
        )->where('e.status_id' . ' = ?', 1);

        return $select;
    }


}
