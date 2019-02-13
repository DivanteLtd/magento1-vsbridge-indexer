<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Sluggenerator
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Sluggenerator
{

    /**
     * @param string $name
     * @param int $id
     *
     * @return string
     */
    public function generate($name, $id)
    {
        $text = $name . '-' . $id;

        return $this->slugify($text);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function slugify($text)
    {
        $text = mb_strtolower($text);
        $text = preg_replace("/\s+/", '-', $text);// Replace spaces with -
        $text = preg_replace("/&/", '-and-', $text); //Replace & with 'and'
        $text = preg_replace("/[^\w-]+/", '', $text);// Remove all non-word chars
        $text = preg_replace("/--+/", '-', $text);// Replace multiple - with single -

        return $text;
    }
}
