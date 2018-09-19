<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Taxrule
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Taxrule
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Tax_Rules
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Taxrule constructor.
     */
    public function __construct()
    {

        $this->resourceModel = Mage::getResourceModel('vsf_indexer/tax_rules');
    }

    /**
     * @param array $taxRuleIds
     *
     * @return \Traversable
     */
    public function rebuild(array $taxRuleIds = [])
    {
        $lastTaxRuleId = 0;

        do {
            $taxRules = $this->resourceModel->getTaxRules($taxRuleIds, $lastTaxRuleId);

            foreach ($taxRules as $taxRule) {
                $taxRule['id'] = intval($taxRule['tax_calculation_rule_id']);
                unset($taxRule['tax_calculation_rule_id']);
                $lastTaxRuleId = $taxRule['id'];

                yield $lastTaxRuleId => $taxRule;
            }
        } while (!empty($taxRules));
    }
}
