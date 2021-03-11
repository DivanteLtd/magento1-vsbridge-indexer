<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Action_Salespromotion
 *
 * @package     Ambimax
 * @category    VueStoreFrontIndexer
 * @author      Tobias Faust <tf@ambimax.de>
 * @copyright   Copyright (C) 2021 ambimax GmbH
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Action_Salespromotion
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Salespromotion
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Action_Salespromotion constructor.
     */
    public function __construct()
    {
        $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_salespromotion');
        $this->dataFilter = Mage::getSingleton('vsf_indexer/data_filter');
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $promotionIds = [])
    {
        $lastPromotionId = 0;

        do {
            $promotions = $this->resourceModel->getPromotions($storeId, $promotionIds, $lastPromotionId);

            /** @var array $product */
            foreach ($promotions as $promotion) {
                $lastPromotionId = $promotion['id'];
                $promotion['id'] = intval($promotion['id']);

                yield $lastPromotionId => $promotion;
            }
        } while (!empty($promotions));
    }
}
