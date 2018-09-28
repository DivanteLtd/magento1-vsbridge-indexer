<?php

use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Attribute
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Attribute implements MappingInterface
{
    /**
     * @var array
     */
    private $booleanProperties = [
        'is_required',
        'is_user_defined',
        'is_unique',
        'is_global',
        'is_visible',
        'is_searchable',
        'is_comparable',
        'is_visible_on_front',
        'is_html_allowed_on_front',
        'is_used_for_price_rules',
        'is_filterable_in_search',
        'used_in_product_listing',
        'used_for_sort_by',
        'is_configurable',
        'is_visible_in_advanced_search',
        'is_wysiwyg_enabled',
        'is_used_for_promo_rules',
    ];

    /**
     * @var array
     */
    private $longProperties = [
        'attribute_id',
        'id',
        'search_weight',
        'entity_type_id',
        'position',
    ];

    /**
     * @var array
     */
    private $stringProperties  = [
        'attribute_code',
        'attribute_model',
        'backend_model',
        'backend_table',
        'apply_to',
        'frontend_model',
        'frontend_input',
        'frontend_label',
        'frontend_class',
        'source_model',
        'default_value',
        'frontend_input_renderer',
        'apply_to',
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        $properties = [];

        foreach ($this->booleanProperties as $property) {
            $properties[$property] = ['type' => 'boolean'];
        }

        foreach ($this->longProperties as $property) {
            $properties[$property] = ['type' => 'long'];
        }

        foreach ($this->stringProperties as $property) {
            $properties[$property] = ['type' => 'string'];
        }

        $properties['options'] = [
            'properties' => [
                'value' => [
                    'type' => 'text',
                    'index' => 'not_analyzed',
                ]
            ]
        ];

        $mapping = ['properties' => $properties];

        $mappingObject = new Varien_Object($mapping);
        Mage::dispatchEvent('elasticsearch_attribute_mapping_properties', ['mapping' => $mappingObject]);

        return $mappingObject->getData();
    }
}
