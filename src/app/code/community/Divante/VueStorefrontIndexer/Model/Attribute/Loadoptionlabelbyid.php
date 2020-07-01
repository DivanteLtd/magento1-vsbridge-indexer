<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptionlabelbyid
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptionlabelbyid
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
     */
    private $loadOptions;

    /**
     * Divante_VueStorefrontIndexer_Model_Attribute_Loadoptionlabelbyid constructor.
     */
    public function __construct()
    {
        $this->loadOptions = Mage::getSingleton('vsf_indexer/attribute_loadoptions');
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @param int $storeId
     *
     * @return string
     */
    public function execute($attributeCode, $optionId, $storeId)
    {
        $options = $this->loadOptions->execute($attributeCode, $storeId);

        foreach ($options as $option) {
            if ($optionId === (int)$option['value']) {
                return $option['label'];
            }
        }

        return '';
    }
}
