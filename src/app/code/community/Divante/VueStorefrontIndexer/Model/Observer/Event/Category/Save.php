<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Categories as CategoryIndexer;
use Divante_VueStorefrontIndexer_Model_Indexer_Products as ProductIndexer;
use Divante_VueStorefrontIndexer_Model_Indexer_Productcategories as ProductCategoryIndexer;
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
     * @var Divante_VueStorefrontIndexer_Model_Config_Generalsettings
     */
    private $configSettings;

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->eventHandler = Mage::getSingleton('vsf_indexer/event_handler');
        $this->configSettings = Mage::getSingleton('vsf_indexer/config_generalsettings');
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
                CategoryIndexer::TYPE,
                'save'
            );

            $this->updateParentCategories($dataObject);

            if ($dataObject->getData('is_changed_product_list')) {
                $affectedProductIds = $dataObject->getData('affected_product_ids');
                $entityType = ProductCategoryIndexer::ENTITY_TYPE;

                if ($this->configSettings->runFullProductUpdateOnPositionChanged()) {
                    $entityType = ProductIndexer::TYPE;
                }

                foreach ($affectedProductIds as $productId) {
                    $this->logEvent(
                        $productId,
                        $entityType,
                        'save'
                    );
                }
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     */
    private function updateParentCategories(Category $category)
    {
        $path = $category->getPath();
        $categoryIds = explode('/', $path);

        foreach ($categoryIds as $categoryId) {
            if ($categoryId != $category->getId() && $categoryId != Category::TREE_ROOT_ID) {
                $this->logEvent(
                    $categoryId,
                    CategoryIndexer::TYPE,
                    'save'
                );
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