<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Relation_Parentids as ParentResourceModel;
use Mage_Catalog_Model_Product as Product;

/**
 * Class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Delete
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Observer_Event_Product_Delete
{

    /**
     * @var EventHandler
     */
    private $eventHandler;

    /**
     * @var ParentResourceModel
     */
    private $parentResourceModel;

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

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
        $this->parentResourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_relation_parentids');
        $this->resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $adapter */
        $this->connection = $this->resource->getConnection('catalog_read');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        $product = $observer->getEvent()->getData('product');

        if ($product instanceof Product) {
            $productId = $product->getId();

            if ('catalog_product_delete_before' === $eventName) {
                $this->logEvents[] = [
                    'id' => $productId,
                    'type' => Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                    'action' => 'delete',
                ];

                /**
                 * Update parent product data
                 */
                $this->updateParents($product);
            }

            if ('catalog_product_delete_after_done' === $eventName) {
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
     * @param Mage_Catalog_Model_Product $product
     */
    private function updateParents(Product $product)
    {
        /**
         * Update parent product data
         */
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $productId = $product->getId();
            $parentIds = $this->parentResourceModel->execute([$productId]);

            foreach ($parentIds as $parentId) {
                $this->logEvents[] = [
                    'id' => $parentId,
                    'type' => Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                    'action' => 'save',
                ];
            }
        }
    }
}
