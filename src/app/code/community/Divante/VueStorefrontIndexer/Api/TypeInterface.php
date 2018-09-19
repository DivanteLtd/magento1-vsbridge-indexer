<?php

/**
 * Interface Divante_VueStorefrontIndexer_Api_TypeInterface
 */
interface Divante_VueStorefrontIndexer_Api_TypeInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return Divante_VueStorefrontIndexer_Api_MappingInterface|null
     */
    public function getMapping();

    /**
     * @return array
     */
    public function getDataSources();

    /**
     * @param string $name
     *
     * @return Divante_VueStorefrontIndexer_Api_DatasourceInterface
     * @throws Mage_Core_Exception
     */
    public function getDataSource($name);
}
