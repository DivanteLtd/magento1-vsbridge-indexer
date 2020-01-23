<?php

class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Configurable_Product
{

    /**
     * @param array $productDTO
     *
     * @return array
     */
    public function prepareConfigurableProduct(array $productDTO)
    {
        $configurableChildren = $productDTO['configurable_children'];
        $areChildInStock = 0;
        $childPrice = [];

        foreach ($configurableChildren as $child) {
            $childPrice[] = $child['price'];

            if ($child['stock']['is_in_stock']) {
                $areChildInStock = 1;
            }
        }

        $isInStock = $productDTO['stock']['is_in_stock'];

        if (!$isInStock || !$areChildInStock) {
            $productDTO['stock']['is_in_stock'] = false;
            $productDTO['stock']['stock_status'] = 0;
        }

        if (!empty($childPrice)) {
            $minPrice = min($childPrice);
            $productDTO['price'] = $minPrice;
            $productDTO['final_price'] = $minPrice;
            $productDTO['regular_price'] = $minPrice;
        }

        return $productDTO;
    }
}
