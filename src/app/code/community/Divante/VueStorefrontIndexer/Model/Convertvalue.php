<?php

use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;
use Divante_VueStorefrontIndexer_Api_MappingInterface as MappingInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Convertvalue
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 */
class Divante_VueStorefrontIndexer_Model_Convertvalue
{
    /**
     * @var array
     */
    private $castMapping = [
        FieldInterface::TYPE_LONG => 'int',
        FieldInterface::TYPE_INTEGER => 'int',
        FieldInterface::TYPE_BOOLEAN => 'bool',
        FieldInterface::TYPE_DOUBLE => 'float',
    ];

    /**
     * @param Divante_VueStorefrontIndexer_Api_MappingInterface $mapping
     * @param string $field
     * @param string|float|int|null $value
     *
     * @return int|float|bool|null
     */
    public function execute(MappingInterface $mapping, $field, $value)
    {
        $properties = $mapping->getMappingProperties()['properties'];
        $type = $this->getFieldTypeByCode($properties, $field);

        if (null === $type) {
            return $value;
        }

        if (null === $value) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                settype($v, $type);
            }
        } else {
            settype($value, $type);
        }

        return $value;
    }

    /**
     * @param array $mapping
     * @param string $field
     *
     * @return string|null
     */
    private function getFieldTypeByCode(array $mapping, $field)
    {
        if (isset($mapping[$field]['type'])) {
            $type = $mapping[$field]['type'];

            if (isset($this->castMapping[$type])) {
                return $this->castMapping[$type];
            }
        }

        return null;
    }
}
