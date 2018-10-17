<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Relation_Parentids as ParentResourceModel;

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
     * @var ParentResourceModel
     */
    private $parentResourceModel;

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->eventHandler = Mage::getSingleton('vsf_indexer/event_handler');
        $this->parentResourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_relation_parentids');
    }

    /**
     * TODO get and update parent ids as well
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $allProductIds = [];
        $productIds = $observer->getEvent()->getData('product_ids');
        $products = $observer->getEvent()->getData('products');

        if (is_array($productIds)) {
            $allProductIds = array_merge($allProductIds, $productIds);
        }

        if (is_array($products)) {
            $allProductIds = array_merge($allProductIds, $products);
        }

        $this->saveLogs($allProductIds);
        $this->updateParents($allProductIds);
    }

    /**
     * @param array $productIds
     */
    private function updateParents(array $productIds)
    {
        if (!empty($productIds)) {
            $parentIds = $this->parentResourceModel->execute($productIds);

            foreach ($parentIds as $parentId) {
                $this->logEvent(
                    $parentId,
                    Divante_VueStorefrontIndexer_Model_Indexer_Products::TYPE,
                    'save'
                );
            }
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