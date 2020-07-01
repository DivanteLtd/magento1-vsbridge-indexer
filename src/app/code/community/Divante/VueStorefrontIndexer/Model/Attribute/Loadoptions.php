<?php

use Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection as OptionCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Loadattributes
     */
    private $loadAttributes;
    /**
     * @var array
     */
    private $optionsByAttribute = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes constructor.
     */
    public function __construct()
    {
        $this->loadAttributes = Mage::getResourceSingleton('vsf_indexer/catalog_product_loadattributes');
    }

    /**
     * @param string    $attributeCode
     * @param int       $storeId
     *
     * @return array
     * @throws Mage_Exception
     *
     * @access public
     */
    public function execute($attributeCode, $storeId)
    {
        $attributeModel = $this->loadAttributes->getAttributeByCode($attributeCode);
        $attributeModel->setStoreId($storeId);

        return $this->getOptions($attributeModel);
    }

    /**
     * @param object $attribute
     *
     * @return array
     */
    protected function getOptions($attribute)
    {
        $attributeId = $attribute->getAttributeId();
        $storeId = $attribute->getStoreId();
        $key = $attributeId . '_' . $storeId;

        if (isset($this->optionsByAttribute[$key])) {
            return $this->optionsByAttribute[$key];
        }

        if ($this->useSourceModel($attribute)) {
            $source = $attribute->getSource();
            $values = $source->getAllOptions();
        } else {
            /** @var Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $options */
            $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
            $options->setOrder('sort_order', 'asc');
            $options->setAttributeFilter($attributeId)->setStoreFilter($storeId);
            $values = $this->toOptionArray($options);
        }

        $this->optionsByAttribute[$key] = $values;

        return $this->optionsByAttribute[$key];
    }

    /**
     * @param OptionCollection $collection
     *
     * @param array            $additional
     *
     * @return array
     */
    protected function toOptionArray(OptionCollection $collection, array $additional = [])
    {
        $res = [];
        $additional['value'] = 'option_id';
        $additional['label'] = 'value';
        $additional['sort_order'] = 'sort_order';

        foreach ($collection as $item) {
            $data = [];

            foreach ($additional as $code => $field) {
                $value = $item->getData($field);

                if ($field === 'sort_order') {
                    $value = (int)$value;
                }

                if ($field === 'option_id') {
                    $value = (string)$value;
                }

                $data[$code] = $value;
            }

            if ($data) {
                $res[] = $data;
            }
        }

        return $res;
    }

    /**
     * @param $attribute
     *
     * @return bool
     */
    private function useSourceModel($attribute)
    {
        $source = $attribute->getSource();

        return ($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Abstract
                && !($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Table));
    }
}
