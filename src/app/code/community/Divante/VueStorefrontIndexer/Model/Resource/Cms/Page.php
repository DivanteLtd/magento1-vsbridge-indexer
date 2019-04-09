<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Cms_Page
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Sven Ehmer <sven.ehmer@gastro-hero.de>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Cms_Page
{
    /**
     * @var Mage_Core_Model_Resource
     */
    protected $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;
    
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');
    }

    /**
     * @param int $storeId
     * @param array $pageIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function loadPages($storeId = 1, array $pageIds = [], $fromId = 0, $limit = 100)
    {
        $select = $this->connection->select()->from(['main_table' => $this->coreResource->getTableName('cms/page')]);
        $select->join(
            ['store_table' => $this->coreResource->getTableName('cms/page_store')],
            'main_table.page_id = store_table.page_id',
            []
        )->group('main_table.page_id');

        $select->where('store_table.store_id IN (?)', [0, $storeId]);

        if (!empty($pageIds)) {
            $select->where('main_table.page_id IN (?)', $pageIds);
        }

        $select->where('is_active = ?', 1);
        $select->where('main_table.page_id > ?', $fromId)
            ->limit($limit)
            ->order('main_table.page_id');

        return $this->connection->fetchAll($select);
    }
}
