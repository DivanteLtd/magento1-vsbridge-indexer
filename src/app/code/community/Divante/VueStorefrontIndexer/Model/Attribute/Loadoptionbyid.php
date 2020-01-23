<?php

use Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection as OptionCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptionbyid
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptionbyid
{
    const DEFAULT_SOURCE_MODEL = 'eav/entity_attribute_source_table';

    /**
     * @var array
     */
    private $optionsByAttribute = [];

    /**
     * @var Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
     */
    private $loadOptions;

    /**
     * LoadOptionById constructor.
     *
     * @param LoadOptions $loadOptions
     */
    public function __construct(LoadOptions $loadOptions)
    {
        $this->loadOptions = $loadOptions;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @param int $storeId
     *
     * @return array
     */
    public function execute($attributeCode, $optionId, $storeId): array
    {
        $options = $this->loadOptions->execute($attributeCode, $storeId);

        foreach ($options as $option) {
            if ($optionId === (int)$option['value']) {
                return $option;
            }
        }

        return [];
    }
}
