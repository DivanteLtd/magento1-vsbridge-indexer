<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_ElasticSearch_Client_Configuration
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Elasticsearch_Client_Configuration
{

    const ES_CLIENT_CONFIG_XML_PREFIX = 'vuestorefront/es_client';

    /**
     * @return string
     */
    public function getHost()
    {
        return (string)$this->getConfigParam('host');
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return (string)$this->getConfigParam('port');
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return (bool)$this->getConfigParam('enable_https_mode') ? 'https' : 'http';
    }

    /**
     * @return bool
     */
    public function isHttpAuthEnabled()
    {
        $authEnabled = (bool)$this->getConfigParam('enable_http_auth');

        return $authEnabled && !empty($this->getHttpAuthUser()) && !empty($this->getHttpAuthPassword());
    }

    /**
     * @return string
     */
    public function getHttpAuthUser()
    {
        return (string)$this->getConfigParam('auth_user');
    }

    /**
     * @return string
     */
    public function getHttpAuthPassword()
    {
        return (string)$this->getConfigParam('auth_pwd');
    }

    /**
     * @param string $configField
     *
     * @return string|null
     */
    private function getConfigParam($configField)
    {
        $path = self::ES_CLIENT_CONFIG_XML_PREFIX . '/' . $configField;

        return Mage::getStoreConfig($path);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'scheme' => $this->getScheme(),
            'enable_http_auth' => $this->isHttpAuthEnabled(),
            'auth_user' => $this->getHttpAuthUser(),
            'auth_pwd' => $this->getHttpAuthPassword(),
        ];

        return $options;
    }
}
