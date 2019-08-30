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

                if ($type && !$tools->checkIfTypeAvailable($type)) {
                    echo "Indexer type #$type is not available \n";
                    echo $this->usageHelp();
                    return;
                }

                /** @var Divante_VueStorefrontIndexer_Model_Config_Generalsettings $settings */
                $settings = Mage::getSingleton('vsf_indexer/config_generalsettings');

                if ($storeId === null) {
                    $storeIds = $settings->getStoresToIndex();
                } else {
                    $storeIds = ($settings->canReindexStore($storeId)) ? array($storeId) : array();
                }

                foreach ($storeIds as $storeId) {
                    if ($type) {
                        echo "Full reindexing: store #$storeId, type #$type ... \n";
                        $tools->runFullReindexByType($type, $storeId);
                        echo "Full reindexing: store #$storeId, type #$type has completed! \n";
                    } else {
                        echo "Full reindexing: store #$storeId ... \n";
                        $tools->fullReindex($storeId);
                        echo "Full reindexing: store #$storeId has completed! \n";
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
                echo "Indices has been deleted from ES. \n";
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
                full_reindex --store STORE_ID|OPTIONAL [--type categories|products|taxrules|attributes|cms_blocks|cms_pages|reviews]
                reindex --store STORE_ID|OPTIONAL
                delete_indices

USAGE;
    }
}

$shell = new Divante_VueStorefrontIndexer_Tools();
$shell->run();
