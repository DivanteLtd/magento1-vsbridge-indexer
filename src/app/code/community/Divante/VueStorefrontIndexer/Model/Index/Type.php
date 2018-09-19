<?php

use Divante_VueStorefrontIndexer_Api_TypeInterface as TypeInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Type
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Type implements TypeInterface
{
    /**
     * Type name.
     *
     * @var string
     */
    private $name;

    /**
     * Type mapping.
     *
     * @var \Divante_VueStorefrontIndexer_Api_MappingInterface|null
     */
    private $mapping;

    /**
     * Type datasources.
     *
     * @var \Divante_VueStorefrontIndexer_Api_DatasourceInterface[]
     */
    private $dataSources;

    /**
     * Divante_VueStorefrontIndexer_Model_Index_Type constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }

        if (isset($params['mapping'])) {
            $this->mapping = $params['mapping'];
        }

        if (isset($params['data_sources'])) {
            $this->dataSources = $params['data_sources'];
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @inheritdoc
     */
    public function getDataSources()
    {
        return $this->dataSources;
    }

    /**
     * @inheritdoc
     */
    public function getDataSource($name)
    {
        if (!isset($this->dataSources[$name])) {
            Mage::throwException("Datasource $name does not exists.");
        }

        return $this->dataSources[$name];
    }
}
