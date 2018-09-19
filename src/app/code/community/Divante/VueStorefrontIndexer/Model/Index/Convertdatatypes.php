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
        'string' => 'string',
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
                foreach ($mappingProperties as $fieldKey => $options) {
                    $type = $this->getCastType($options['type']);

                    if ($type && isset($indexData[$fieldKey]) && (null !== $indexData[$fieldKey])) {
                        settype($docs[$docId][$fieldKey], $type);
                    }
                }

                if (isset($indexData['configurable_children'])) {
                    foreach ($docs[$docId]['configurable_children'] as $key => $child) {
                        foreach ($mappingProperties as $fieldKey => $options) {
                            $type = $this->getCastType($options['type']);

                            if ($type && isset($child[$fieldKey]) && (null !== $child[$fieldKey])) {
                                settype($docs[$docId]['configurable_children'][$key][$fieldKey], $type);
                            }
                        }
                    }
                }

                if (isset($indexData['children_data'])) {
                    foreach ($indexData['children_data'] as $index => $subCategory) {
                        $subCategory = $this->convertChildrenData($subCategory, $mappingProperties);
                        $docs[$docId]['children_data'][$index] = $subCategory;
                    }
                }
            }
        }

        return $docs;
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

        foreach ($childrenData as &$subCategory) {
            foreach ($mappingProperties as $fieldKey => $options) {
                $type = $this->getCastType($options['type']);

                if ($type && isset($subCategory[$fieldKey]) && (null !== $subCategory[$fieldKey])) {
                    settype($subCategory[$fieldKey], $type);
                }
            }

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
