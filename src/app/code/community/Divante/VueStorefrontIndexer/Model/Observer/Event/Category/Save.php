<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Mage_Catalog_Model_Category as Category;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Category_Save
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Category_Save
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

        if ($dataObject instanceof Category) {
            $this->logEvent(
                $dataObject->getId(),
                Divante_VueStorefrontIndexer_Model_Indexer_Categories::TYPE,
                'save'
            );

            if ($dataObject->getIsActive() && $dataObject->getData('is_changed_product_list')) {
                $affectedProductIds = $dataObject->getData('affected_product_ids');

                foreach ($affectedProductIds as $productId) {
                    $this->logEvent(
                        $productId,
                        Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                        'save'
                    );
                }
            }
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