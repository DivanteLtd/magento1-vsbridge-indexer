<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Attribute
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Attribute
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_attribute');
    }

    /**
     * @param array $attributeIds
     *
     * @return \Traversable
     */
    public function rebuild(array $attributeIds = [])
    {
        $lastAttributeId = 0;

        do {
            $attributes = $this->resourceModel->getAttributes($attributeIds, $lastAttributeId);

            foreach ($attributes as $attributeData) {
                $lastAttributeId = $attributeData['attribute_id'];
                #VSF requires this filed mapping as 'default_frontend_label'
		$attributeData['default_frontend_label'] = $attributeData['frontend_label'];
                $attributeData['id'] = $attributeData['attribute_id'];
                $attributeData = $this->filterData($attributeData);

                yield $lastAttributeId => $attributeData;
            }
        } while (!empty($attributes));
    }

    /**
     * @param array $attributeData
     *
     * @return array
     */
    private function filterData(array $attributeData)
    {
        if (isset($attributeData['position'])) {
            $attributeData['position'] = (int)$attributeData['position'];
        }

        return $attributeData;
    }
}
