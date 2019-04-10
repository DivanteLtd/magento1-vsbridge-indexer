<?php

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Customoptions
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Niels Gongoll <angongoll@highdigital.de
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Customoptions implements DataSourceInterface
{

    /**
     * @var Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Customoptions
     */
    private $resourceModel;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Customoptions constructor.
     */
    public function __construct()
    {
//        $this->linkedProductResource = Mage::getResourceModel('vsf_indexer/catalog_product_customoptions');
//        {
            $this->resourceModel = Mage::getResourceModel('vsf_indexer/catalog_product_customoptions');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->resourceModel->clear();
        $this->resourceModel->setProducts($indexData);
        foreach ($indexData as $productId => $indexData){



//        $productId = $indexData['id'];

//        $productCustomOptions = $this->resourceModel->loadCustomOptions($storeId);
//print_r($indexData);die();

//
            $product = Mage::getModel('catalog/product')->load($productId);
        if($product->getData('has_options')) {

            foreach ($product->getOptions() as $option) {
                #TODO add sorting
                $indexData[$productId][$option->getOptionId()] = [
                    'option_id' => $option->getOptionId(),
                    'type' => $option->getType(),
                    'title' => $option->getTitle()

                ];
                if(empty((array) $option->getValues())){
                    $indexData[$productId]['custom_options'][$option->getOptionId()]['values'] = [];
                } else {


                    foreach ($option->getValues() as $value) {
                        #TODO add sorting
                        $indexData[$productId]['custom_options'][$option->getOptionId()]['values'][$value->getOptionTypeId()] = [
                            'title' => $value->getTitle(),
                            'price' => $value->getPrice(),
                            'option_type_id' => $value->getOptionTypeId(),
                            'price_type' => $value->getPriceType()

                        ];
                    }

                }
            }
    }
        }



//        $productBundleOptions = $this->resourceModel->loadBundleOptions($storeId);
//
//        foreach ($productBundleOptions as $productId => $bundleOptions) {
//            $indexData[$productId]['bundle_options'] = [];
//
//            foreach ($bundleOptions as $option) {
//                $indexData[$productId]['bundle_options'][] = $option;
//            }
//        }

        $this->resourceModel->clear();
//        $productBundleOptions = null;


      //  return $indexData;
    }
}
