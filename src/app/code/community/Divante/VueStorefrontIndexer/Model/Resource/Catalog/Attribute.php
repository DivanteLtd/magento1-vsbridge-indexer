<?php

use Mage_Catalog_Model_Resource_Product_Attribute_Collection as AttributeCollection;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute
{
    /**
     * @var Mage_Core_Model_Resource
     */
    private $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');
    }

    /**
     * @param array $attributeIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getAttributes(array $attributeIds = [], $fromId = 0, $limit = 100)
    {
        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $select = $attributeCollection->getSelect();
        $sourceModelCondition = [$this->connection->quoteInto('source_model != ?', 'core/design_source_design')];
        $sourceModelCondition[] = 'source_model IS NULL';
        $select->where(sprintf('(%s)', implode(' OR ', $sourceModelCondition)));

        if (!empty($attributeIds)) {
            $select->where('main_table.attribute_id IN (?)', $attributeIds);
        }

        $select->where('main_table.attribute_id > ?', $fromId)
            ->limit($limit)
            ->order('main_table.attribute_id');

        return $this->connection->fetchAll($select);
    }
}
