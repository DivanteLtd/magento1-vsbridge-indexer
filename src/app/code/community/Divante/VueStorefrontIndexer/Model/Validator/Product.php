<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Validator_Product
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Validator_Product
{

    /**
     * @param int $storeId
     * @param array $product
     *
     * @return bool
     */
    public function isSpecialPriceValid($storeId, array $product)
    {
        if (isset($product['special_price'])) {
            if (floatval($product['special_price']) > 0) {
                $specialPriceFrom = null;
                $specialPriceTo = null;

                if (isset($product['special_from_date'])) {
                    $specialPriceFrom = $product['special_from_date'];
                }

                if (isset($product['special_to_date'])) {
                    $specialPriceTo = $product['special_to_date'];
                }

                if (Mage::app()->getLocale()->isStoreDateInInterval($storeId, $specialPriceFrom, $specialPriceTo)) {
                    return true;
                }
            }
        }

        return false;
    }
}