<?php

use Divante_VueStorefrontIndexer_Model_Index_Type as Type;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Cast_Fields
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Convertdatatypes
{

    /**
     * @var array
     */
    private $castMapping = [
        'integer' => 'int',
        'text' => 'string',
        'long' => 'int',
        'boolean' => 'bool',
        'double' => 'double',
    ];

    /**
     * @param Type $type
     * @param array $docs
     *
     * @return array
     */
    public function castFieldsUsingMapping(Type $type, array $docs)
    {
        $mapping = $type->getMapping();

        if ($mapping) {
            $mappingProperties = $mapping->getMappingProperties()['properties'];

            foreach ($docs as $docId => $indexData) {
                $indexData = $this->convert($indexData, $mappingProperties);

                if (isset($indexData['configurable_children'])) {
                    foreach ($indexData['configurable_children'] as $key => $child) {
                        $child = $this->convert($child, $mappingProperties);
                        $indexData['configurable_children'][$key] = $child;
                    }
                }

                if (isset($indexData['children_data'])) {
                    foreach ($indexData['children_data'] as $index => $subCategory) {
                        $subCategory = $this->convertChildrenData($subCategory, $mappingProperties);
                        $indexData['children_data'][$index] = $subCategory;
                    }
                }

                $docs[$docId] = $indexData;
            }
        }

        return $docs;
    }

    /**
     * @param array $indexData
     * @param array $mappingProperties
     *
     * @return array
     */
    private function convert(array $indexData, array $mappingProperties)
    {
        foreach ($mappingProperties as $fieldKey => $options) {
            if (isset($options['type'])) {
                $type = $this->getCastType($options['type']);

                if ($type && isset($indexData[$fieldKey]) && (null !== $indexData[$fieldKey])) {
                    if (is_array($indexData[$fieldKey])) {
                        foreach ($indexData[$fieldKey] as $value) {
                            settype($value, $type);
                        }
                    } else {
                        settype($indexData[$fieldKey], $type);
                    }
                }
            }
        }

        return $indexData;
    }

    /**
     * @param array $category
     * @param       $mappingProperties
     *
     * @return array
     */
    private function convertChildrenData(array $category, $mappingProperties)
    {
        $childrenData = $category['children_data'];

        foreach ($childrenData as $subCategory) {
            $subCategory = $this->convert($subCategory, $mappingProperties);
            $subCategory = $this->convertChildrenData($subCategory, $mappingProperties);
        }

        $category['children_data'] = $childrenData;

        return $category;
    }

    /**
     * @param string $esFieldType
     *
     * @return string|null
     */
    private function getCastType($esFieldType)
    {
        if (isset($this->castMapping[$esFieldType])) {
            return $this->castMapping[$esFieldType];
        }

        return null;
    }
}
