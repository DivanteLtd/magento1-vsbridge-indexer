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
     * @var Mage_Catalog_Model_Resource_Category
     */
    private $categoryResource;

    /**
     * @var array
     */
    private $logEvents = [];

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->eventHandler = Mage::getSingleton('vsf_indexer/event_handler');
        $this->categoryResource = Mage::getSingleton('catalog/category');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        $dataObject = $observer->getEvent()->getData('data_object');

        if ($dataObject instanceof Category) {
            $categoryId = $dataObject->getId();

            /**
             * Cache event before delete
             */
            if ('catalog_category_delete_before' === $eventName) {
                $this->logEvents = [];
                $this->logEvents[] = [
                    'id' => $categoryId,
                    'type' => Divante_VueStorefrontIndexer_Model_Indexer_Categories::TYPE,
                    'action' => 'delete',
                ];

                /**
                 * Update parent categories
                 */
                $parentIds = $dataObject->getParentIds();

                foreach ($parentIds as $parentId) {
                    $this->logEvents[] = [
                        'id' => $parentId,
                        'type' => Divante_VueStorefrontIndexer_Model_Indexer_Categories::TYPE,
                        'action' => 'save',
                    ];
                }

                $productsPosition = $this->categoryResource->getProductsPosition($dataObject);
                $productIds = array_keys($productsPosition);

                foreach ($productIds as $productId) {
                    $this->logEvents[] = [
                        'id' => $productId,
                        'type' => Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                        'action' => 'save',
                    ];
                }
            }

            /**
             * Log event after category has been deleted
             */
            if ('catalog_category_delete_after' === $eventName) {
                foreach ($this->logEvents as $event) {
                    $this->eventHandler->logEvent(
                        $event['id'],
                        $event['type'],
                        $event['action']
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