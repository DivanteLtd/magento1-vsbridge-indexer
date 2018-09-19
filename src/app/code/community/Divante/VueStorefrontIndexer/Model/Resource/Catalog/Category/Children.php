<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Children
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Category_Children
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
     * @var int
     */
    private $isActiveAttributeId;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');
    }

    /**
     * @param array $category
     * @param int   $storeId
     *
     * @return array
     */
    public function loadChildren(array $category, $storeId)
    {
        $childIds = $this->getChildrenIds($category, $storeId);

        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addIsActiveFilter();

        $select = $collection->getSelect();
        $select->where('e.entity_id IN (?)', $childIds);
        $select->order('path asc');
        $select->order('position asc');

        return $this->connection->fetchAll($select);
    }

    /**
     * @param array $category
     * @param int   $storeId
     * @param bool  $recursive
     *
     * @return array
     */
    private function getChildrenIds(array $category, $storeId, $recursive = true)
    {
        $attributeId = (int)$this->getIsActiveAttributeId();
        $backendTable = $this->coreResource->getTableName(
            [
                'catalog/category',
                'int',
            ]
        );
        $checkSql = $this->connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = array(
            'attribute_id' => $attributeId,
            'store_id' => $storeId,
            'scope' => 1,
        );
        $select = $this->getChildrenIdSelect($category, $recursive);
        $select
            ->joinLeft(
                array('d' => $backendTable),
                'd.attribute_id = :attribute_id AND d.store_id = 0 AND d.entity_id = m.entity_id',
                array()
            )
            ->joinLeft(
                array('c' => $backendTable),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = m.entity_id',
                array()
            )
            ->where($checkSql . ' = :scope');

        return $this->connection->fetchCol($select, $bind);
    }

    /**
     * @param array $category
     * @param bool  $recursive
     *
     * @return Varien_Db_Select
     */
    private function getChildrenIdSelect(array $category, $recursive = true)
    {
        $path = $category['path'];
        $level = $category['level'];

        $select = $this->connection->select()
            ->from(array('m' => $this->coreResource->getTableName('catalog/category')), 'entity_id')
            ->where($this->connection->quoteIdentifier('path') . ' LIKE ?', $path . '/%');

        if (!$recursive) {
            $select->where($this->connection->quoteIdentifier('level') . ' <= ?', $level + 1);
        }

        return $select;
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    private function getIsActiveAttributeId()
    {
        if ($this->isActiveAttributeId === null) {
            $bind = array(
                'catalog_category' => Mage_Catalog_Model_Category::ENTITY,
                'is_active' => 'is_active',
            );
            $select = $this->connection->select()
                ->from(array('a' => $this->coreResource->getTableName('eav/attribute')), array('attribute_id'))
                ->join(
                    array('t' => $this->coreResource->getTableName('eav/entity_type')),
                    'a.entity_type_id = t.entity_type_id'
                )
                ->where('entity_type_code = :catalog_category')
                ->where('attribute_code = :is_active');

            $this->isActiveAttributeId = $this->connection->fetchOne($select, $bind);
        }

        return $this->isActiveAttributeId;
    }
}