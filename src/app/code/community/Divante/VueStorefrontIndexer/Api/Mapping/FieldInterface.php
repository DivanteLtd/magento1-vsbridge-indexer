<?php

/**
 * Interface Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface
 *
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
interface Divante_VueStorefrontIndexer_Api_Mapping_FieldInterface
{
    const TYPE_INT = 'integer';
    const TYPE_KEYWORD = 'keyword';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DOUBLE = 'double';
    const TYPE_LONG = 'long';
    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';

    const DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';
}
