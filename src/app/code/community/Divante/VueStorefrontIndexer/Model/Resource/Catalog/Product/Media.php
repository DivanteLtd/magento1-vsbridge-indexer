<?php

use Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media as MediaBackend;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Media
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Media
{

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        $this->connection = $this->resource->getConnection('catalog_read');
    }

    /**
     * @param array $productIds
     * @param int   $storeId
     *
     * @return array
     */
    public function loadGallerySet(array $productIds, $storeId)
    {
        $select = $this->getLoadGallerySelect($productIds, $storeId);

        return $this->connection->fetchAll($select);
    }

    /**
     * @return int
     */
    private function getMediaGalleryAttributeId()
    {
        $attribute = Mage::getModel('eav/entity_attribute')
            ->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');

        return $attribute->getId();
    }

    /**
     * @param array $productIds
     * @param int   $storeId
     *
     * @return Varien_Db_Select
     */
    private function getLoadGallerySelect(array $productIds, $storeId)
    {
        $attributeId = $this->getMediaGalleryAttributeId();
        $adapter = $this->connection;

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
                ['main' => $this->resource->getTableName(MediaBackend::GALLERY_TABLE)],
                [
                    'value_id',
                    'value AS file',
                    'product_id' => 'entity_id',
                ]
            )
            ->joinLeft(
                ['value' => $this->resource->getTableName(MediaBackend::GALLERY_VALUE_TABLE)],
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
                [
                    'label',
                    'position',
                ]
            )
            ->joinLeft( // Joining default values
                ['default_value' => $this->resource->getTableName(MediaBackend::GALLERY_VALUE_TABLE)],
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                [
                    'label_default' => 'label',
                    'position_default' => 'position',
                ]
            )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id in (?)', $productIds)
            ->where('default_value.disabled is NULL or default_value.disabled != 1')
            ->where('value.disabled is NULL or value.disabled != 1')
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }
}
