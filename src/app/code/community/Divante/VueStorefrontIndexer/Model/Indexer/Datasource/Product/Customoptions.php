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
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links
     */

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
//        $this->linkedProductResource = Mage::getResourceModel('vsf_indexer/catalog_product_links');
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId) {


        $productInstance = Mage::getModel('catalog/product');
        foreach ($indexData as $productId => $productDTO) {


            if ($productDTO['has_options'] == 1) {

                mage::log($productId, null, 'hd-co.log', true);
                $product = $productInstance->load($productId);
//                mage::log('options', null, 'hd-co.log', true);
//                mage::log($indexData[$productId], null, 'hd-co.log', true);
                foreach ($product->getOptions() as $option) {
//                    mage::log('custom_options - '.$productId, null, 'hd-co.log', true);
//                    mage::log($option->getTitle(), null, 'hd-co.log', true);
                    #TODO add sorting
                    $indexData[$productId]['custom_options'][$option->getOptionId()] = [
                        'option_id' => $option->getOptionId(),
                        'type' => $option->getType(),
                        'title' => $option->getTitle()

                    ];
                    if (empty((array)$option->getValues())) {
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
//                mage::log($indexData[$productId]['custom_options'], null, 'hd-co.log', true);

            } else {

                $indexData[$productId]['custom_options'] = [];

            }
            $productInstance->clearInstance();
        }
        return $indexData;
    }
}
