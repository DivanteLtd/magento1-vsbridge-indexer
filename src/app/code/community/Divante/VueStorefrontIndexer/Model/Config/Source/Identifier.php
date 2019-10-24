<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Config_Source_Identifier
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Config_Source_Identifier
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'id',
                'label' => Mage::helper('vsf_indexer')->__('Store ID'),
            ],
            [
                'value' => 'code',
                'label' => Mage::helper('vsf_indexer')->__('Store Code'),
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => Mage::helper('vsf_indexer')->__('Store ID'),
            'code' => Mage::helper('vsf_indexer')->__('Store Code'),
        ];
    }
}
