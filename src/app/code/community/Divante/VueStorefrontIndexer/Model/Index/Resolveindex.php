<?php

use Divante_VueStorefrontIndexer_Model_Index_Operations as IndexOperation;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Resolveindex
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Resolveindex
{

    /**
     * @var IndexOperation
     */
    private $indexOperation;

    /**
     * @var array
     */
    private $cacheIndices = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Tools constructor.
     */
    public function __construct()
    {
        $this->indexOperation = Mage::getSingleton('vsf_indexer/index_operations');
    }

    /**
     * @param Mage_Core_Model_Store $storeId
     *
     * @return Divante_VueStorefrontIndexer_Model_Index_Index
     */
    public function getIndex(Mage_Core_Model_Store $store, $createNew = false)
    {
        $storeId = (int)$store->getId();

        if (isset($this->cacheIndices[$storeId])) {
            return $this->cacheIndices[$storeId];
        }

        if ($createNew) {
            $index = $this->indexOperation->createIndex('vue_storefront_catalog', $store);
        } else {
            try {
                $index = $this->indexOperation->getIndexByName('vue_storefront_catalog', $store);
            } catch (\Exception $e) {
                $index = $this->indexOperation->createIndex('vue_storefront_catalog', $store);
            }
        }

        $this->cacheIndices[$storeId] = $index;

        return $this->cacheIndices[$storeId];
    }
}
