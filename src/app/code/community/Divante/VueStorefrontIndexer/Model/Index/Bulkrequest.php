<?php

use Divante_VueStorefrontIndexer_Api_BulkRequestInterface as BulkRequestInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Bulkrequest
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Bulkrequest implements BulkRequestInterface
{
    /**
     * Bulk operation stack.
     *
     * @var array
     */
    private $bulkData = [];

    /**
     * @inheritdoc
     */
    public function deleteDocuments($index, $type, array $docIds)
    {
        foreach ($docIds as $docId) {
            $this->deleteDocument($index, $type, $docId);
        }

        return $this;
    }

    /**
     * @param string $index
     * @param string $type
     * @param string $docId
     *
     * @return $this
     */
    private function deleteDocument($index, $type, $docId)
    {
        $this->bulkData[] = [
            'delete' => [
                '_index' => $index,
                '_type' => $type,
                '_id' => $docId,
            ]
        ];

        return $this;
    }

    /**
     * @param string $index
     * @param string $type
     * @param string $docId
     * @param array $data
     *
     * @return $this
     */
    private function addDocument($index, $type, $docId, array $data)
    {
        $this->bulkData[] = [
            'index' => [
                '_index' => $index,
                '_type' => $type,
                '_id' => $docId,
            ]
        ];

        $this->bulkData[] = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addDocuments($index, $type, array $data)
    {
        foreach ($data as $docId => $documentData) {
            $this->addDocument($index, $type, $docId, $documentData);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return count($this->bulkData) == 0;
    }

    /**
     * @inheritdoc
     */
    public function getOperations()
    {
        return $this->bulkData;
    }
}
