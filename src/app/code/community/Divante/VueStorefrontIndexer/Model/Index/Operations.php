<?php

use Divante_VueStorefrontIndexer_Api_BulkRequestInterface as BulkRequestInterface;
use Divante_VueStorefrontIndexer_Api_BulkResponseInterface as BulkResponseInterface;
use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Client as Client;
use Divante_VueStorefrontIndexer_Model_Index_Index as Index;
use Divante_VueStorefrontIndexer_Model_Index_Settings as IndexSettings;
use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Operations
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Operations
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var IndexSettings
     */
    protected $indexSettings;

    /**
     * @var array
     */
    protected $indicesConfiguration;

    /**
     * @var array
     */
    protected $indicesByIdentifier;

    /**
     * Divante_VueStorefrontIndexer_Model_Index_Operations constructor.
     */
    public function __construct()
    {
        $this->client = Mage::getSingleton('vsf_indexer/elasticsearch_client');
        $this->indexSettings = Mage::getSingleton('vsf_indexer/index_settings');
        $this->indicesConfiguration = $this->indexSettings->getIndicesConfig();
    }

    /**
     * @param BulkRequestInterface $bulk
     *
     * @return BulkResponseInterface
    */
    public function executeBulk(BulkRequestInterface $bulk)
    {
        if ($bulk->isEmpty()) {
            throw new \LogicException('Can not execute empty bulk.');
        }

        $bulkParams = ['body' => $bulk->getOperations()];
        $rawBulkResponse = $this->client->bulk($bulkParams);

        /** @var BulkResponseInterface $bulkResponse */
        $bulkResponse = Mage::getModel('vsf_indexer/index_bulkresponse', $rawBulkResponse);

        if ($bulkResponse->hasErrors()) {
            $aggregateErrorsByReason = $bulkResponse->aggregateErrorsByReason();

            foreach ($aggregateErrorsByReason as $error) {
                $docIds = implode(', ', array_slice($error['document_ids'], 0, 10));
                $errorMessages = [
                    sprintf(
                        "Bulk %s operation failed %d times in index %s for type %s.",
                        $error['operation'],
                        $error['count'],
                        $error['index'],
                        $error['document_type']
                    ),
                    sprintf(
                        "Error (%s) : %s.",
                        $error['error']['type'],
                        $error['error']['reason']
                    ),
                    sprintf(
                        "Failed doc ids sample : %s.",
                        $docIds
                    ),
                ];

                /** @var Divante_VueStorefrontIndexer_Model_Logger $logger */
                $logger = Mage::getModel('vsf_indexer/logger');

                foreach ($errorMessages as $message) {
                    $logger->error($message);
                }

                $errorMessages = null;
            }

            $aggregateErrorsByReason = null;
        }

        return $bulkResponse;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function deleteByQuery(array $params)
    {
        $this->client->deleteByQuery($params);
    }

    /**
     * @param string $indexIdentifier
     *
     * @return bool
     */
    public function indexExists($indexName)
    {
        $exists = true;

        if (!isset($this->indicesByIdentifier[$indexName])) {
            $exists = $this->client->indexExists($indexName);
        }

        return $exists;
    }

    /**
     * @param string $indexIdentifier
     * @param Store  $store
     *
     * @return Index
     */
    public function getIndexByName($indexIdentifier, Store $store)
    {
        $indexName = $this->getIndexAlias($store);

        if (!isset($this->indicesByIdentifier[$indexName])) {
            if (!$this->indexExists($indexName)) {
                throw new \LogicException(
                    "{$indexIdentifier} index does not exist yet."
                );
            }

            $this->initIndex($indexIdentifier, $store, true);
        }

        return $this->indicesByIdentifier[$indexName];
    }

    /**
     * @param Store  $store
     *
     * @return string
     */
    public function getIndexAlias(Store $store)
    {
        return $this->indexSettings->getIndexAlias($store);
    }

    /**
     * @param Store $store
     *
     * @return string
     */
    private function createIndexName(Store $store)
    {
        return $this->indexSettings->createIndexName($store);
    }

    /**
     * @param string $indexIdentifier
     * @param Store  $store
     *
     * @return Divante_VueStorefrontIndexer_Model_Index_Index
     */
    public function createIndex($indexIdentifier, Store $store)
    {
        $index = $this->initIndex($indexIdentifier, $store, false);
        $this->client->createIndex(
            $index->getName(),
            $this->indexSettings->getEsConfig()
        );

        /** @var Divante_VueStorefrontIndexer_Model_Index_Type $type */
        foreach ($index->getTypes() as $type) {
            $mapping = $type->getMapping();

            if ($mapping instanceof MappingInterface) {
                $this->client->putMapping(
                    $index->getName(),
                    $type->getName(),
                    $mapping->getMappingProperties()
                );
            }
        }

        return $index;
    }

    /**
     * @inheritdoc
     */
    public function switchIndexer($indexName, $indexAlias)
    {
        $aliasActions   = [
            [
                'add' => [
                    'index' => $indexName,
                    'alias' => $indexAlias,
                ]
            ],
        ];

        $deletedIndices = [];
        $oldIndices = $this->client->getIndicesNameByAlias($indexAlias);

        foreach ($oldIndices as $oldIndexName) {
            if ($oldIndexName != $indexName) {
                $deletedIndices[] = $oldIndexName;
                $aliasActions[]   = [
                    'remove' => [
                        'index' => $oldIndexName,
                        'alias' => $indexAlias,
                    ]
                ];
            }
        }

        $this->client->updateAliases($aliasActions);

        foreach ($deletedIndices as $deletedIndex) {
            $this->client->deleteIndex($deletedIndex);
        }
    }

    /**
     * @param Index $index
     *
     * @return $this
     */
    public function refreshIndex(Index $index)
    {
        $this->client->refreshIndex($index->getName());

        return $this;
    }

    /**
     * Init the index object
     *
     * @param string $indexIdentifier
     * @param Store  $store
     * @param bool $existingIndex
     *
     * @return Index
     */
    private function initIndex($indexIdentifier, Store $store, $existingIndex)
    {
        if (!isset($this->indicesConfiguration[$indexIdentifier])) {
            throw new \LogicException("No configuration found");
        }

        $config = $this->indicesConfiguration[$indexIdentifier];
        $types = $config['types'];

        $indexName = $this->createIndexName($store);
        $indexAlias = $this->getIndexAlias($store);

        if ($existingIndex) {
            $indexName = $indexAlias;
        }

        $index = Mage::getModel(
            'vsf_indexer/index_index',
            [
                'name' => $indexName,
                'types' => $types,
                'isNew' => !$existingIndex,
                'identifier' => $indexAlias,
            ]
        );

        $this->indicesByIdentifier[$indexName] = $index;

        return $this->indicesByIdentifier[$indexName];
    }

    /**
     * @return BulkRequestInterface
     */
    public function createBulk()
    {
        return Mage::getModel('vsf_indexer/index_bulkrequest');
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return $this->indexSettings->getBatchIndexingSize();
    }
}
