<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_System_Config_Source_Product_Type
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_System_Config_Source_Product_Type
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Mage_Catalog_Model_Product_Type $productType */
        $productType = Mage::getSingleton('catalog/product_type');

        return $productType->getAllOptions();
    }
}
