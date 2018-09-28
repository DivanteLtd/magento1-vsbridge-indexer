<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Mage_Catalog_Model_Resource_Eav_Attribute as Attribute;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Attribute_Delete
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Attribute_Delete
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

        if ($dataObject instanceof Attribute) {
            $this->logEvent(
                $dataObject->getId(),
                Divante_VueStorefrontIndexer_Model_Indexer_Attributes::TYPE,
                'delete'
            );

            $this->logEvent(
                '',
                Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                'full'
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