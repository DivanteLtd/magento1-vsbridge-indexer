<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Mage_Catalog_Model_Product as Product;
use Mage_Catalog_Model_Product_Status as ProductStatus;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Save
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Save
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
     * TODO check in which store/webiste data has changed
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');

        if ($product instanceof Product) {
            $status = (int)$product->getStatus();

            if (false === $status) {
                /** @var Mage_Catalog_Model_Resource_Product $resourceModel */
                $resourceModel = Mage::getResourceModel('catalog/product');
                $status = (int)$resourceModel->getAttributeRawValue(
                    $product->getId(),
                    'status',
                    $product->getStoreId()
                );
            }

            if (ProductStatus::STATUS_DISABLED === $status) {
                $this->logEvent(
                    $product->getId(),
                    Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                    'delete'
                );
            } else {
                $this->logEvent(
                    $product->getId(),
                    Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                    'save'
                );
            }
        }
    }

    /**
     * @param $id
     * @param $entityType
     * @param $eventType
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