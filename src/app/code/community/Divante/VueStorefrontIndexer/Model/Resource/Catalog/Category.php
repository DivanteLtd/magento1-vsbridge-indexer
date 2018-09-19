<?php

use Mage_Catalog_Model_Resource_Category_Collection as CategoryCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category
{

    /**
     * @param int   $storeId
     * @param array $categoryIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCategories($storeId = 1, array $categoryIds = [], $fromId = 0, $limit = 1000)
    {
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);

        /** @var CategoryCollection $collection */
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addAttributeToFilter(
            'path',
            ['like' => "1/{$rootCategory->getId()}%"]
        );

        if (!empty($categoryIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $categoryIds]);
        }

        $collection->setPageSize($limit);
        $collection->addFieldToFilter('entity_id', ['gt' => $fromId]);

        return $collection->getItems();
    }
}
