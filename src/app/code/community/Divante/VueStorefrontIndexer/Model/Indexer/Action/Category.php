<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Category
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Category
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category
     */
    protected $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Category constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_category');
    }

    /**
     * @param int   $storeId
     * @param array $categoryIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $categoryIds = [])
    {
        $lastCategoryId = 0;

        do {
            $categories = $this->resourceModel->getCategories($storeId, $categoryIds, $lastCategoryId);

            foreach ($categories as $category) {
                $lastCategoryId = $category['entity_id'];
                $categoryData['id'] = $category['entity_id'];
                $categoryData = $category;

                yield $lastCategoryId => $categoryData;
            }
        } while (!empty($categories));
    }
}
