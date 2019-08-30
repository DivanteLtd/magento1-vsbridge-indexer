<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links
{

    /**
     * Product link type mapping, used for references and validation
     *
     * @var array
     */
    private $typeMap = [
        Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED => 'related',
        Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL => 'upsell',
        Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL => 'crosssell',
        Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED => 'associated',
    ];

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $links;

    /**
     * @var Mage_Core_Model_Resource
     */
    private $resource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    private $connection;

    /**
     * @var array
     */
    private $positionAttribute;

    /**
     * Divante_VueStorefrontIndexer_Model_Resource_Catalog_Product_Links constructor.
     */
    public function __construct()
    {
        $this->resource = Mage::getSingleton('core/resource');
        $this->connection = $this->resource->getConnection('catalog_read');
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->links = null;
        $this->products = null;
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    /**
     * @param array $product
     *
     * @return array
     */
    public function getLinkedProduct(array $product)
    {
        $links = $this->getAllLinkedProducts();
        $productId = $product['id'];

        if (isset($links[$productId])) {
            $linkProductList = [];

            foreach ($links[$productId] as $linkData) {
                $typeId = $linkData['link_type_id'];

                $linkProductList[] = [
                    'sku' => $product['sku'],
                    'link_type' => $this->getLinkType($typeId),
                    'linked_product_sku' => $linkData['sku'],
                    'linked_product_type' => $linkData['type_id'],
                    'position' => (int)$linkData['position'],
                ];
            }

            return $linkProductList;
        }

        return [];
    }

    /**
     * @param int $typeId
     *
     * @return string|null
     */
    private function getLinkType($typeId)
    {
        if (isset($this->typeMap[$typeId])) {
            return $this->typeMap[$typeId];
        }

        return null;
    }

    /**
     * @return array
     */
    private function getAllLinkedProducts()
    {
        if (null === $this->links) {
            $select = $this->prepareLinksSelect();
            $links = $this->connection->fetchAll($select);
            $groupByProduct = [];

            foreach ($links as $link) {
                $productId = $link['product_id'];
                unset($link['product_id']);
                $groupByProduct[$productId][] = $link;
            }

            $this->links = $groupByProduct;
        }

        return $this->links;
    }

    /**
     * @return Varien_Db_Select
     */
    private function prepareLinksSelect()
    {
        $productIds = $this->getProductsIds();

        $select = $this->connection->select()
            ->from(
                ['links' => $this->resource->getTableName('catalog/product_link')],
                [
                    'product_id',
                    'linked_product_id',
                    'link_type_id',
                ]
            )
            ->where('product_id in (?)', $productIds);

        $select->joinLeft(
            ['entity' => $this->resource->getTableName('catalog/product')],
            'links.linked_product_id = entity.entity_id',
            [
                'sku',
                'type_id',
            ]
        );

        return $this->joinPositionAttribute($select);
    }

    /**
     * @param Varien_Db_Select $select
     *
     * @return Varien_Db_Select
     */
    private function joinPositionAttribute(Varien_Db_Select $select)
    {
        $alias = 'link_position';
        $attributePosition = $this->fetchPositionAttributeData();
        
        if (empty($attributePosition)) {
            return $select;
        }
        
        $table = $this->resource->getTableName($this->getAttributeTypeTable($attributePosition['type']));

        $joinCondition = [
            "{$alias}.link_id = links.link_id",
            $this->connection->quoteInto(
                "{$alias}.product_link_attribute_id = ?",
                $attributePosition['id']
            ),
        ];

        $select->joinLeft(
            array($alias => $table),
            implode(' AND ', $joinCondition),
            array($attributePosition['code'] => 'value')
        );

        return $select;
    }

    /**
     * @return array
     */
    private function fetchPositionAttributeData()
    {
        if (null === $this->positionAttribute) {
            $select = $this->connection->select()
                ->from(
                    $this->resource->getTableName('catalog/product_link_attribute'),
                    [
                        'id' => 'product_link_attribute_id',
                        'code' => 'product_link_attribute_code',
                        'type' => 'data_type',
                    ]
                )
                ->where('product_link_attribute_code = ?', 'position');

            $this->positionAttribute = $this->connection->fetchRow($select);
        }

        return $this->positionAttribute;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getAttributeTypeTable($type)
    {
        return 'catalog/product_link_attribute_' . $type;
    }

    /**
     * Add product filter to collection
     *
     * @return int[]
     */
    private function getProductsIds()
    {
        $products = $this->getProducts();

        return array_keys($products);
    }

    /**
     * @return array
     */
    private function getProducts()
    {
        return $this->products;
    }
}
