<?php
namespace App\Event\Plugin\CsvMigrations\FieldHandlers;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Utility\Inflector;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerInterface;

class MagicDefaultValueListener implements EventListenerInterface
{
    /**
     * @return array of implemented Events
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::FIELD_HANDLER_DEFAULT_VALUE() => 'getDefaultValue',
        ];
    }

    /**
     * Provide magic default value
     *
     * Convert a default value in magic form like %CURRENT_DATE%
     * to a dynamic value like '2017-06-27'.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param string $default Default value (before conversion)
     * @return mixed Converted value or previous default
     */
    public function getDefaultValue(Event $event, $default = null)
    {
        $result = $default;

        // If default is not in a magic format (%MAGIC_EXAMPLE%)
        // then return the default value as is
        if (!preg_match('/^%(.+)%$/', $default, $matches)) {
            return $result;
        }

        // Get field handler instance
        $fieldHandler = $event->subject();

        // Convert magic format to a method name.
        // For example: MAGIC_EXAMPLE = getMagicExampleValue
        $magicValue = strtolower($matches[1]);
        $magicValue = Inflector::camelize($magicValue);
        $magicValue = 'get' . $magicValue . 'Value';

        // TODO: Add some logging here for non-supported magic values
        if (method_exists($this, $magicValue) && is_callable([$this, $magicValue])) {
            $result = $this->$magicValue($fieldHandler);
        }

        return $result;
    }

    /**
     * CURRENT_DATE magic value
     *
     * @param $object $fieldHandler Field handler instance
     * @return string
     */
    protected function getCurrentDateValue($fieldHandler = null)
    {
        return date('Y-m-d');
    }

    /**
     * CURRENT_TIME magic value
     *
     * @param $object $fieldHandler Field handler instance
     * @return string
     */
    protected function getCurrentTimeValue($fieldHandler = null)
    {
        return date('H:i:s');
    }

    /**
     * CURRENT_DATETIME magic value
     *
     * @param $object $fieldHandler Field handler instance
     * @return string
     */
    protected function getCurrentDatetimeValue($fieldHandler = null)
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * CURRENT_DATETIME magic value
     *
     * @param $object $fieldHandler Field handler instance
     * @return string
     */
    protected function getCurrentUserIdValue($fieldHandler = null)
    {
        $result = null;

        // No way to figure out user without fieldHandler
        if (! $fieldHandler instanceof FieldHandlerInterface) {
            return $result;
        }

        $view = $fieldHandler->getConfig()->getView();

        if (empty($view->viewVars['user']['id'])) {
            return $result;
        }

        return $view->viewVars['user']['id'];
    }
}
