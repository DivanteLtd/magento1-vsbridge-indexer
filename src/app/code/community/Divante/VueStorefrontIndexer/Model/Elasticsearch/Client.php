<?php

// Include the Elasticsearch required libraries
require_once 'vendor/autoload.php';

use Divante_VueStorefrontIndexer_Model_ElasticSearch_Client_Configuration as ClientConfiguration;
use Divante_VueStorefrontIndexer_Model_ElasticSearch_Client_Builder as ClientBuilder;

/**
 * Class Divante_VueStorefrontIndexer_Model_ElasticSearch_Client
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Elasticsearch_Client
{

    /**
     * @var \Elasticsearch\Client|null
     */
    private $esClient = null;

    /**
     * Divante_VueStorefrontIndexer_Model_ElasticSearch_Client constructor.
     */
    public function __construct()
    {
        /** @var ClientConfiguration $clientConfiguration */
        $clientConfiguration = Mage::getSingleton('vsf_indexer/elasticsearch_client_configuration');
        /** @var ClientBuilder $clientBuilder */
        $clientBuilder = Mage::getSingleton('vsf_indexer/elasticsearch_client_builder');
        $this->esClient = $clientBuilder->build($clientConfiguration->getOptions());
    }

    /**
     * @param array $bulkParams
     *
     * @return array
     */
    public function bulk(array $bulkParams)
    {
        return $this->esClient->bulk($bulkParams);
    }

    /**
     * @param string $indexName
     * @param array $indexSettings
     *
     * @return void
     */
    public function createIndex($indexName, array $indexSettings)
    {
        $this->esClient->indices()->create(['index' => $indexName, 'body' => $indexSettings]);
    }

    /**
     * @param string $indexName
     *
     * @return void
     */
    public function refreshIndex($indexName)
    {
        $this->esClient->indices()->refresh(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists($indexName)
    {
        return $this->esClient->indices()->exists(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     *
     * @return array
     */
    public function deleteIndex($indexName)
    {
        return $this->esClient->indices()->delete(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     * @param string $type
     * @param array $mapping
     */
    public function putMapping($indexName, $type, array $mapping)
    {
        $this->esClient->indices()->putMapping(
            [
                'index' => $indexName,
                'type'  => $type,
                'body'  => [$type => $mapping]
            ]
        );
    }

    /**
     * @param array $params
     */
    public function deleteByQuery(array $params)
    {
        $this->esClient->deleteByQuery($params);
    }
}
