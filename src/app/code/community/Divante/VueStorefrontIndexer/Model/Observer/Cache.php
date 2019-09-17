<?php

use Divante_VueStorefrontIndexer_Api_BulkResponseInterface as BulkResponseInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Cache
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Cache
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Cache_Settings
     */
    protected $settings;
    /**
     * @var Divante_VueStorefrontIndexer_Model_Cache_Logger
     */
    protected $logger;
    /**
     * @var array
     */
    protected $cacheTags = [
        'category' => 'C',
        'product'  => 'P',
    ];

    /**
     * Divante_VueStorefrontIndexer_Model_Observer_Cache constructor.
     */
    public function __construct()
    {
        $this->settings = Mage::getSingleton('vsf_indexer/cache_settings');
        $this->logger = Mage::getSingleton('vsf_indexer/cache_logger');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if ($this->settings->clearCache()) {
            /** @var BulkResponseInterface $bulkResponse */
            $bulkResponse = $observer->getData('bulk_response');
            $dataType = $observer->getData('data_type');

            if (isset($this->cacheTags[$dataType])) {
                $successItems = $bulkResponse->getSuccessItems();
                $docIds = [];

                foreach ($successItems as $item) {
                    $operationType = current(array_keys($item));
                    $itemData = $item[$operationType];
                    $docIds[] = $itemData['_id'];
                }

                if (!empty($docIds)) {
                    $cacheInvalidateUrl = $this->getCacheInvalidateUrl($dataType, $docIds);

                    try {
                        $this->call($cacheInvalidateUrl);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @param string $uri
     */
    public function call($uri)
    {
        $http = new Varien_Http_Adapter_Curl();
        $config = $this->settings->getConnectionOptions();
        $http->setConfig($config);
        $http->write('GET', $uri);
        $response = $http->read();
        $http->close();
        $httpCode = Zend_Http_Response::extractCode($response);

        if ($httpCode !== 200) {
            $response = Zend_Http_Response::extractBody($response);
            $this->logger->debug($response);
        }
    }

    /**
     * @param string $type
     * @param array  $ids
     *
     * @return string
     */
    protected function getCacheInvalidateUrl($type, array $ids)
    {
        $baseUrl = $this->getInvalidateCacheUrl();
        $params = $this->prepareParams($type, $ids);
        $fullUrl = $baseUrl .= $params;

        return $fullUrl;
    }

    /**
     * @return string
     */
    protected function getInvalidateCacheUrl()
    {
        $url = $this->settings->getVsfBaseUrl();
        $url .= sprintf('invalidate?key=%s&tag=', $this->settings->getInvalidateCacheKey());

        return $url;
    }

    /**
     * @param string $type
     * @param array  $ids
     *
     * @return string
     */
    protected function prepareParams($type, array $ids)
    {
        $params = '';

        if (isset($this->cacheTags[$type])) {
            $cacheTag = $this->cacheTags[$type];
            $count = count($ids);

            foreach ($ids as $key => $id) {
                $params .= $cacheTag . $id;

                if ($key !== ($count - 1)) {
                    $params .= ',';
                }
            }
        }

        return $params;
    }
}
