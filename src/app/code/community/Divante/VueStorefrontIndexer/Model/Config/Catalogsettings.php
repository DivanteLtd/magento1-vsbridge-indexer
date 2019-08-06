<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
{
    const CATALOG_SETTINGS_CONFIG_XML_PREFIX = 'vuestorefront/catalog_settings';

    /**
     * @return bool
     */
    public function useMagentoUrlKeys()
    {
        return (bool)$this->getConfigParam('use_magento_url_keys');
    }

    /**
     * @return bool
     */
    public function useSimplePriceForConfigurableChildren()
    {
        return (bool)$this->getConfigParam('configurable_children_use_simple_price');
    }

    /**
     * @return bool
     */
    public function useImageInheritanceForConfigurableChildren()
    {
        return (bool)$this->getConfigParam('configurable_children_use_image_inheritance');
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllowedProductTypes($storeId)
    {
        $types = $this->getConfigParam('allowed_product_types', $storeId);

        if (null === $types || '' === $types) {
            $types = [];
        } else {
            $types = explode(',', $types);
        }

        return $types;
    }

    /**
     * @param string $configField
     * @param null|int $storeId
     *
     * @return string|null|int
     */
    public function getConfigParam($configField, $storeId = null)
    {
        $path = self::CATALOG_SETTINGS_CONFIG_XML_PREFIX . '/' . $configField;

        return Mage::getStoreConfig($path, $storeId);
    }
}
