<?php

use Divante_VueStorefrontIndexer_Model_Resource_Catalog_Eav as Eav;

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes extends Eav
{

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $params = [
            'entity_type' => 'catalog_product',
            'collection_model' => 'catalog/product_attribute_collection',
        ];

        parent::__construct($params);
    }
}
