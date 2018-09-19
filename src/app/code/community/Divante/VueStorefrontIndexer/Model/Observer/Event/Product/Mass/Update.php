<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Save
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Mass_Update
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
        $productIds = $observer->getEvent()->getData('product_ids');
        $products = $observer->getEvent()->getData('products');

        if (is_array($productIds)) {
            $this->saveLogs($productIds);
        }

        if (is_array($products)) {
            $this->saveLogs($products);
        }
    }

    /**
     * @param array $productIds
     */
    private function saveLogs(array $productIds)
    {
        foreach ($productIds as $productId) {
            $this->logEvent(
                $productId,
                Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                'save'
            );
        }
    }

    /**
     * @param int $id
     * @param string $entityType
     * @param string $eventType
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