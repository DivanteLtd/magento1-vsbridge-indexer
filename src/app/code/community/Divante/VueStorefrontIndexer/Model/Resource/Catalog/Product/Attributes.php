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
     * @var Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Loadattributes
     */
    private $loadAttributes;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Attributes constructor.
     */
    public function __construct()
    {
        $this->loadAttributes = Mage::getResourceSingleton('vsf_indexer/catalog_product_loadattributes');
        parent::__construct();
    }

    /**
     * @return array
     */
    public function initAttributes()
    {
        return $this->loadAttributes->execute();
    }
}
