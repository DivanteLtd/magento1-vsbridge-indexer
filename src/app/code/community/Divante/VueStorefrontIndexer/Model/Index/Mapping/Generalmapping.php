<?php
/**
 * @package  Divante
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping
 */
class Divante_VueStorefrontIndexer_Model_Index_Mapping_Generalmapping
{

    private $commonProperties = [
        'position' => ['type' => 'long'],
        'level' => ['type' => 'long'],
        'created_at' => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
        ],
        'updated_at' => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
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
