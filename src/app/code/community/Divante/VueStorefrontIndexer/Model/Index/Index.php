<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Index
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Index_Index
{

    /**
     * Name of the index.
     *
     * @var string
     */
    private $name;

    /**
     * Index types.
     *
     * @var
     */
    private $types;

    /**
     * Divante_VueStorefrontIndexer_Model_Index_Index constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }

        if (isset($params['types'])) {
            $this->types = $this->prepareTypes($params['types']);
        }
    }

    /**
     * @param Divante_VueStorefrontIndexer_Model_Index_Type[] $types
     *
     * @return array
     */
    private function prepareTypes(array $types)
    {
        $preparedTypes = [];

        foreach ($types as $type) {
            $preparedTypes[$type->getName()] = $type;
        }

        return $preparedTypes;
    }

    /**
     * string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $typeName
     *
     * @return Divante_VueStorefrontIndexer_Model_Index_Type
     * @throws Mage_Core_Exception
     */
    public function getType($typeName)
    {
        if (!isset($this->types[$typeName])) {
            Mage::throwException("Type $typeName does not exists in the index.");
        }

        return $this->types[$typeName];
    }
}