<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Gridperpage
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Gridperpage implements DataSourceInterface
{

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as $categoryId => $categoryData) {
            $indexData[$categoryId]['grid_per_page'] = $this->getDefaultGridPerPageValue($storeId);
        }

        return $indexData;
    }

    /**
     * @param int $storeId
     *
     * @return int
     */
    private function getDefaultGridPerPageValue($storeId)
    {
        return (int)Mage::getStoreConfig('catalog/frontend/grid_per_page', $storeId);
    }
}
