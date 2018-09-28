<?php

use Divante_VueStorefrontIndexer_Api_IndexerInterface as IndexerInterface;
use Divante_VueStorefrontIndexer_Model_Indexer_Action_Category as CategoryAction;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler as IndexerHandler;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Categories implements IndexerInterface
{
    const TYPE = 'category';

    /**
     * @var IndexerHandler
     */
    private $indexHandler;

    /**
     * @var CategoryAction
     */
    private $action;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Category constructor.
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

        $this->action = Mage::getSingleton('vsf_indexer/indexer_action_category');
    }

    /**
     * @inheritdoc
     */
    public function updateDocuments(array $ids = [])
    {
        $stores = Mage::app()->getStores();

        /** @var Store $store */
        foreach ($stores as $store) {
            $this->indexHandler->saveIndex($this->action->rebuild($store->getId(), $ids), $store);
            $this->indexHandler->cleanUpByTransactionKey($store);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteDocuments(array $ids)
    {
        $stores = Mage::app()->getStores();

        if (!empty($ids)) {
            foreach ($stores as $store) {
                $this->indexHandler->deleteDocuments($ids, $store);
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
