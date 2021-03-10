<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Salespromotion
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Salespromotion
{

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    /**
     * @var Divante_VueStorefrontIndexer_Model_Config_Catalogsettings
     */
    protected $catalogSettings;

    /**
     * @var int
     */
    private $isActiveAttributeId;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Attribute_Full constructor.
     */
    public function __construct()
    {
        $this->coreResource = Mage::getSingleton('core/resource');
        $this->connection = $this->coreResource->getConnection('core_write');
        $this->catalogSettings = Mage::getSingleton('vsf_indexer/config_catalogsettings');
    }

    /**
     * Get promotions for the given store.
     *
     * @param int   $storeId
     * @param array $productIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getPromotions($storeId = 1, array $promotionIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->connection->select()->from(['e' => $this->coreResource->getTableName('delphin_salespromotion/weekly_product')]);

        if (!empty($promotionIds)) {
            $select->where('e.id IN (?)', $promotionIds);
        }

        $select->limit($limit);
        $select->where('e.id > ?', $fromId);
        $select->order('e.id ASC');

        return $this->connection->fetchAll($select);
    }
}
