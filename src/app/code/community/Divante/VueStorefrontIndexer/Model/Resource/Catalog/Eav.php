<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav
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
     * @var
     */
    private $attributeCollectionModel;

    /**
     * @var array
     */
    private $attributesById;

    /**
     * @var
     */
    private $entityType;

    /**
     * @var
     */
    private $valuesByEntityId;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');

        if (isset($params['entity_type'])) {
            $this->entityType = $params['entity_type'];
        }

        if (isset($params['collection_model'])) {
            $this->attributeCollectionModel = $params['collection_model'];
        }
    }

    /**
     * @param int   $storeId
     * @param array $entityIds
     *
     * @return array
     */
    public function loadAttributesData($storeId, array $entityIds, array $requiredAttributes = null)
    {
        $this->getAttributesById();
        $tableAttributes = [];
        $attributeTypes = [];
        $selects = [];

        foreach ($this->attributesById as $attributeId => $attribute) {
            if ($this->canReindex($attribute, $requiredAttributes)) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;

                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        foreach ($tableAttributes as $table => $attributeIds) {
            $select = $this->getLoadAttributesSelect($storeId, $table, $attributeIds, $entityIds);
            $selects[$table] = $select;
        }

        $this->valuesByEntityId = [];

        if (!empty($selects)) {
            foreach ($selects as $select) {
                $values = $this->connection->fetchAll($select);
                $this->prepareValues($values);
            }
        }

        return $this->valuesByEntityId;
    }

    /**
     * @param $attribute
     * @param $allowedAttributes
     *
     * @return bool
     */
    private function canReindex($attribute, $allowedAttributes)
    {
        if ($attribute->isStatic()) {
            return false;
        }

        if (null === $allowedAttributes) {
            return true;
        }

        return in_array($attribute->getAttributeCode(), $allowedAttributes);
    }

    /**
     * @param array $values
     *
     * @return array
     */
    private function prepareValues(array $values)
    {
        foreach ($values as $value) {
            $entityId = $value['entity_id'];
            $attribute = $this->attributesById[$value['attribute_id']];
            $attributeCode = $attribute->getAttributeCode();
            $this->valuesByEntityId[$entityId][$attributeCode] = $value['value'];
        }

        return $this->valuesByEntityId;
    }

    /**
     * @return Mage_Eav_Model_Entity_Type
     */
    private function getEntityType()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('eav/entity_type')->loadByCode($this->entityType);

        return $entityType;
    }

    /**
     * Retrieve attributes load select
     *
     * @param int    $storeId
     * @param string $table
     * @param array  $attributeIds
     * @param array  $entityIds
     *
     * @return Varien_Db_Select
     */
    private function getLoadAttributesSelect($storeId, $table, array $attributeIds, array $entityIds)
    {
        $joinStoreCondition = [
            't_default.entity_id=t_store.entity_id',
            't_default.attribute_id=t_store.attribute_id',
            't_store.store_id=?',
        ];

        $joinCondition = $this->connection->quoteInto(
            implode(' AND ', $joinStoreCondition),
            $storeId
        );

        $select = $this->connection->select()
            ->from(['t_default' => $table],
                [
                    'entity_id',
                    'attribute_id',
                ]
            )
            ->joinLeft(
                ['t_store' => $table],
                $joinCondition,
                ['value' => new Zend_Db_Expr('COALESCE(t_store.value, t_default.value)')]
            )
            ->where('t_default.store_id = ?', 0)
            ->where('t_default.entity_id IN (?)', $entityIds)
            ->where('t_default.attribute_id IN (?)', $attributeIds);

        return $select;
    }

    /**
     * @return array
     */
    public function getAttributesById()
    {
        if (null === $this->attributesById) {
            $this->attributesById = [];
            $entityType = $this->getEntityType();

            $attributes = Mage::getResourceModel($this->attributeCollectionModel)
                ->setEntityTypeFilter($entityType->getEntityTypeId());

            /** @var  $attribute */
            foreach ($attributes as $attribute) {
                $this->attributesById[$attribute->getAttributeId()] = $attribute;
            }
        }

        return $this->attributesById;
    }
}
