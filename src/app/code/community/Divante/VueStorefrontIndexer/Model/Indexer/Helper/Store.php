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
     * @var Divante_VueStorefrontIndexer_Model_Config_Generalsettings
     */
    protected $configSettings;

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->configSettings = Mage::getSingleton('vsf_indexer/config_generalsettings');
    }

    /**
     * @param null|int $storeId
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStores($storeId = null)
    {
        $allowStores = $this->configSettings->getStoresToIndex();
        $stores = [];

        if (null === $storeId) {
            $allStores = Mage::app()->getStores();

            foreach ($allStores as $store) {
                if (in_array($store->getId(), $allowStores)) {
                    $stores[] = $store;
                }
            }
        } elseif (in_array($storeId, $allowStores)) {
            $store = Mage::app()->getStore($storeId);
            $stores = [$store];
        }

        return $stores;
    }
}
