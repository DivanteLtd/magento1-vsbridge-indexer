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
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Customoptions constructor.
     */
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {

        foreach ($indexData as $productId => $productDTO) {

            if ($productDTO['has_options'] == 1) {

                $product = Mage::getModel('catalog/product')->load($productId);

                if (count($product->getOptions()) >= 1) {

                    foreach ($product->getOptions() as $option) {

                        $options = [
                            'option_id' => $option->getOptionId(),
                            'type' => $option->getType(),
                            'title' => $option->getTitle(),
                            'store_title' => $option->getStoreTitle(),
                            'is_require' => $option->getIsRequire(),
                            'sku' => $option->getSku(),
                            'max_characters' => $option->getMaxCharacters(),
                            'default_title' => $option->getDefaultTitle(),
                            'default_price' => $option->getDefaultPrice(),
                            'default_price_type' => $option->getDefaultPriceType(),
                            'store_price' => $option->getStorePrice(),
                            'store_price_type' => $option->getStorePriceType(),
                            'price' => $option->getPrice(),
                            'price_type' => $option->getPriceType(),
                            'values' => []
                        ];
                        if (!empty((array)$option->getValues())) {

                            foreach ($option->getValues() as $value) {
                                #TODO add sorting
                                $options['values'][] = [
                                    'option_type_id' => $value->getOptionTypeId(),
                                    'title' => $value->getTitle(),
                                    'price' => $value->getPrice(),
                                    'price_type' => $value->getPriceType(),
                                    'default_price' => $value->getDefaultPrice(),
                                    'default_price_type' => $value->getDefaultPriceType(),
                                    'store_price' => $value->getStorePrice(),
                                    'store_price_type' => $value->getStorePriceType(),
                                    'sort_order' => $value->getSortOrder(),
                                    'default_title' => $value->getDefaultTitle(),
                                    'store_title' => $value->getStoreTitle()
                                ];
                            }
                        }

                        $indexData[$productId]['custom_options'][] = $options;
                    }
                }

            } else {

                $indexData[$productId]['custom_options'] = [];

            }
        }
        return $indexData;
    }
}
