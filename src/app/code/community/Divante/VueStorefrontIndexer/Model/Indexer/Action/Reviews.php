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
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product
     */
    private $resourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    private $dataFilter;

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
     * @param array $productIds
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
                $lastReviewId = $review['review_id'];
                $review['id'] = intval($review['review_id']);
                unset($review['review_id']);
                yield $lastReviewId => $this->filterData($review);
            }
        } while (!empty($reviews));
    }

    /**
     * @param array $productData
     *
     * @return mixed
     */
    private function filterData(array $productData)
    {
        return $this->dataFilter->execute($productData);
    }
}
