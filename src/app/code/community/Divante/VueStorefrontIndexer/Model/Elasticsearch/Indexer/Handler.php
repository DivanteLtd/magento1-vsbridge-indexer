<?php

use Divante_VueStorefrontIndexer_Model_Index_Operations as IndexOperation;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Client as Client;
use Divante_VueStorefrontIndexer_Model_Index_Convertdatatypes as ConvertDataTypes;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_ElasticSearch_Indexer_Handler
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Elasticsearch_Indexer_Handler
{

    /**
     * @var IndexOperation
     */
    protected $indexOperation;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Cache_Processor
     */
    protected $cacheProcessor;

    /**
     * @var string
     */
    protected $typeName;

    /**
     * @var string
     */
    protected $indexIdentifier;

    /**
     * @var int|string
     */
    protected $transactionKey;

    /**
     * @var ConvertDataTypes
     */
    protected $convertDataTypes;

    /**
     * constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (isset($params['type_name'])) {
            $this->typeName = $params['type_name'];
        }

        if (isset($params['index_identifier'])) {
            $this->indexIdentifier = $params['index_identifier'];
        }

        $this->cacheProcessor = Mage::getSingleton('vsf_indexer/cache_processor');
        $this->client = Mage::getSingleton('vsf_indexer/elasticsearch_client');
        $this->indexOperation = Mage::getSingleton('vsf_indexer/index_operations');
        /** @var Divante_VueStorefrontIndexer_Model_Transactionkey $transactionKeyModel */
        $transactionKeyModel = Mage::getSingleton('vsf_indexer/transactionkey');
        $this->transactionKey = $transactionKeyModel->load();
        $this->convertDataTypes = Mage::getSingleton('vsf_indexer/index_convertdatatypes');
    }

    /**
     * @param Store $store
     * @param array $docIds
     *
     * @return void
     */
    public function cleanUpByTransactionKey(Store $store, array $docIds = null)
    {
        $indexName = $this->indexOperation->getIndexName($store);

        if ($this->indexOperation->indexExists($indexName)) {
            $index = $this->indexOperation->getIndexByName($this->indexIdentifier, $store);
            $transactionKeyQuery = ['must_not' => ['term' => ['tsk' => $this->transactionKey]]];
            $query = ['query' => ['bool' => $transactionKeyQuery]];

            if ($docIds) {
                $query['query']['bool']['must']['terms'] = ['_id' => $docIds];
            }
            $query = [
                'index' => $index->getName(),
                'type' => $this->typeName,
                'body' => $query,
            ];

            $this->indexOperation->deleteByQuery($query);
        }
    }

    /**
     * @param array $docIds
     * @param Store $store
     *
     * @return void
     */
    public function deleteDocuments(array $docIds, Store $store)
    {
        $indexName = $this->indexOperation->getIndexName($store);

        if ($this->indexOperation->indexExists($indexName)) {
            $index = $this->indexOperation->getIndexByName($this->indexIdentifier, $store);

            $bulkRequest = $this->indexOperation->createBulk()->deleteDocuments(
                $index->getName(),
                $this->typeName,
                $docIds
            );

            $response = $this->indexOperation->executeBulk($bulkRequest);
            Mage::dispatchEvent(
                'search_engine_delete_documents_after',
                [
                    'data_type' => $this->typeName,
                    'bulk_response' => $response
                ]
            );
        }
    }

    /**
     * @param Traversable           $documents
     * @param Mage_Core_Model_Store $store
     * @param array                 $requireDataSources
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function updateIndex(\Traversable $documents, Store $store, array $requireDataSources)
    {
        $index = $this->getIndex($store);
        $type = $index->getType($this->typeName);
        $dataSources = [];

        foreach ($type->getDatasources() as $name => $datasource) {
            if (in_array($name, $requireDataSources)) {
                $dataSources[] = $datasource;
            }
        }

        if (empty($dataSources)) {
            return $this;
        }

        /** @var Divante_VueStorefrontIndexer_Model_Indexer_Batch $batch */
        $batch = Mage::getSingleton('vsf_indexer/indexer_batch');

        foreach ($batch->getItems($documents, $this->getBatchSize()) as $docs) {
            /** @var Divante_VueStorefrontIndexer_Api_DatasourceInterface $datasource */
            foreach ($dataSources as $datasource) {
                if (!empty($docs)) {
                    $docs = $datasource->addData($docs, $store->getId());
                }
            }

            $docs = $this->convertDataTypes->castFieldsUsingMapping($type, $docs);

            $bulkRequest = $this->indexOperation->createBulk()->updateDocuments(
                $index->getName(),
                $this->typeName,
                $docs
            );

            $response = $this->indexOperation->executeBulk($bulkRequest);
            $docs = null;

            Mage::dispatchEvent(
                'search_engine_update_documents_after',
                [
                    'data_type' => $this->typeName,
                    'bulk_response' => $response
                ]
            );
        }

        $this->indexOperation->refreshIndex($index);
    }

    /**
     * @param \Traversable $documents
     * @param Store       $store
     *
     * @return void
     */
    public function saveIndex(\Traversable $documents, Store $store)
    {
        $index = $this->getIndex($store);

        /** @var Divante_VueStorefrontIndexer_Model_Indexer_Batch $batch */
        $batch = Mage::getSingleton('vsf_indexer/indexer_batch');
        $type = $index->getType($this->typeName);

        foreach ($batch->getItems($documents, $this->getBatchSize()) as $docs) {
            /** @var Divante_VueStorefrontIndexer_Api_DatasourceInterface $datasource */
            foreach ($type->getDatasources() as $datasource) {
                if (!empty($docs)) {
                    $docs = $datasource->addData($docs, $store->getId());
                }
            }

            $docs = $this->convertDataTypes->castFieldsUsingMapping($type, $docs);

            $bulkRequest = $this->indexOperation->createBulk()->addDocuments(
                $index->getName(),
                $this->typeName,
                $docs
            );

            $response = $this->indexOperation->executeBulk($bulkRequest);
            Mage::dispatchEvent(
                'search_engine_save_documents_after',
                [
                    'data_type' => $this->typeName,
                    'bulk_response' => $response
                ]
            );

            $docs = null;
        }

        $this->indexOperation->refreshIndex($index);
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return Divante_VueStorefrontIndexer_Model_Index_Index
     */
    protected function getIndex(Store $store)
    {
        try {
            $index = $this->indexOperation->getIndexByName($this->indexIdentifier, $store);
        } catch (\Exception $e) {
            $index = $this->indexOperation->createIndex($this->indexIdentifier, $store);
        }

        return $index;
    }

    /**
     * @return int
     */
    protected function getBatchSize()
    {
        return $this->indexOperation->getBatchIndexingSize();
    }

    /**
     * @param int $storeId
     * @param array $ids
     */
    public function invalidateCache($storeId, array $ids)
    {
        $this->cacheProcessor->cleanCacheByDocIds($storeId, $this->typeName, $ids);
    }
}
