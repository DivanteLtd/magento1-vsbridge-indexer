<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Settings
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Cache_Settings
{
    const INDICES_SETTINGS_CONFIG_XML_PREFIX = 'vuestorefront/redis_cache_settings';

    /**
     * @param int $storeId
     * @return bool
     */
    public function clearCache($storeId)
    {
        return (bool)$this->getConfigParam('clear_cache', $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getVsfBaseUrl($storeId)
    {
        return (string)$this->getConfigParam('vsf_base_url', $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getInvalidateCacheKey($storeId)
    {
        return (string)$this->getConfigParam('invalidate_cache_key', $storeId);
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getTimeout($storeId)
    {
        return (int)$this->getConfigParam('connection_timeout', $storeId);
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getConnectionOptions($storeId)
    {
        $options = [
            'timeout' => $this->getTimeout($storeId)
        ];

        return $options;
    }

    /**
     * @param string $configField
     * @param int $storeId
     *
     * @return string|null|int
     */
    public function getConfigParam($configField, $storeId = null)
    {
        $path = self::INDICES_SETTINGS_CONFIG_XML_PREFIX . '/' . $configField;

        return Mage::getStoreConfig($path, $storeId);
    }
}
