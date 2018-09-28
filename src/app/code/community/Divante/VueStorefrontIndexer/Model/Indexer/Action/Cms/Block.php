<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Cms_Block
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Cms_Block
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Cms_Block
     */
    private $resourceModel;

    /**
     * @var Varien_Filter_Template
     */
    private $blockTemplateProcessor;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Cms_Block constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/cms_block');
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $this->blockTemplateProcessor = $helper->getBlockTemplateProcessor();
    }

    /**
     * @param int   $storeId
     * @param array $blockIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $blockIds = [])
    {
        $lastBlockId = 0;

        do {
            $cmsBlocks = $this->resourceModel->loadBlocks($storeId, $blockIds, $lastBlockId);

            foreach ($cmsBlocks as $blockData) {
                $lastBlockId = $blockData['block_id'];
                $blockData['id'] = $blockData['block_id'];
                $blockData['content'] = $this->blockTemplateProcessor->filter($blockData['content']);

                unset($blockData['creation_time'], $blockData['update_time'], $blockData['block_id']);

                yield $lastBlockId => $blockData;
            }
        } while (!empty($cmsBlocks));
    }
}
