<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Event
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Event extends Mage_Core_Model_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('vsf_indexer/event');
    }

    /**
     * Serelaize old and new data arrays before saving
     *
     * @return Divante_VueStorefrontIndexer_Model_Event
     */
    protected function _beforeSave()
    {
        if (!$this->hasCreatedAt()) {
            $this->setCreatedAt($this->_getResource()->formatDate(time(), true));
        }

        return parent::_beforeSave();
    }

    /**
     * Merge previous event data to object.
     * Used for events duplicated protection
     *
     * @param array $data
     * @return Divante_VueStorefrontIndexer_Model_Event
     */
    public function mergePreviousData(array $data)
    {
        if (!empty($data['event_id'])) {
            $this->setId($data['event_id']);
            $this->setCreatedAt($data['created_at']);
        }

        return $this;
    }
}
