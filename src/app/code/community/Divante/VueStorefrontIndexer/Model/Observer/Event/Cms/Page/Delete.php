<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Cms_Pages as PagesIndexer;
use Mage_Cms_Model_Page as CmsPage;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Cms_Page_Delete
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Sven Ehmer <sven.ehmer@gastro-hero.de>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Cms_Page_Delete
{
    /**
     * @var EventHandler
     */
    protected $eventHandler;

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
        $dataObject = $observer->getEvent()->getData('page');
        $status = $observer->getEvent()->getData('status');

        if ($dataObject instanceof CmsPage && $status === 'success') {
            $this->logEvent(
                $dataObject->getId(),
                PagesIndexer::TYPE,
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