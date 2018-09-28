<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_ElasticSearch_Client_Builder
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Elasticsearch_Client_Builder
{

    /**
     * @var array
     */
    private $defaultOptions = [
        'host' => 'localhost',
        'port' => '9200',
        'enable_http_auth' => false,
        'auth_user' => null,
        'auth_pwd' => null,
    ];

    /**
     * @param array $options
     *
     * @return \Elasticsearch\Client
     */
    public function build(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);
        $clientBuilder = \Elasticsearch\ClientBuilder::create();
        $host = $this->getHost($options);

        if (!empty($host)) {
            $clientBuilder->setHosts([$host]);
        }

        return $clientBuilder->build();
    }

    /**
     * Return hosts config used to connect to the cluster.
     *
     * @param array $options Client options.
     *
     * @return array
     */
    private function getHost(array $options)
    {
        $scheme = 'http';

        if (isset($options['enable_https_mode'])) {
            $scheme = 'https';
        } elseif (isset($options['schema'])) {
            $scheme = $options['schema'];
        }

        $currentHostConfig = [
            'host' => $options['host'],
            'port' => $options['port'],
            'scheme' => $scheme,
        ];

        if ($options['enable_http_auth']) {
            $currentHostConfig['user'] = $options['auth_user'];
            $currentHostConfig['pass'] = $options['auth_pwd'];
        }

        return $currentHostConfig;
    }
}
