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
    /**
     * @var Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
     */
    protected $loadOptions;

    public function __construct()
    {
        $this->loadOptions = Mage::getSingleton('vsf_indexer/attribute_loadoptions');
    }

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

            $options = $this->loadOptions->execute($attributeData['attribute_code'], $storeId);

            if ($this->useSource($attributeData)) {
                $attributeData['options'] = $options;
            }
        }

        return $indexData;
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
        $attributeResource = Mage::getResourceSingleton('eav/entity_attribute');

        return $attributeResource->getStoreLabelsByAttributeId($attributeId);
    }
}
