<?php

use Divante_VueStorefrontIndexer_Model_Event_Handler as EventHandler;
use Divante_VueStorefrontIndexer_Model_Indexer_Partialupdate_Category_Gridperpage as GridPerPageIndexer;

/**
 * Class Divante_VueStorefrontIndexer_Model_System_Config_Backend_Gridperpage
 *
 * @package     Divante
 * @category    VueStoreFrontIndexer
 * @author      Agata Firlejczyk <afirlejczyk@divante.pl
 * @copyright   Copyright (C) 2019 Divante Sp. z o.o.
 * @license     See LICENSE_DIVANTE.txt for license details.
 */
class Divante_VueStorefrontIndexer_Model_System_Config_Backend_Gridperpage extends Mage_Core_Model_Config_Data
{
    /**
     * @var EventHandler
     */
    protected $eventHandler;

    /**
     * Divante_VueStoreFrontElasticSearch_Model_Observer_LogEventObserver constructor.
     */
    public function __construct()
    {
        $this->eventHandler = Mage::getSingleton('vsf_indexer/event_handler');
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            try {
                $this->logEvent(
                    '',
                    GridPerPageIndexer::ENTITY_TYPE,
                    'full'
                );
            } catch (\Exception $e) {
                Mage::logException($e);
            }
        }

        parent::_afterSave();
    }

    /**
     * @param $id
     * @param $entityType
     * @param $eventType
     *
     * @throws Exception
     */
    public function logEvent($id, $entityType, $eventType)
    {
        $this->eventHandler->logEvent(
            $id,
            $entityType,
            $eventType
        );
    }
}
