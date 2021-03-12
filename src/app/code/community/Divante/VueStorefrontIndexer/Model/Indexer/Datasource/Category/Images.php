<?php declare(strict_types=1);

use Divante_VueStorefrontIndexer_Api_DatasourceInterface as DataSourceInterface;

class Divante_VueStorefrontIndexer_Model_Indexer_Datasource_Category_Images implements DataSourceInterface
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
        $this->_lazyCatalog
            ->setHeight(180)
            ->setWidth(180)
            ->setTransparency();
    }

    /**
     * @param array $indexData
     * @param int $storeId
     * @return array
     */
    public function addData(array $indexData, $storeId): array // @codingStandardsIgnoreLine
    {
        foreach ($indexData as $categoryId => $category) {
            $indexData[$categoryId]['thumbnail'] = $this->_lazyCatalog
                ->setImagePath($category['thumbnail'])
                ->setImageName($category['name'])
                ->getImageUrl();
        }
        return $indexData;
    }
}
