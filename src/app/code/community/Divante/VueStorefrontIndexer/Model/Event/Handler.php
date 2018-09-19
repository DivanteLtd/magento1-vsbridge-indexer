<?php

/**
 * Class Divante_VueStorefrontIndexer_Model_Event_Handler
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2018 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_Event_Handler
{

    /**
     * Create new event log
     *
     * @param int $entityId
     * @param string $entityType
     * @param string $eventType
     *
     * @return Divante_VueStorefrontIndexer_Model_Event
     * @throws Exception
     */
    public function logEvent($entityId, $entityType, $eventType)
    {
        /** @var Divante_VueStorefrontIndexer_Model_Event $event */
        $event = Mage::getModel('vsf_indexer/event')
            ->setEntity($entityType)
            ->setType($eventType)
            ->setEntityPk($entityId);

        $event->save();

        return $event;
    }
}