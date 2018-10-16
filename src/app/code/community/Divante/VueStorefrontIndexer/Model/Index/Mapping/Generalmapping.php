<?php
/**
 * @package  Divante
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

use Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface as FieldInterface;

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping
{

    /**
     * @var array
     */
    private $commonProperties = [
        'position' => ['type' => FieldInterface::TYPE_LONG],
        'level' => ['type' => FieldInterface::TYPE_LONG],
        'created_at' => [
            "type" => FieldInterface::TYPE_DATE,
            "format" => FieldInterface::DATE_FORMAT,
        ],
        'updated_at' => [
            "type" => FieldInterface::TYPE_DATE,
            "format" => FieldInterface::DATE_FORMAT,
        ]
    ];

    /**
     * @return array
     */
    public function getCommonProperties()
    {
        return $this->commonProperties;
    }
}
