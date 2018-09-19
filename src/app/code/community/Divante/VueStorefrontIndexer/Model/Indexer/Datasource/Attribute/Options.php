<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

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
                $attributeData['options'] = $this->getAttributeOptions($attributeId, $storeId);
            }
        }

        return $indexData;
    }

    /**
     * @param int $attributeId
     * @param int $storeId
     *
     * @return array
     */
    private function getAttributeOptions($attributeId, $storeId)
    {
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
        $values  = $options->setAttributeFilter($attributeId)->setStoreFilter($storeId)->toOptionArray();

        return $values;
    }

    /**
     * @param array $attributeData
     *
     * @return bool
     */
    private function useSource(array $attributeData)
    {
        return $attributeData['frontend_input'] === 'select' || $attributeData['frontend_input'] === 'multiselect'
               || $attributeData['source_model'] != '';
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    private function getStoreLabelsByAttributeId($attributeId)
    {
        /** @var Mage_Eav_Model_Resource_Entity_Attribute $attributeResource */
        $attributeResource = Mage::getResourceModel('eav/entity_attribute');

        return $attributeResource->getStoreLabelsByAttributeId($attributeId);
    }
}
