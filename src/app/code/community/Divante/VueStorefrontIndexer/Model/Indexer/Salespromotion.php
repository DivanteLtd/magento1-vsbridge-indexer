<?php

use Divante_VueStorefrontIndexer_Api_IndexerInterface as IndexerInterface;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler as IndexerHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store as StoreHelper;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Salespromotion
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Salespromotion implements IndexerInterface
{
    const TYPE = 'salespromotion';

    /**
     * @var IndexerHandler
     */
    private $indexHandler;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Indexer_Salespromotion
     */
    private $action;

    /**
     * @var StoreHelper
     */
    private $storeHelper;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Salespromotion constructor.
     */
    public function __construct()
    {
        $this->indexHandler = Mage::getModel(
            'vsf_indexer/elasticsearch_indexer_handler',
            [
                'type_name' => self::TYPE,
                'index_identifier' => 'vue_storefront_catalog',
                /* todo add different configuration by type = add support for ElasticSearch 6.**/
            ]
        );

        $this->action = Mage::getSingleton('vsf_indexer/indexer_action_salespromotion');
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
            $this->indexHandler->invalidateCache($store->getId(), $ids);
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
                $idsToDelete = $this->action->getDisableProducts($store->getId(), $ids);

                if (!empty($idsToDelete)) {
                    $this->indexHandler->deleteDocuments($idsToDelete, $store);
                    $this->indexHandler->invalidateCache($store->getId(), $ids);
                }
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
