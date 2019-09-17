<?php

use Divante_VueStorefrontIndexer_Api_IndexerInterface as IndexerInterface;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler as IndexerHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store as StoreHelper;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Jose Castaneda <jose@qbo.tech>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Reviews implements IndexerInterface
{
    const TYPE = 'review';

    /**
     * @var IndexerHandler
     */
    protected $indexHandler;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Indexer_Action_Reviews
     */
    protected $action;

    /**
     * @var StoreHelper
     */
    protected $storeHelper;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Reviews constructor.
     */
    public function __construct()
    {
        $this->indexHandler = Mage::getModel(
            'vsf_indexer/elasticsearch_indexer_handler',
            [
                'type_name' => self::TYPE,
                'index_identifier' => 'vue_storefront_catalog'
            ]
        );

        $this->action = Mage::getSingleton('vsf_indexer/indexer_action_reviews');
        $this->storeHelper = Mage::getSingleton('vsf_indexer/indexer_helper_store');
    }

    /**
     * @inheritdoc
     */
    public function updateDocuments($storeId = null, array $ids = [])
    {
        $stores = $this->storeHelper->getStores($storeId);

        /** @var Store $store */
        foreach ($stores as $store) {
            $this->indexHandler->saveIndex($this->action->rebuild($store->getId(), $ids), $store);
            $this->indexHandler->cleanUpByTransactionKey($store, $ids);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteDocuments($storeId = null, array $ids)
    {
        $stores = $this->storeHelper->getStores($storeId);

        if (!empty($ids)) {
            foreach ($stores as $store) {
                $this->indexHandler->deleteDocuments($ids, $store);
                $this->indexHandler->invalidateCache($store->getId(), $ids);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getTypeName()
    {
        return self::TYPE;
    }
}
