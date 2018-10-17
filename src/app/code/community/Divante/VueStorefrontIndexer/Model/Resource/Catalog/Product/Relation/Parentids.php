<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Relation_Parentids
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Relation_Parentids
{

    /**
     * @param array $productId
     *
     * @return array
     */
    public function execute(array $productId)
    {
        $parentIds = $this->getParentIds($productId);

        if (!empty($parentIds)) {
            return array_unique($parentIds);
        }

        return [];
    }

    /**
     * @param array $productId
     *
     * @return array
     */
    private function getParentIds($productId)
    {
        $configurableIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($productId);
        $bundleIds = Mage::getResourceSingleton('bundle/selection')
            ->getParentIdsByChild($productId);
        $groupIds = Mage::getResourceSingleton('catalog/product_link')
            ->getParentIdsByChild($productId, Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);

        return array_merge($configurableIds, $bundleIds, $groupIds);
    }
}
