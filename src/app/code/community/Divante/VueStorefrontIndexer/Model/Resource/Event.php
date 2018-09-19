<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Event
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('vsf_indexer/event', 'event_id');
    }

    /**
     * Check if semilar event exist before start saving data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Index_Model_Resource_Event
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * Check if event already exist and merge previous data
         */
        if (!$object->getId()) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('type=?', $object->getType())
                ->where('entity=?', $object->getEntity());

            if ($object->hasEntityPk()) {
                $select->where('entity_pk=?', $object->getEntityPk());
            }

            $data = $this->_getWriteAdapter()->fetchRow($select);

            if ($data) {
                $object->mergePreviousData($data);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param string $type
     * @param array $ids
     *
     * @return $this
     */
    public function deleteByTypeIds($type, array $ids = null)
    {
        if (empty($ids)) {
            return $this;
        }

        $where = ['entity = ?' => $type];

        if ($ids) {
            $where[] = $this->_getWriteAdapter()->quoteInto('entity_pk IN (?)', $ids);
        }

        try {
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                $where
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }
}
