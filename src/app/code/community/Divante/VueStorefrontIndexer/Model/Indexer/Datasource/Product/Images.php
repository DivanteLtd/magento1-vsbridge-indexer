<?php declare(strict_types=1);

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Images implements DataSourceInterface
{

    /**
     * @var Ambimax_LazyCatalogImages_Model_Catalog_Image
     */
    protected $_lazyCatalog;

    /**
     * Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Product_Links constructor.
     */
    public function __construct()
    {
        $this->_lazyCatalog = Mage::getModel('ambimax_lazycatalogimages/catalog_image');
        $this->_lazyCatalog;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     * @return array
     */
    public function addData(array $indexData, $storeId): array // @codingStandardsIgnoreLine
    {
        foreach ($indexData as $productId => $product) {
            $indexData[$productId]['image'] = $this->_lazyCatalog
                ->setImagePath($product['image'])
                ->setImageName($product['name'])
                ->setHeight(0)
                ->setWidth(0)
                ->getImageUrl();
            $indexData[$productId]['small_image'] = $this->_lazyCatalog
                ->setImagePath($product['small_image'])
                ->setImageName($product['name'])
                ->setHeight(0)
                ->setWidth(0)
                ->getImageUrl();
            $indexData[$productId]['thumbnail'] = $this->_lazyCatalog
                ->setImagePath($product['thumbnail'])
                ->setImageName($product['name'])
                ->setHeight(180)
                ->setWidth(180)
                ->getImageUrl();
        }
        return $indexData;
    }
}
