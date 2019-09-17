<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Cache_Processor
 *
 * @package     Adika
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 */
class Divante_VueStorefrontIndexer_Model_Cache_Processor
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
    protected $defaultCacheTags = [
        'category' => 'C',
        'product' => 'P',
    ];
    /**
     * @var array
     */
    protected $cacheTags;

    /**
     * Divante_VueStorefrontIndexer_Model_Observer_Cache constructor.
     */
    public function __construct()
    {
        $this->settings = Mage::getSingleton('vsf_indexer/cache_settings');
        $this->logger = Mage::getSingleton('vsf_indexer/cache_logger');
    }

    /**
     * @param int $storeId
     * @param string $dataType
     * @param array $docIds
     */
    public function cleanCacheByDocIds($storeId, $dataType, array $docIds)
    {
        if ($this->settings->clearCache($storeId)) {
            if (!empty($docIds)) {
                $cacheInvalidateUrl = $this->getCacheInvalidateUrl($storeId, $dataType, $docIds);

                try {
                    $this->call($storeId, $cacheInvalidateUrl);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            } else {
                $cacheTags = $this->getCacheTags();

                if (isset($cacheTags[$dataType])) {
                    $this->cleanCacheByTags($storeId, [$dataType]);
                }
            }
        }

        return $this;
    }

    /**
     * @param int $storeId
     * @param array $tags
     */
    public function cleanCacheByTags($storeId, array $tags)
    {
        $baseUrl = $this->getInvalidateCacheUrl($storeId);
        $cacheTags = implode(',', $tags);
        $cacheInvalidateUrl = $baseUrl .= $cacheTags;

        try {
            $this->call($storeId, $cacheInvalidateUrl);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $storeId
     * @param string $uri
     */
    protected function call($storeId, $uri)
    {
        $http = new Varien_Http_Adapter_Curl();
        $config = $this->settings->getConnectionOptions($storeId);
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
     * @param int $storeId
     * @param string $type
     * @param array  $ids
     *
     * @return string
     */
    protected function getCacheInvalidateUrl($storeId, $type, array $ids)
    {
        $fullUrl = $this->getInvalidateCacheUrl($storeId);
        $params = $this->prepareTagsByDocIds($type, $ids);
        $fullUrl .= $params;

        return $fullUrl;
    }

    /**
     * @return string
     */
    protected function getInvalidateCacheUrl($storeId)
    {
        $url = $this->settings->getVsfBaseUrl($storeId);
        $url .= sprintf('invalidate?key=%s&tag=', $this->settings->getInvalidateCacheKey($storeId));

        return $url;
    }

    /**
     * @param string $type
     * @param array $ids
     *
     * @return string
     */
    public function prepareTagsByDocIds($type, array $ids)
    {
        $params = '';
        $cacheTags = $this->getCacheTags();

        if (isset($cacheTags[$type])) {
            $cacheTag = $cacheTags[$type];
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

    /**
     * @return array
     */
    public function getCacheTags()
    {
        if (null === $this->cacheTags) {
            $tags = new Varien_Object();
            $tags->setData('items', $this->defaultCacheTags);
            Mage::dispatchEvent('vsf_prepare_cache_tags', ['cache_tags' => $tags]);
            $this->cacheTags = $tags->getData('items');
        }

        return $this->cacheTags;
    }
}