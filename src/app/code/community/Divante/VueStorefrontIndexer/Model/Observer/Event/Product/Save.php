<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Relation_Parentids as ParentResourceModel;
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

            /**
             * TODO update parent only if specific attributes value changed
             */
            $this->updateParents($product);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    private function updateParents(Product $product)
    {
        if (Mage_Catalog_Model_Product_Type::TYPE_SIMPLE === $product->getTypeId()) {
            $productId = $product->getId();
            $parentIds = $this->parentResourceModel->execute([$productId]);

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