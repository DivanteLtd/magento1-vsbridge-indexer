<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;
use Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection as OptionCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Attribute_Basic
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Attribute_Options implements DataSourceInterface
{

    const DEFAULT_SOURCE_MODEL = 'eav/entity_attribute_source_table';

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as $attributeId => &$attributeData) {
            $storeLabels = $this->getStoreLabelsByAttributeId($attributeId);

            if (isset($storeLabels[$storeId])) {
                $attributeData['frontend_label'] = $storeLabels[$storeId];
            }

            if ($this->useSource($attributeData)) {
                $attributeData['options'] = $this->getAttributeOptions($attributeData, $storeId);
            }
        }

        return $indexData;
    }

    /**
     * @param array $attributeData
     * @param int   $storeId
     *
     * @return array
     */
    public function getAttributeOptions(array $attributeData, $storeId)
    {
        $values = [];
        $source = (string)$attributeData['source_model'];
        $attributeId = $attributeData['attribute_id'];

        if ('' !== $source && self::DEFAULT_SOURCE_MODEL !== $source) {
            $sourceModel = Mage::getModel($source);

            if (false !== $sourceModel) {
                if ($sourceModel instanceof Mage_Eav_Model_Entity_Attribute_Source_Abstract) {
                    $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
                    $attribute->setStoreId($storeId);
                    $sourceModel->setAttribute($attribute);
                }

                $values = $sourceModel->getAllOptions(false);
            }
        } else {
            /** @var Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $options */
            $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
            $options->setOrder('sort_order', 'asc');
            $options->setAttributeFilter($attributeId)->setStoreFilter($storeId);
            $values = $this->toOptionArray($options);
        }

        return $values;
    }

    /**
     * @param OptionCollection $collection
     *
     * @param array $additional
     *
     * @return array
     */
    public function toOptionArray(OptionCollection $collection, array $additional = [])
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
     * @param array $attributeData
     *
     * @return bool
     */
    protected function useSource(array $attributeData)
    {
        return $attributeData['frontend_input'] === 'select' || $attributeData['frontend_input'] === 'multiselect'
               || $attributeData['source_model'] != '';
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    protected function getStoreLabelsByAttributeId($attributeId)
    {
        /** @var Mage_Eav_Model_Resource_Entity_Attribute $attributeResource */
        $attributeResource = Mage::getResourceModel('eav/entity_attribute');

        return $attributeResource->getStoreLabelsByAttributeId($attributeId);
    }
}
