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
        $this->resourceModel->getRatings($storeId, $reviewResultIds);

        foreach ($indexData as $key => &$review) {
            $review['ratings'] = $this->resourceModel->getRatingsByReviewId($review['id']);
        }

        return $indexData;
    }
}
