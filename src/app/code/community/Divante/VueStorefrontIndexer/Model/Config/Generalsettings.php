<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Config_Generalsettings
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Config_Generalsettings
{
    const GENERAL_SETTINGS_CONFIG_XML_PREFIX = 'vuestorefront/general_settings';

    /**
     * @return bool
     */
    public function runFullProductUpdateOnPositionChanged()
    {
        return (bool)$this->getConfigParam('category_products_update');
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function canReindexStore($storeId)
    {
        $allowedStores = $this->getStoresToIndex();

        if (in_array($storeId, $allowedStores)) {
            return true;
        }

        return false;
    }

    /**
     * @return array|int|null|string
     */
    public function getStoresToIndex()
    {
        $stores = $this->getConfigParam('allowed_stores');

        if (null === $stores || '' === $stores) {
            $stores = [];
        } else {
            $stores = explode(',', $stores);
        }

        return $stores;
    }

    /**
     * @param string $configField
     * @param null|int $storeId
     *
     * @return string|null|int
     */
    public function getConfigParam($configField, $storeId = null)
    {
        $path = self::GENERAL_SETTINGS_CONFIG_XML_PREFIX . '/' . $configField;

        return Mage::getStoreConfig($path, $storeId);
    }
}
