<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Review_Rating
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Christian Ewald <c.ewald@impericon.com>
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Review_Rating implements DataSourceInterface
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Rating
     */
    protected $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Review_Rating constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_rating');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $reviewResultIds = array_column($indexData, 'id');
        $ratings = $this->resourceModel->getRatings($storeId, $reviewResultIds);

        foreach ($ratings as $rating) {
            $reviewId = (int) $rating['review_id'];
            $ratingId = (int) $rating['rating_id'];
            $title = $this->resourceModel->getRatingTitleById($ratingId, $storeId);

            $indexData[$reviewId]['ratings'][] = [
                'percent' => (int) $rating['percent'],
                'value' => (int) $rating['value'],
                'title' => $title,
            ];
        }

        return $indexData;
    }
}
