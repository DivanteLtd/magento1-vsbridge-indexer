<?php

use Divante_VueStorefrontIndexer_Api_BulkResponseInterface as BulkResponseInterface;

/**
 * Class Divante_VueStoreFrontIndexer_Model_Event_Delete
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStoreFrontIndexer_Model_Event_Delete
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Event
     */
    private $resource;

    /**
     * Divante_VueStoreFrontIndexer_Model_Event_Delete constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getResourceModel('vsf_indexer/event');
    }

    /**
     * @param BulkResponseInterface $bulkResponse
     */
    public function execute($type, array $ids = null)
    {
        $this->resource->deleteByTypeIds($type, $ids);
    }
}
