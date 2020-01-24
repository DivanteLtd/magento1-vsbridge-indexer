<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;
use Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Metaattributes as AttributesResource;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Metaattributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Metaattributes implements DataSourceInterface
{

    /**
     * @var array
     */
    protected $requiredColumns = [
        'is_visible_on_front',
        'is_visible',
        'attribute_id',
        'entity_type_id',
        'frontend_input',
        'attribute_id',
        'frontend_input',
        'is_user_defined',
        'is_comparable',
        'attribute_code',
    ];

    /**
     * @var AttributesResource
     */
    private $attributesResource;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Attribute_Loadoptions
     */
    protected $loadOptions;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Convertvalue
     */
    protected $convertValue;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Index_Mapping_Attribute
     */
    protected $attributeMapping;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
     */
    protected $catalogSettings;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Metaattributes constructor.
     */
    public function __construct()
    {
        $this->catalogSettings = Mage::getSingleton('vsf_indexer/config_catalogsettings');
        $this->loadOptions = Mage::getSingleton('vsf_indexer/attribute_loadoptions');
        $this->attributesResource = Mage::getResourceSingleton('vsf_indexer/catalog_product_metaattributes');
        $this->convertValue = Mage::getSingleton('vsf_indexer/convertvalue');
        $this->attributeMapping = Mage::getSingleton('vsf_indexer/index_mapping_attribute');
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        if ($this->catalogSettings->canExportAttributesMetadata()) {
            foreach ($indexData as &$productDTO) {
                $metaAttributes = $this->getAttributeMetaData($productDTO, $storeId);
                $productDTO['attributes_metadata'] = $metaAttributes;
            }
        }

        return $indexData;
    }

    /**
     * @param array $productDTO
     * @param int   $storeId
     *
     * @return array
     * @throws Mage_Exception
     *
     * @access private
     */
    private function getAttributeMetaData(array $productDTO, $storeId)
    {
        $attributes = $this->attributesResource->execute();

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute['attribute_code'];
            $storeLabels = $this->getStoreLabelsByAttributeId($attribute['attribute_id']);

            if (isset($storeLabels[$storeId])) {
                $attributeData['frontend_label'] = $storeLabels[$storeId];
            }

            $metaAttribute = $this->copyColumns($attribute);
            $productOptions = $this->getOptions($productDTO, $attributeCode, $storeId);
            $metaAttribute['options'] = $productOptions;

            $metaAttributes[] = $metaAttribute;
        }

        return $metaAttributes;
    }

    /**
     * @param array     $productDTO
     * @param string    $attributeCode
     * @param int       $storeId
     *
     * @return array
     * @throws Mage_Exception
     *
     * @access private
     */
    private function getOptions(array $productDTO, $attributeCode, $storeId)
    {
        $productOptionIds = [];

        if ('configurable' === $productDTO['type_id']) {
            $productOptionIds = $this->getOptionsForChildren($productDTO, $attributeCode);
        }

        if (isset($productDTO[$attributeCode])) {
            $productOptionIds[] = $productDTO[$attributeCode];
        }

        $allAttributeOptions = $this->loadOptions->execute($attributeCode, $storeId);
        $productOptions = [];

        foreach ($allAttributeOptions as $option) {
            $optionId = $option['value'];

            if (in_array($optionId, $productOptionIds)) {
                $productOptions[] = $option;
            }
        }

        return $productOptions;
    }

    /**
     * @param array $productDTO
     * @param string $attributeCode
     *
     * @return array
     */
    private function getOptionsForChildren(array $productDTO, $attributeCode)
    {
        $options = [];

        foreach ($productDTO['configurable_children'] as $child) {
            if (isset($child[$attributeCode])) {
                $options[] = $child[$attributeCode];
            }
        }

        return $options;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    private function copyColumns(array $row)
    {
        $attribute['id'] = (int)$row['attribute_id'];
        $attribute['default_frontend_label'] = $row['frontend_label'];

        foreach ($this->requiredColumns as $column) {
            $attribute[$column] = $this->convertValue->execute($this->attributeMapping, $column, $row[$column]);
        }

        return $attribute;
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
