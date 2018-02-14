<?php
namespace App\Event\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\AssociationCollection;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FileUploadsUtils;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

abstract class BaseActionListener implements EventListenerInterface
{
    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    /**
     * File association class name
     */
    const FILE_CLASS_NAME = 'Burzum/FileStorage.FileStorage';

    /**
     * Current module fields list which are associated with files
     *
     * @var array
     */
    protected $_fileAssociationFields = [];

    /**
     * An instance of Field Handler Factory
     *
     * @var \CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $factory;

    /**
     * Wrapper method that checks if Table instance has method 'findByLookupFields'
     * and if it does, it calls it, passing along the required arguments.
     *
     * @param  \Cake\ORM\Query   $query the Query
     * @param  \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function _lookupFields(Query $query, Event $event)
    {
        $methodName = 'findByLookupFields';
        $table = $event->subject()->{$event->subject()->name};
        if (!method_exists($table, $methodName) || !is_callable([$table, $methodName])) {
            return;
        }
        $id = $event->subject()->request['pass'][0];

        $table->{$methodName}($query, $id);
    }

    /**
     * Wrapper method that checks if Table instance has method 'setAssociatedByLookupFields'
     * and if it does, it calls it, passing along the required arguments.
     *
     * @param  \Cake\ORM\Entity  $entity Entity
     * @param  \Cake\Event\Event $event  Event instance
     * @return void
     */
    protected function _associatedByLookupFields(Entity $entity, Event $event)
    {
        $methodName = 'setAssociatedByLookupFields';
        $table = $event->subject()->{$event->subject()->name};
        if (!method_exists($table, $methodName) || !is_callable([$table, $methodName])) {
            return;
        }

        $table->{$methodName}($entity);
    }

    /**
     * Method that fetches action fields from the corresponding csv file.
     *
     * @param  \Cake\Network\Request $request Request object
     * @param  string                $action  Action name
     * @return array
     */
    protected function _getActionFields(Request $request, $action = null)
    {
        $controller = $request->controller;

        if (is_null($action)) {
            $action = $request->action;
        }

        $mc = new ModuleConfig(ConfigType::VIEW(), $controller, $action);
        $result = $mc->parse()->items;

        return $result;
    }

    /**
     * Move associated files under the corresponding entity property
     * and unset association property.
     *
     * Entity argument:
     *
     * ```
     * \Cake\ORM\Entity $object {
     *     'file' => null,
     *     'file_file_storage_file_storage' => [
     *         0 => \Burzum\FileStorage\Model\Entity\FileStorage $object,
     *         1 => \Burzum\FileStorage\Model\Entity\FileStorage $object
     *     ]
     * }
     * ```
     *
     * Becomes:
     *
     * ```
     * \Cake\ORM\Entity $object {
     *     'file' => [
     *         0 => \Burzum\FileStorage\Model\Entity\FileStorage $object,
     *         1 => \Burzum\FileStorage\Model\Entity\FileStorage $object
     *     ]
     * }
     * ```
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     */
    protected function _restructureFiles(Entity $entity, Table $table)
    {
        $fileAssociationFields = $this->_getFileAssociationFields($table);
        foreach ($fileAssociationFields as $associationName => $fieldName) {
            $associatedFieldName = Inflector::underscore($associationName);

            $entity->set($fieldName, $entity->get($associatedFieldName));
            $entity->unsetProperty($associatedFieldName);
            $this->_attachThumbnails($entity->{$fieldName}, $table);
        }
    }

    /**
     * Attach image file thumbnails into the entity.
     *
     * @param array $images Entity images
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     */
    protected function _attachThumbnails(array $images, Table $table)
    {
        if (empty($images)) {
            return;
        }

        $hashes = Configure::read('FileStorage.imageHashes.file_storage');
        $fileUploadsUtils = new FileUploadsUtils($table);
        $extensions = $fileUploadsUtils->getImgExtensions();

        // append thumbnails
        foreach ($images as &$image) {
            // skip  non-image files
            if (!in_array($image->extension, $extensions)) {
                continue;
            }

            $image->set('thumbnails', []);
            $path = dirname($image->path) . '/' . basename($image->path, $image->extension);
            foreach ($hashes as $name => $hash) {
                $thumbnailPath = $path . $hash . '.' . $image->extension;
                // if thumbnail does not exist, provide the path to the original image
                $thumbnailPath = !file_exists(WWW_ROOT . $thumbnailPath) ? $image->path : $thumbnailPath;
                $image->thumbnails[$name] = $thumbnailPath;
            }
        }
    }

    /**
     * Method that generates property name for belongsTo and HasOne associations.
     *
     * @param  string $name Association name
     * @return string
     */
    protected function _associationPropertyName($name)
    {
        list(, $name) = pluginSplit($name);

        return Inflector::underscore(Inflector::singularize($name));
    }

    /**
     * Method responsible for retrieving current Table's file associations
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociations(Table $table)
    {
        $result = [];

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }

            $result[] = $association->name();
        }

        return $result;
    }

    /**
     * Method responsible for retrieving file associations field names
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociationFields(Table $table)
    {
        $result = [];

        if (!empty($this->_fileAssociationFields)) {
            return $this->_fileAssociationFields;
        }

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }
            $result[$association->name()] = $association->conditions()['model_field'];
        }

        $this->_fileAssociationFields = $result;

        return $this->_fileAssociationFields;
    }

    /**
     * Convert Entity resource values to strings.
     * Temporary fix for bug with resources and json_encode() (see link).
     *
     * @param  \Cake\ORM\Entity $entity Entity
     * @return void
     * @link   https://github.com/cakephp/cakephp/issues/9658
     */
    protected function _resourceToString(Entity $entity)
    {
        $fields = array_keys($entity->toArray());
        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $this->_resourceToString($entity->{$field});
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }

                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    $this->_resourceToString($associatedEntity);
                }
            }

            if (is_resource($entity->{$field})) {
                $entity->{$field} = stream_get_contents($entity->{$field});
            }
        }
    }

    /**
     * Method that renders Entity values through Field Handler Factory.
     *
     * @param  \Cake\ORM\Entity       $entity    Entity instance
     * @param  \Cake\ORM\Table|string $table     Table instance
     * @param  array                 $fields    Fields to prettify
     * @return void
     */
    protected function _prettify(Entity $entity, $table, array $fields = [])
    {
        if (!$this->factory instanceof FieldHandlerFactory) {
            $this->factory = new FieldHandlerFactory();
        }
        if (empty($fields)) {
            $fields = array_keys($entity->toArray());
        }

        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $tableName = $table->association($entity->{$field}->source())->className();
                $this->_prettify($entity->{$field}, $tableName);
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }
                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    list(, $associationName) = pluginSplit($associatedEntity->source());
                    $tableName = $table->association($associationName)->className();
                    $this->_prettify($associatedEntity, $tableName);
                }
            }

            $entity->{$field} = $this->factory->renderValue($table, $field, $entity->{$field}, ['entity' => $entity]);
        }
    }
}
