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
     * @return bool
     */
    public function clearCache()
    {
        return (bool)$this->getConfigParam('clear_cache');
    }

    /**
     * @return string
     */
    public function getVsfBaseUrl()
    {
        return (string)$this->getConfigParam('vsf_base_url');
    }

    /**
     * @return string
     */
    public function getInvalidateCacheKey()
    {
        return (string)$this->getConfigParam('invalidate_cache_key');
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return (int)$this->getConfigParam('connection_timeout');
    }

    /**
     * @return array
     */
    public function getConnectionOptions()
    {
        $options = [
            'timeout' => $this->getTimeout()
        ];

        return $options;
    }

    /**
     * @param string $configField
     *
     * @return string|null|int
     */
    public function getConfigParam($configField)
    {
        $path = self::INDICES_SETTINGS_CONFIG_XML_PREFIX . '/' . $configField;

        return Mage::getStoreConfig($path);
    }
}
