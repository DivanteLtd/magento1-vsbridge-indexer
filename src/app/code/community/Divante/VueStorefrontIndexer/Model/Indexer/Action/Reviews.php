<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Reviews
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Review
     */
    protected $resourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    protected $dataFilter;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category_Full constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_review');
        $this->dataFilter = Mage::getSingleton('vsf_indexer/data_filter');
    }

    /**
     * @param int $storeId
     * @param array $reviewIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $reviewIds = [])
    {
        $lastReviewId = 0;

        do {
            $reviews = $this->resourceModel->getReviews($storeId, $reviewIds, $lastReviewId);

            /** @var array $product */
            foreach ($reviews as $review) {
                $review['id'] = (int)($review['review_id']);
                $review['product_id'] = (int)$review['entity_pk_value'];
                $review['review_status'] = $review['status_id'];

                unset($review['review_id'], $review['entity_pk_value'], $review['status_id']);
                $lastReviewId = $review['id'];

                yield $lastReviewId => $this->filterData($review);
            }
        } while (!empty($reviews));
    }

    /**
     * @param array $reviewData
     *
     * @return mixed
     */
    protected function filterData(array $reviewData)
    {
        return $this->dataFilter->execute($reviewData);
    }
}
