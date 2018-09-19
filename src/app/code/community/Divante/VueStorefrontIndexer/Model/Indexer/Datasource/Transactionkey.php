<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Transactionkey implements DataSourceInterface
{

    /**
     * @return int|string
     */
    public function getTransactionKey()
    {
        /** @var Divante_VueStorefrontIndexer_Model_Transactionkey $transactionKeyModel */
        $transactionKeyModel = Mage::getSingleton('vsf_indexer/transactionkey');

        return $transactionKeyModel->load();
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as &$data) {
            $data['tsk'] = $this->getTransactionKey();
        }

        return $indexData;
    }
}
