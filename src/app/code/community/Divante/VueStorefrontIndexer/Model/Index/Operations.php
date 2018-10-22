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
    private $client;

    /**
     * @var IndexSettings
     */
    private $indexSettings;

    /**
     * @var array
     */
    private $indicesConfiguration;

    /**
     * @var array
     */
    private $indicesByName;

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

        if (!isset($this->indicesByName[$indexName])) {
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
        $indexName = $this->getIndexName($store);

        if (!isset($this->indicesByName[$indexName])) {
            if (!$this->indexExists($indexName)) {
                throw new \LogicException(
                    "{$indexIdentifier} index does not exist yet."
                );
            }

            $this->initIndex($indexIdentifier, $store);
        }

        return $this->indicesByName[$indexName];
    }

    /**
     * @param Store  $store
     *
     * @return string
     */
    public function getIndexName(Store $store)
    {
        $name = $this->indexSettings->getIndexNamePrefix();

        return $name . '_' . $store->getId();
    }

    /**
     * @param string $indexIdentifier
     * @param Store  $store
     *
     * @return Divante_VueStorefrontIndexer_Model_Index_Index
     */
    public function createIndex($indexIdentifier, Store $store)
    {
        $index = $this->initIndex($indexIdentifier, $store);
        $this->client->createIndex($index->getName(), []);

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
     * @param string $indexIdentifier
     * @param Mage_Core_Model_Store $store
     */
    public function deleteIndex($indexIdentifier, Store $store)
    {
        $index = $this->initIndex($indexIdentifier, $store);

        if ($this->client->indexExists($index->getName())) {
            $this->client->deleteIndex($index->getName());
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
     *
     * @return Index
     */
    private function initIndex($indexIdentifier, Store $store)
    {
        if (!isset($this->indicesConfiguration[$indexIdentifier])) {
            throw new \LogicException("No configuration found");
        }

        $config = $this->indicesConfiguration[$indexIdentifier];
        $types = $config['types'];

        $indexName = $this->getIndexName($store);
        $index = Mage::getModel(
            'vsf_indexer/index_index',
            [
                'name' => $indexName,
                'types' => $types,
            ]
        );

        $this->indicesByName[$indexName] = $index;

        return $this->indicesByName[$indexName];
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
