<?php

/**
 * Interface Divante_VueStorefrontIndexer_Api_IndexerInterface
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
interface Divante_VueStorefrontIndexer_Api_IndexerInterface
{

    /**
     * @return string
     */
    public function getTypeName();

    /**
     * @param array $ids
     *
     * @return void
     */
    public function updateDocuments(array $ids = []);

    /**
     * @param array $ids
     *
     * @return void
     */
    public function deleteDocuments(array $ids);
}
