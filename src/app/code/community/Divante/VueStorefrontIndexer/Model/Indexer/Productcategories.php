<?php

use Divante_VueStorefrontIndexer_Api_Indexer_UpdateInterface as IndexerUpdateInterface;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler as IndexerHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store as StoreHelper;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Productcategories
 *
 * Indexer used only to update "category" properties in product type
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Productcategories implements IndexerUpdateInterface
{
    const ENTITY_TYPE = 'product_categories';
    const ES_TYPE = 'product';

    /**
     * @var IndexerHandler
     */
    protected $indexHandler;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Indexer_Action_Product
     */
    protected $action;

    /**
     * @var StoreHelper
     */
    protected $storeHelper;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Attribute constructor.
     */
    public function __construct()
    {
        $this->indexHandler = Mage::getModel(
            'vsf_indexer/elasticsearch_indexer_handler',
            [
                'type_name' => self::ES_TYPE,
                'index_identifier' => 'vue_storefront_catalog',
                /* todo add different configuration by type = add support for ElasticSearch 6.**/
            ]
        );

        $this->action = Mage::getSingleton('vsf_indexer/indexer_action_product');
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
            $this->indexHandler->updateIndex($this->action->rebuild($store->getId(), $ids), $store, ['category_data']);
            $this->indexHandler->invalidateCache($store->getId(), $ids);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTypeName()
    {
        return self::ENTITY_TYPE;
    }
}
