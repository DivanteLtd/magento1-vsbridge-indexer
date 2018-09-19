<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Cms_Block
{

    /**
     * @var Mage_Core_Model_Resource
     */
    private $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('read');
    }

    /**
     * @param int $storeId
     * @param array $blockIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function loadBlocks($storeId = 1, array $blockIds = [], $fromId = 0, $limit = 100)
    {
        $select = $this->connection->select()->from(['main_table' => $this->coreResource->getTableName('cms/block')]);
        $select->join(
            ['store_table' => $this->coreResource->getTableName('cms/block_store')],
            'main_table.block_id = store_table.block_id',
            []
        )->group('main_table.block_id');

        $select->where('store_table.store_id IN (?)', [0, $storeId]);

        if (!empty($blockIds)) {
            $select->where('main_table.block_id IN (?)', $blockIds);
        }

        $select->where('is_active = ?', 1);
        $select->where('main_table.block_id > ?', $fromId)
            ->limit($limit)
            ->order('main_table.block_id');

        return $this->connection->fetchAll($select);
    }
}
