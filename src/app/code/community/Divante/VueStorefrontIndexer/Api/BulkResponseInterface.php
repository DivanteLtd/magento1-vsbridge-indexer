<?php

/**
 * Interface Divante_VueStorefrontIndexer_Model_Index_BulkRequestInterface
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
interface Divante_VueStorefrontIndexer_Api_BulkResponseInterface
{

    /**
     * @return boolean
     */
    public function hasErrors();

    /**
     * @return array
     */
    public function getErrorItems();

    /**
     * @return array
     */
    public function aggregateErrorsByReason();
}
