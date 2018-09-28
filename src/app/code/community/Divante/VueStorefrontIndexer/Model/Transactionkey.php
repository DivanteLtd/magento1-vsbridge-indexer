<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Transactionkey
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Transactionkey
{

    /**
     * @var int|string
     */
    private $key;

    /**
     * @return int|string
     */
    public function load()
    {
        if (null === $this->key) {
            $currentDate = new \Zend_Date();
            $this->key = $currentDate->getTimestamp();
        }

        return $this->key;
    }
}
