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
class Divante_VueStorefrontIndexer_Model_Index_Settings
{
    const INDICES_SETTINGS_CONFIG_XML_PREFIX = 'vuestorefront/indices_settings';
    const INDICES_CONFIG_ROOT_NODE = 'global/vsf_indexer/indices_config';

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
            $datasources  = [];

            foreach ($typeConfigData['datasources'] as $datasourceName => $datasourceClass) {
                $datasources[$datasourceName] = Mage::getSingleton($datasourceClass);
            }

            $params = [
                'name' => $typeName,
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
}
