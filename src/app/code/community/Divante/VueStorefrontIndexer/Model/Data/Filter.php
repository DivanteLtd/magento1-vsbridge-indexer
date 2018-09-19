<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Data_Filter
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Data_Filter
{

    /**
     * @var array
     */
    private $longProperties = [];

    /**
     * Divante_VueStorefrontIndexer_Model_Data_Filter constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        if (isset($params['long_properties'])) {
            $this->longProperties = $params['long_properties'];
        }
    }

    /**
     * @param array      $dtoToFilter
     * @param array|null $blackList
     *
     * @return array
     */
    public function execute(array $dtoToFilter, array $blackList = null)
    {
        foreach ($dtoToFilter as $key => $val) {
            if ($blackList && in_array($key, $blackList)) {
                unset($dtoToFilter[$key]);
            } else {
                if (strstr($key, 'is_') || strstr($key, 'has_')) {
                    $dtoToFilter[$key] = boolval($val);
                } else {
                    if (in_array($key, $this->longProperties)) {
                        $dtoToFilter[$key] = (int)$val;
                    }
                }
            }
        }

        return $dtoToFilter;
    }
}
