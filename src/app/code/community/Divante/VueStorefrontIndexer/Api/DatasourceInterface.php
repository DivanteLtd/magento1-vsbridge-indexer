<?php

/**
 * Interface Divante_VueStorefrontIndexer_Api_DatasourceInterface
 */
interface Divante_VueStorefrontIndexer_Api_DatasourceInterface
{
    /**
     * Append data to a list of documents.
     *
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId);
}
