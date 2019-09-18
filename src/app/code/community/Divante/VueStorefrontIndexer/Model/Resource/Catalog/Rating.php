<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Rating
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Christian Ewald <c.ewald@impericon.com>
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Rating
{
     /**
     * @var Mage_Core_Model_Resource
     */
    protected $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Review
     */
    private $reviewResource;

    /**
     * @var
     */
    private $ratingTitlesByStore;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('catalog_read');
        $this->reviewResource = Mage::getResourceSingleton('vsf_indexer/catalog_review');
    }

    /**
     * @param int   $storeId
     * @param array $reviewIds
     *
     * @return array
     */
    public function getRatings($storeId = 1, array $reviewIds)
    {
        $select = $this->connection->select()->from(
            ['e' => $this->coreResource->getTableName('rating_option_vote')],
            [
                'review_id',
                'rating_id',
                'percent',
                'value',
            ]
        );

        $select->where('e.review_id IN (?)', $reviewIds)->order('e.review_id ASC');

        return $this->connection->fetchAll($select);
    }

    /**
     * @param int $ratingId
     * @param int $storeId
     *
     * @return string
     */
    public function getRatingTitleById($ratingId, $storeId)
    {
        $titles = $this->getRatingTitle($storeId);

        return (string) $titles[$ratingId];
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getRatingTitle($storeId)
    {
        if (!isset($this->ratingTitlesByStore[$storeId])) {
            $entityId = $this->reviewResource->getProductEntityId();
            $connection = $this->getConnection();
            $table = $this->coreResource->getTableName('rating');
            $select = $connection->select()->from($table, ['rating_id']);
            $select->where('entity_id = ?', $entityId);
            $codeExpr = $connection->getIfNullSql('title.value', "{$table}.rating_code");
            $select->joinLeft(
                ['title' => $this->coreResource->getTableName('rating_title')],
                $connection->quoteInto("{$table}.rating_id = title.rating_id AND title.store_id = ?", $storeId),
                ['title' => $codeExpr]
            );

            $this->ratingTitlesByStore[$storeId] = $connection->fetchPairs($select);
        }

        return $this->ratingTitlesByStore[$storeId];
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
