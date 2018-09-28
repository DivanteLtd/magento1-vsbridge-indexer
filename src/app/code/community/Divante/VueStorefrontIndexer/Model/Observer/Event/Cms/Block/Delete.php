<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Cms_Blocks as BlocksIndexer;
use Mage_Cms_Model_Block as CmsBlock;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Cms_Block_Delete
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Cms_Block_Delete
{

    /**
     * @var EventHandler
     */
    private $eventHandler;

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->eventHandler = Mage::getSingleton('vsf_indexer/event_handler');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $dataObject = $observer->getEvent()->getData('data_object');

        if ($dataObject instanceof CmsBlock) {
            $this->logEvent(
                $dataObject->getId(),
                BlocksIndexer::TYPE,
                'delete'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function logEvent($id, $entityType, $eventType)
    {
        $this->eventHandler->logEvent(
            $id,
            $entityType,
            $eventType
        );
    }
}