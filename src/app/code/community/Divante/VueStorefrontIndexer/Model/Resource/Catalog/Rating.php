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
    protected $connection;

    /**
     * @var Array
     */
    protected $ratings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('catalog_read');
    }

    /**
     * @param int   $storeId
     * @param array $reviewIds
     *
     * @return array
     */
    public function getRatings($storeId = 1, array $reviewIds = [])
    {
        if (empty($reviewIds)) {
            return array();
        }

        if (!$this->ratings) {
            $select = $this->connection->select()->from(
                ['e' => $this->coreResource->getTableName('rating_option_vote')],
                [
                    'review_id',
                    'rating_id',
                    'percent',
                    'value',
                ]
            );
    
            $select->where('e.review_id IN (?)', $reviewIds);
    
            $select->joinLeft(
                ['r' => $this->coreResource->getTableName('rating')],
                'e.rating_id = r.rating_id',
                ['title' => 'rating_code']
            )->order('e.review_id ASC');
      
            $this->ratings = $this->connection->fetchAll($select);
        }

        return $this->ratings;
    }

    /**
     * @param int|string $reviewId
     * @return array
     */
    public function getRatingsByReviewId($reviewId)
    {
        $ratings = array();
        $ratingReviewIds = array_column($this->ratings, 'review_id');
        if (in_array($reviewId, $ratingReviewIds)) {
            foreach ($this->ratings as $rating) {
                if ((int) $rating['review_id'] === $reviewId) {
                    $ratings[] = [
                      'percent' => (int) $rating['percent'],
                      'value' => (int) $rating['value'],
                      'title' => (string) $rating['title'],
                    ];
                }
            }
        }

        return $ratings;
    }
}
