<?php

/*available in php 5.6.6*/
if (!defined('JSON_PRESERVE_ZERO_FRACTION')) {
    define('JSON_PRESERVE_ZERO_FRACTION', 1024);
}

require_once 'abstract.php';

use Divante_VueStorefrontIndexer_Model_Tools as Tools;

/**
 * Class Divante_VueStorefrontIndexer_Tools
 */
class Divante_VueStorefrontIndexer_Tools extends Mage_Shell_Abstract
{

    /**
     * Run Magento -> Elastic data synchronization
     * @return void
     */
    public function run()
    {
        $action = $this->getArg('action');
        /** @var Tools $tools */
        $tools = Mage::getSingleton('vsf_indexer/tools');

        if ($action === true) {
            $action = '';
        }

        $storeId = empty($this->getArg('store')) ? null : (int) $this->getArg('store');

        switch ($action) {
            case 'full_reindex':
                $type = $this->getArg('type');

                if ($storeId === null) {
                    /** @var Divante_VueStorefrontIndexer_Model_Config_Generalsettings $settings */
                    $settings = Mage::getSingleton('vsf_indexer/config_generalsettings');
                    $storeIds = $settings->getStoresToIndex();
                } else {
                    $storeIds = array($storeId);
                }

                foreach ($storeIds as $storeId) {
                    echo "Full reindex for store #$storeId - start \n";

                    if ($type) {
                        $tools->runFullReindexByType($type, $storeId);
                    } else {
                        $tools->fullReindex($storeId);
                        echo "Full reindex for store #$storeId - done \n";
                    }
                }

                break;
            case 'reindex':
                $tools->reindex($storeId);
                break;
            case 'delete_indices':
                /** @var Divante_VueStorefrontIndexer_Model_Tools_Index $indexTools */
                $indexTools = Mage::getSingleton('vsf_indexer/tools_index');
                $indexTools->deleteIndices();
                break;
            default:
                echo $this->usageHelp();
                break;
        }
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f vsf_tools.php -- [options]

        --action <action_name>
                full_reindex --store STORE_ID|OPTIONAL [--type categories|products|taxrules|attributes|cms_blocks]
                reindex --store STORE_ID|OPTIONAL
                delete_indices

USAGE;
    }
}

$shell = new Divante_VueStorefrontIndexer_Tools();
$shell->run();
