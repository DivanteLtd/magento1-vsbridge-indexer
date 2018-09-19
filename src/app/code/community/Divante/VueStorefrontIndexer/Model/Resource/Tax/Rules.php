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
class Divante_VueStorefrontIndexer_Model_Resource_Tax_Rules
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
     * @param array $taxRuleIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getTaxRules(array $taxRuleIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->connection->select()
            ->from(
                $this->coreResource->getTableName('tax/tax_calculation_rule'),
                [
                    'tax_calculation_rule_id',
                    'code',
                    'priority',
                    'position',
                    'calculate_subtotal',
                ]
            );

        if (!empty($taxRuleIds)) {
            $select->where('tax_calculation_rule_id in (?)', $taxRuleIds);
        }

        $select->where('tax_calculation_rule_id > ?', $fromId);
        $select->order('tax_calculation_rule_id');
        $select->limit($limit);

        return $this->connection->fetchAssoc($select);
    }
}
