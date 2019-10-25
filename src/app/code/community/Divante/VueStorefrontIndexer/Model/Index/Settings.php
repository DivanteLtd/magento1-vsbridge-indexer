<?php

use Mage_Core_Model_Store as Store;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Settings
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Settings
{

    const INDICES_SETTINGS_CONFIG_XML_PREFIX = 'vuestorefront/indices_settings';
    const INDICES_CONFIG_ROOT_NODE           = 'global/vsf_indexer/indices_config';

    /**
     * @return string
     */
    public function getIndexNamePrefix()
    {
        return (string)$this->getConfigParam('index_name');
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return (int)$this->getConfigParam('batch_indexing_size');
    }

    /**
     * @return int
     */
    public function getFieldsLimit()
    {
        return (int)$this->getConfigParam('fields_limit');
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

    /**
     * @param Store $store
     *
     * @return string
     */
    public function createIndexName(Store $store)
    {
        $name = $this->getIndexAlias($store);
        $currentDate = new \Zend_Date();

        return $name . '_' . $currentDate->getTimestamp();
    }

    /**
     * @param Store $store
     *
     * @return string
     */
    public function getIndexAlias(Store $store)
    {
        $indexNamePrefix = $this->getIndexNamePrefix();
        $storeIdentifier = $this->getStoreIdentifier($store);

        if ($storeIdentifier) {
            $indexNamePrefix .= '_' . $storeIdentifier;
        }

        return $indexNamePrefix;
    }

    /**
     * @param Store $store
     *
     * @return string
     */
    private function getStoreIdentifier(Store $store)
    {
        if (!$this->addIdentifierToDefaultStoreView()) {
            $defaultStoreView = Mage::app()->getDefaultStoreView();

            if ($defaultStoreView->getId() === $store->getId()) {
                return '';
            }
        }

        return ('code' === $this->getIndexIdentifier()) ? $store->getCode() : (string)$store->getId();
    }

    /**
     * @return string
     */
    private function getIndexIdentifier()
    {
        return (string)$this->getConfigParam('index_identifier');
    }

    /**
     * @return bool
     */
    private function addIdentifierToDefaultStoreView()
    {
        return (bool)$this->getConfigParam('add_identifier_to_default');
    }

    /**
     * @return array
     */
    public function getIndicesConfig()
    {
        $mappingConfig = Mage::getConfig()->getNode(self::INDICES_CONFIG_ROOT_NODE)->asArray();
        $config = [];

        foreach ($mappingConfig as $indexIdentifier => $indexConfigData) {
            $config[$indexIdentifier] = $this->initIndicesConfig($indexConfigData);
        }

        return $config;
    }

    /**
     * @param array $indexConfigData
     *
     * @return array
     */
    private function initIndicesConfig(array $indexConfigData)
    {
        $types = [];

        foreach ($indexConfigData['types'] as $typeName => $typeConfigData) {
            $datasources = [];

            foreach ($typeConfigData['datasources'] as $datasourceName => $datasourceClass) {
                $datasources[$datasourceName] = Mage::getSingleton($datasourceClass);
            }

            $params = [
                'name'         => $typeName,
                'data_sources' => $datasources,
            ];

            if (isset($typeConfigData['mapping'])) {
                $mapping = Mage::getModel($typeConfigData['mapping']);
                $params['mapping'] = $mapping;
            }

            $type = Mage::getModel(
                'vsf_indexer/index_type',
                $params
            );

            $types[$typeName] = $type;
        }

        return ['types' => $types];
    }

    /**
     * Get Language analysis index settings
     *
     * @return array
     */
    public function getEsConfig()
    {
        return array_merge(
            ["index.mapping.total_fields.limit" => $this->getFieldsLimit()],
            [
                "analysis" => [
                    "analyzer"  => [
                        "autocomplete"        => [
                            "tokenizer" => "autocomplete",
                            "filter"    => ["lowercase"],
                        ],
                        "autocomplete_search" => ["tokenizer" => "lowercase"],
                    ],
                    "tokenizer" => [
                        "autocomplete" => [
                            "type"        => "edge_ngram",
                            "min_gram"    => 2,
                            "max_gram"    => 10,
                            "token_chars" => ["letter"],
                        ],
                    ],
                ],
            ]
        );
    }
}
