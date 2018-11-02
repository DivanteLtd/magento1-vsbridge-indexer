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

        /**
         * TODO add option to have full reindex per store
         */
        switch ($action) {
            case 'full_reindex':
                $type = $this->getArg('type');
                $storeId = (int)$this->getArg('store');

                if (0 === $storeId) {
                    echo "Please provide store to reindex \n\n";
                    echo $this->usageHelp();

                    return;
                }

                if ($type) {
                    $tools->reindexByType($type, $storeId);
                } else {
                    $tools->fullReindex($storeId);
                    echo "Full reindex - done \n";
                }
                break;
            case 'reindex':
                /*TODO reindex per store*/
                $tools->reindex();
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
                full_reindex --store STORE_ID [--type categories|products|taxrules|attributes|cms_blocks]
                reindex
                delete_indices

USAGE;
    }
}

$shell = new Divante_VueStorefrontIndexer_Tools();
$shell->run();