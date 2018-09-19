<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Indexer
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Indexer
{

    /**
     * Create new event log and register event in all processes
     *
     * @param   int $entityId
     * @param   string $entityType
     * @param   string $eventType
     * @return  Mage_Index_Model_Event
     */
    public function logEvent($entityId, $entityType, $eventType)
    {
        $event = Mage::getModel('vsf_indexer/event')
            ->setEntity($entityType)
            ->setType($eventType)
            ->setEntityPk($entityId);

        $event->save();

        return $event;
    }
}