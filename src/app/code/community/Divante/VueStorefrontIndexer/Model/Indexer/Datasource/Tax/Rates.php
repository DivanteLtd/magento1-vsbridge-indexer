<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Tax_Rates
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Tax_Rates implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Tax_Rates
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Taxrule constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/tax_rates');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $taxRuleIds = array_keys($indexData);
        $taxRates = $this->resourceModel->loadTaxRates($taxRuleIds);

        foreach ($taxRates as $taxRate) {
            $ruleId = $taxRate['tax_calculation_rule_id'];
            $taxRate['id'] = intval($taxRate['tax_calculation_rate_id']);
            unset($taxRate['tax_calculation_rule_id'], $taxRate['tax_calculation_rate_id']);

            $indexData[$ruleId]['rates'][] = $taxRate;
        }

        return $indexData;
    }
}