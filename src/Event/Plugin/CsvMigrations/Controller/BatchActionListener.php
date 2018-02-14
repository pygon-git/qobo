<?php
namespace App\Event\Plugin\CsvMigrations\Controller;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use CsvMigrations\Event\EventName;
use RolesCapabilities\CapabilityTrait;

class BatchActionListener implements EventListenerInterface
{
    use CapabilityTrait;

    /**
     * @return array of implemented events for sets module
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::BATCH_IDS() => 'batchAccessCheck',
        ];
    }

    /**
     * Access check for batch operation ids.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param array $batchIds Batch ids
     * @param string $operation Batch operation
     * @param array $user User info
     * @return void
     */
    public function batchAccessCheck(Event $event, array $batchIds, $operation, array $user)
    {
        $result = [];
        foreach ($batchIds as $batchId) {
            $url = [
                'plugin' => $event->subject()->plugin,
                'controller' => $event->subject()->name,
                'action' => $operation,
                $batchId
            ];
            if ($this->_checkAccess($url, $user)) {
                $result[] = $batchId;
            }
        }

        $event->result = $result;
    }
}
