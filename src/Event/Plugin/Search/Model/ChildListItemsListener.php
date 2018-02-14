<?php
namespace App\Event\Plugin\Search\Model;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Search\Event\EventName;

class ChildListItemsListener implements EventListenerInterface
{
    /**
     * DBlist constant
     */
    const LIST_TYPE_DBLIST = 'dblist';

    /**
     * File list constant
     */
    const LIST_TYPE_FILELIST = 'list';

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_SEARCH_CHILD_ITEMS => 'childItemsForParent',
        ];
    }

    /**
     * childItemsForParent method
     *
     * @param \Cake\Event\Event $event Event instance
     * @param array $criteria to build where statement
     * @return array
     */
    public function childItemsForParent(Event $event, $criteria)
    {
        if (empty($criteria['criteria'])) {
            return $criteria;
        }

        foreach ($criteria['criteria'] as $key => $val) {
            $items = [];

            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    if ($v['operator'] != 'in') {
                        continue;
                    }

                    array_push($items, $v['value']);

                    $ret = $this->processChildren($v['value'], $v['type'], $key);

                    if (!empty($ret)) {
                        $items = array_merge($items, $ret);
                    }

                    $criteria['criteria'][$key][$k]['value'] = $items;
                }
            }
        }

        return $criteria;
    }

    /**
     * getDbListChildren method
     *
     * @param string $parentId of parent item
     * @param object $table where lists are stored
     * @return array
     */
    private function getDbListChildren($parentId, $table)
    {
        $query = $table->find('all', [
            'conditions' => ['parent_id' => $parentId],
        ]);
        $children = $query->toArray();

        return $children;
    }

    /**
     * getFileListChildren method
     *
     * @param string $parentValue of parent item
     * @param string $listName for target list
     * @return array with children elements or empty
     */
    private function getFileListChildren($parentValue, $listName)
    {
        if (strpos($listName, '.') !== false) {
            list ($module, $name) = explode('.', $listName);

            $moduleConfig = new ModuleConfig(ConfigType::MIGRATION(), $module);
            $fields = $moduleConfig->parse();
            $fields = json_decode(json_encode($fields), true);

            $fieldInfo = $fields[$name];
            $type = $fieldInfo['type'];
            preg_match('/\((.*)\)/', $type, $match);
            $listName = !empty($match[1]) ? $match[1] : null;
        }

        $moduleConfig = new ModuleConfig(ConfigType::LISTS(), null, $listName);
        $listData = $moduleConfig->parse()->items;
        $result = json_decode(json_encode($listData), true);

        $list = [];
        foreach ($result as $item) {
            if ($item['value'] == $parentValue && !empty($item['children'])) {
                foreach ($item['children'] as $child) {
                    array_push($list, ['value' => $child['value']]);
                }
            }
        }

        return $list;
    }

    /**
     * processChildren method
     *
     * @param string $value to search
     * @param string $type - dblist of list stored in files
     * @param string $listName to find children in
     * @return array with childen items or empty
     */
    private function processChildren($value, $type = self::LIST_TYPE_DBLIST, $listName = '')
    {
        $result = [];
        $list = [];

        if ($type == static::LIST_TYPE_DBLIST) {
            $table = TableRegistry::get('CsvMigrations.DblistItems');
            $query = $table->find('all', [
                'conditions' => ['value' => $value],
            ]);
            $item = $query->first();

            $list = $this->getDbListChildren($item['id'], $table);
        } elseif ($type == static::LIST_TYPE_FILELIST) {
            $list = $this->getFileListChildren($value, $listName);
        }

        foreach ($list as $item) {
            array_push($result, $item['value']);
            $ret = $this->processChildren($item['value'], $type, $listName);

            if (!empty($ret)) {
                $result = array_merge($result, $ret);
            }
        }

        return $result;
    }
}
