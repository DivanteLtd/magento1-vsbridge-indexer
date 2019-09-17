<?php

use Divante_VueStorefrontIndexer_Api_Indexer_UpdateInterface as IndexerUpdateInterface;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler as IndexerHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Categories as CategoryIndexer;
use Divante_VueStorefrontIndexer_Model_Indexer_Action_Category as CategoryAction;
use Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store as StoreHelper;
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
class Divante_VueStorefrontIndexer_Model_Indexer_Partialupdate_Category_Gridperpage implements IndexerUpdateInterface
{
    const ENTITY_TYPE = 'category_grid_per_page';

    /**
     * @var IndexerHandler
     */
    protected $indexHandler;

    /**
     * @var CategoryAction
     */
    protected $action;

    /**
     * @var StoreHelper
     */
    protected $storeHelper;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Category constructor.
     */
    public function __construct()
    {
        $this->indexHandler = Mage::getModel(
            'vsf_indexer/elasticsearch_indexer_handler',
            [
                /*
                 * type name in elastic
                 * TODO rename 'type_name' to 'type'
                */
                'type_name' => CategoryIndexer::TYPE,
                'index_identifier' => 'vue_storefront_catalog',
                /* todo add different configuration by type = add support for ElasticSearch 6.**/
            ]
        );

        $this->action = Mage::getSingleton('vsf_indexer/indexer_action_category');
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
            $this->indexHandler->updateIndex($this->action->rebuild($store->getId(), $ids), $store, ['grid_per_page']);
            $this->indexHandler->invalidateCache($store->getId(), $ids);
        }
    }

    /**
     * TODO rename to getName() or getIndexerName()
     * @inheritdoc
     */
    public function getTypeName()
    {
        return self::ENTITY_TYPE;
    }
}
