<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Tax_Taxclasses
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Tax_Taxclasses implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Tax_Taxclasses
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Taxrule constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/tax_taxclasses');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $taxRuleIds = array_keys($indexData);
        $taxClasses = $this->resourceModel->loadTaxClasses($taxRuleIds);

        foreach ($taxClasses as $data) {
            $ruleId = $data['tax_calculation_rule_id'];

            $indexData[$ruleId]['customer_tax_class_ids'][] = $data['customer_tax_class_id'];
            $indexData[$ruleId]['product_tax_class_ids'][] = $data['product_tax_class_id'];
        }

        return $indexData;
    }
}
