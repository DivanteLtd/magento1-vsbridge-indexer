<?php

use Divante_VueStorefrontIndexer_Model_Index_Operations as IndexOperation;

/**
 * Class Divante_VueStorefrontIndexer_Model_Tools
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Tools_Index
{
    /**
     * @var IndexOperation
     */
    private $indexOperation;

    /**
     * Divante_VueStorefrontIndexer_Model_Tools constructor.
     */
    public function __construct()
    {
        $this->indexOperation = Mage::getSingleton('vsf_indexer/index_operations');
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function deleteIndices()
    {
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $this->indexOperation->deleteIndex('vue_storefront_catalog', $store);
        }
    }
}
