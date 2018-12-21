<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Data_Gallery
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Data_Gallery
{

    /**
     * @param array $gallerySet
     *
     * @return array
     */
    public function prepareMediaGallery(array $gallerySet)
    {
        $galleryPerProduct = [];

        foreach ($gallerySet as $mediaImage) {
            $productId    = $mediaImage['product_id'];
            $image['typ'] = 'image';
            $image        = [
                'typ' => 'image',
                'image' => $mediaImage['file'],
                'lab' => $this->getValue('label', $mediaImage),
                'pos' => intval($this->getValue('position', $mediaImage)),
            ];

            $galleryPerProduct[$productId][] = $image;
        }

        return $galleryPerProduct;
    }

    /**
     * @param string $fieldKey
     * @param array  $image
     *
     * @return string
     */
    private function getValue($fieldKey, array $image)
    {
        if (isset($image[$fieldKey]) && (null !== $image[$fieldKey])) {
            return $image[$fieldKey];
        }

        if (isset($image[$fieldKey . '_default'])) {
            return $image[$fieldKey . '_default'];
        }

        return '';
    }
}