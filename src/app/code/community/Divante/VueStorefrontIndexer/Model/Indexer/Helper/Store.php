<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Helper_Store
{

    /**
     * @param null|int $storeId
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStores($storeId = null)
    {
        if (null !== $storeId) {
            $store = Mage::app()->getStore($storeId);
            $stores = [$store];
        } else {
            $stores = Mage::app()->getStores();
        }

        return $stores;
    }
}
