<?php

use Divante_VueStorefrontIndexer_Api_IndexerInterface as IndexerInterface;
use Divante_VueStorefrontIndexer_Model_Index_Operations as IndexOperation;
use Divante_VueStoreFrontIndexer_Model_Event_Delete as DeleteEvent;

/**
 * Class Divante_VueStorefrontIndexer_Model_Tools
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Tools
{

    /**
     * @var string
     */
    const MAPPING_CONF_ROOT_NODE = 'global/vsf_indexer/indexer';

    /**
     * @var IndexOperation
     */
    private $indexOperation;

    /**
     * @var deleteEvent
     */
    private $deleteEvent;

    /**
     * Divante_VueStorefrontIndexer_Model_Tools constructor.
     */
    public function __construct()
    {
        $this->indexOperation = Mage::getSingleton('vsf_indexer/index_operations');
        $this->deleteEvent = Mage::getSingleton('vsf_indexer/event_delete');
    }

    /**
     * Force full reindex
     */
    public function fullReindex()
    {
        $mappingConfig = Mage::getConfig()->getNode(self::MAPPING_CONF_ROOT_NODE)->asArray();
        $types = array_keys($mappingConfig);

        foreach ($types as $type) {
            $this->reindexByType($type);
        }
    }

    /**
     * @param string $type
     */
    public function reindexByType($type)
    {
        $mappingConfig = Mage::getConfig()->getNode(self::MAPPING_CONF_ROOT_NODE)->asArray();

        if (isset($mappingConfig[$type])) {
            $config = $mappingConfig[$type];
            $class = $config['class'];
            $model = Mage::getModel($class);

            /** @var IndexerInterface $model */
            if ($model instanceof IndexerInterface) {
                $model->updateDocuments();
                $this->deleteEvent->execute($type);
            }
        }
    }

    /**
     * Reindex data
     */
    public function reindex()
    {
        $mappingConfig = Mage::getConfig()->getNode(self::MAPPING_CONF_ROOT_NODE)->asArray();

        $types = [];

        foreach ($mappingConfig as $config) {
            $class = $config['class'];
            $model = Mage::getModel($class);

            if ($model instanceof IndexerInterface) {
                $type = $model->getTypeName();
                $types[$type] = $model;
            }
        }

        /**
         * @var string $type
         * @var IndexerInterface $model
         */
        foreach ($types as $type => $model) {
            $fullIndexing = $this->runFullIndexing($type);

            if ($fullIndexing) {
                $model->updateDocuments();
                $this->deleteEvent->execute($type);
            } else {
                $this->runPartialIndexing($model);
            }
        }
    }

    /**
     * @param string $entityType
     *
     * @return bool
     */
    private function runFullIndexing($entityType)
    {
        /** @var Divante_VueStorefrontIndexer_Model_Resource_Event_Collection $collection */
        $collection = Mage::getResourceModel('vsf_indexer/event_collection');
        $collection->addFieldToFilter('entity', $entityType);
        $collection->setPageSize(1);
        $collection->addFieldToFilter('type', 'full');

        $values = $collection->getColumnValues('type');

        if (!empty($values)) {
            return true;
        }

        return false;
    }

    /**
     * @param Divante_VueStorefrontIndexer_Api_IndexerInterface $indexerModel
     */
    private function runPartialIndexing(IndexerInterface $indexerModel)
    {
        $type = $indexerModel->getTypeName();

        do {
            $ids = $this->getUpdateEventLists($type, 'delete');

            if (!empty($ids)) {
                $indexerModel->deleteDocuments($ids);
                $this->deleteEvent->execute($type, $ids);
            }
        } while (!empty($ids));

        do {
            $ids = $this->getUpdateEventLists($type);

            if (!empty($ids)) {
                $indexerModel->updateDocuments($ids);
                $this->deleteEvent->execute($type, $ids);
            }
        } while (!empty($ids));
    }

    /**
     * @param string $entityType
     * @param string $eventType
     *
     * @return array
     */
    private function getUpdateEventLists($entityType, $eventType = 'save')
    {
        $limit = $this->indexOperation->getBatchIndexingSize();
        /** @var Divante_VueStorefrontIndexer_Model_Resource_Event_Collection $collection */
        $collection = Mage::getResourceModel('vsf_indexer/event_collection');
        $collection->addFieldToFilter('entity', $entityType);
        $collection->setPageSize($limit);
        $collection->addFieldToFilter('type', $eventType);
        $collection->setOrder('created_at', 'ASC');

        return $collection->getColumnValues('entity_pk');
    }
}
