<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Cms_Page
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Sven Ehmer <sven.ehmer@gastro-hero.de>
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Cms_Page
{
    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Cms_Page
     */
    protected $resourceModel;

    /**
     * @var Varien_Filter_Template
     */
    protected $pageTemplateProcessor;

    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/cms_page');
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $this->pageTemplateProcessor = $helper->getPageTemplateProcessor();
    }

    /**
     * @param int   $storeId
     * @param array $pageIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $pageIds = [])
    {
        $lastPageId = 0;

        do {
            $cmsPages = $this->resourceModel->loadPages($storeId, $pageIds, $lastPageId);

            foreach ($cmsPages as $pageData) {
                $lastPageId = $pageData['page_id'];
                $pageData['id'] = $pageData['page_id'];
                $pageData['content'] = $this->pageTemplateProcessor->filter($pageData['content']);

                unset($pageData['creation_time'], $pageData['update_time'], $pageData['page_id']);

                yield $lastPageId => $pageData;
            }
        } while (!empty($cmsPages));
    }
}
