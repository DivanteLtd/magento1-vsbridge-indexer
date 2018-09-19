<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Tax_Rules
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Tax_Taxclasses
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
     * @param array $ruleIds
     *
     * @return array
     */
    public function loadTaxClasses(array $ruleIds)
    {
        $select = $this->connection->select();
        $select->from(
            $this->coreResource->getTableName('tax/tax_calculation'),
            [
                'tax_calculation_rule_id',
                'customer_tax_class_id',
                'product_tax_class_id',
            ]
        )->where('tax_calculation_rule_id IN (?)', $ruleIds);

        $select->distinct(true);

        return $this->connection->fetchAssoc($select);
    }
}
