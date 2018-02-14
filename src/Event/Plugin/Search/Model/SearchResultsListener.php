<?php
namespace App\Event\Plugin\Search\Model;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Search\Event\EventName;

class SearchResultsListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_SEARCH_AFTER_FIND() => 'afterFind'
        ];
    }

    /**
     * Method that handles search result-set.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\ORM\ResultSet $entities ResultSet
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     */
    public function afterFind(Event $event, ResultSet $entities, Table $table)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $fhf = new FieldHandlerFactory();

        foreach ($entities as $entity) {
            $this->_renderValues($entity, $table, $fhf);
        }

        $event->result = $entities;
    }

    /**
     * Passes search entity fields through Field Handlers.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @param \CsvMigrations\FieldHandlers\FieldHandlerFactory $fhf Field Handler Factory
     * @return void
     */
    protected function _renderValues(EntityInterface $entity, Table $table, FieldHandlerFactory $fhf)
    {
        foreach ($entity->visibleProperties() as $prop) {
            if ('_matchingData' === $prop) {
                foreach ($entity->{$prop} as $associationName => $targetEntity) {
                    $targetTable = $table->association($associationName)->getTarget();
                    $this->_renderValues($targetEntity, $targetTable, $fhf);
                }
            } else {
                $entity->{$prop} = $fhf->renderValue($table, $prop, $entity->{$prop});
            }
        }
    }
}
