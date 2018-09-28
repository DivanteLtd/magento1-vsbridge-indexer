<?php
/**
 * @package   magento
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/* @var $installer Mage_Index_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'vsf_indexer/event'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('vsf_indexer/event'))
    ->addColumn(
        'event_id',
        Varien_Db_Ddl_Table::TYPE_BIGINT,
        null,
        [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ],
        'Event Id'
    )
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 64, ['nullable' => false], 'Type')
    ->addColumn('entity', Varien_Db_Ddl_Table::TYPE_TEXT, 64, ['nullable' => false], 'Entity')
    ->addColumn('entity_pk', Varien_Db_Ddl_Table::TYPE_BIGINT, null, [], 'Entity Primary Key')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Creation Time')
    ->addIndex(
        $installer->getIdxName(
            'index/event',
            [
                'type',
                'entity',
                'entity_pk',
            ],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        [
            'type',
            'entity',
            'entity_pk',
        ],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->setComment('VSF ElasticSearch Index Event');

$installer->getConnection()->createTable($table);

$installer->endSetup();