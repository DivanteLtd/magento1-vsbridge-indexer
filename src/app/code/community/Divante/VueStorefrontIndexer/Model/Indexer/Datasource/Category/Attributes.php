<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Attributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Attributes implements DataSourceInterface
{
    /**
     * @var array
     */
    private $longProperties = [
        'level',
        'id',
        'parent_id',
        'position',
        'children_count',
    ];

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Attributes
     */
    private $attributeResourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Children
     */
    private $childrenResourceModel;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Data_Filter
     */
    private $dataFilter;

    /**
     * @var array
     */
    private $childrenRowAttributes = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Attributes constructor.
     */
    public function __construct()
    {
        $this->attributeResourceModel = Mage::getResourceModel('vsf_indexer/catalog_category_attributes');
        $this->childrenResourceModel = Mage::getResourceModel('vsf_indexer/catalog_category_children');
        $this->dataFilter = Mage::getModel(
            'vsf_indexer/data_filter',
            ['long_properties' => $this->longProperties]
        );
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $attributes = $this->attributeResourceModel->loadAttributesData($storeId, array_keys($indexData));

        foreach ($attributes as $entityId => $attributesData) {
            $categoryData = array_merge($indexData[$entityId], $attributesData);
            $indexData[$entityId] = $this->prepareCategory($categoryData);
        }

        foreach ($indexData as $categoryId => $categoryData) {
            $children = $this->childrenResourceModel->loadChildren($categoryData, $storeId);
            $sortChildrenById = $this->sortChildrenById($children);
            unset($children);

            $this->childrenRowAttributes =
                $this->attributeResourceModel->loadAttributesData($storeId, array_keys($sortChildrenById));

            $childrenData = $this->plotTree($sortChildrenById, $categoryId);

            $indexData[$categoryId]['children_data'] = $childrenData;
            $indexData[$categoryId]['children_count'] = count($childrenData);
        }

        return $indexData;
    }

    /**
     * @param array $children
     *
     * @return array
     */
    private function sortChildrenById(array $children)
    {
        $sortChildrenById = [];

        foreach ($children as $cat) {
            $sortChildrenById[$cat['entity_id']] = $cat;
            $sortChildrenById[$cat['entity_id']]['children_data'] = [];
        }

        return $sortChildrenById;
    }

    /**
     * @param array $categories
     * @param       $rootId
     *
     * @return array
     */
    private function plotTree(array $categories, $rootId)
    {
        $return = [];

        # Traverse the tree and search for direct children of the root
        foreach ($categories as $categoryId => $categoryData) {
            $parent = $categoryData['parent_id'];

            # A direct child is found
            if ($parent == $rootId) {
                # Remove item from tree (we don't need to traverse this again)
                unset($categories[$categoryId]);

                if (isset($this->childrenRowAttributes[$categoryId])) {
                    $categoryData = array_merge($categoryData, $this->childrenRowAttributes[$categoryId]);
                }

                $categoryData = $this->prepareCategory($categoryData);

                $categoryData['children_data'] = $this->plotTree($categories, $categoryId);
                $categoryData['children_count'] = count($categoryData['children_data']);
                $return[] = $categoryData;
            }
        }

        return empty($return) ? [] : $return;
    }

    /**
     * @param array $categoryDTO
     *
     * @return array
     */
    private function prepareCategory(array $categoryDTO)
    {
        $categoryDTO['id'] = intval($categoryDTO['entity_id']);
        unset($categoryDTO['entity_id']);
        unset($categoryDTO['entity_type_id']);
        unset($categoryDTO['attribute_set_id']);

        $removeFields = [
            'entity_id',
            'entity_type_id',
            'attribute_set_id',
            'created_at',
            'updated_at',
            'request_path',
            'description',
            'meta_description',
            'custom_layout_update',
            'meta_title',
            'filter_price_range',
            'include_in_menu',
            'custom_use_parent_settings',
            'custom_apply_to_products',
            'top_category',
            'show_products_from_category',
            'used_in_product_search',
            'custom_design',
            'page_layout',
            'landing_page',
            'custom_design_from',
            'custom_design_to',
            'meta_keywords'
        ];

        $categoryDTO = array_diff_key($categoryDTO, array_flip($removeFields));
        $categoryDTO = $this->filterData($categoryDTO);

        return $categoryDTO;
    }

    /**
     * @param array $categoryData
     *
     * @return array
     */
    private function filterData(array $categoryData)
    {
        return $this->dataFilter->execute($categoryData);
    }
}
