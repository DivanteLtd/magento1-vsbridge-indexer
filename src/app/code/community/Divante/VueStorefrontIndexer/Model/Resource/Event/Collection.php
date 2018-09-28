<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Event_Collection
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Event_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('vsf_indexer/event');
    }
}
