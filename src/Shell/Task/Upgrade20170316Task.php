<?php

namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;

/**
 *  Set value of the datetime field in case it allows null and it has value 0000-00-00 00:00:0
 */
class Upgrade20170316Task extends Shell
{
    /**
     *  Default datetime field name
     */
    const DEFAULT_COLUMN_NAME = 'trashed';

    /**
     *  Default field type
     */
    const TARGET_COLUMN_TYPE = 'datetime';

    /**
     * Configure option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('Convert datetime fields, which accept null value, from 0000-00-00 00:00:00 to NULL');
        $parser->addArgument('target_table', [
            'help' => 'Target table to fix datetime null value (optional)',
            'required' => false,
        ]);
        $parser->addArgument('target_field', [
            'help' => 'Target field to fix datetime null value (optional)',
            'required' => false,
        ]);

        return $parser;
    }

    /**
     *  Run update process
     *
     * @return void
     */
    public function main()
    {
        $targetTable = !empty($this->args[0]) ? $this->args[0] : null;
        $targetColumn = !empty($this->args[1]) ? $this->args[1] : self::DEFAULT_COLUMN_NAME;

        $db = ConnectionManager::get('default');
        $collection = $db->schemaCollection();

        if (!empty($targetTable)) {
            $tables = [$targetTable];
        } else {
            $tables = $collection->listTables();
        }

        foreach ($tables as $tbl) {
            $tblSchema = $collection->describe($tbl);

            $columns = $tblSchema->columns();

            if (in_array($targetColumn, $columns)) {
                $columnType = $tblSchema->columnType($targetColumn);
                $this->out("Type of column '$targetColumn' has type '$columnType'");

                if ($tblSchema->isNullable($targetColumn) && $columnType == self::TARGET_COLUMN_TYPE) {
                    $this->out("Column '$targetColumn' can be null in the table '$tbl' and has the target type '$columnType'. Update data ...");
                    $result = $db->query("UPDATE $tbl SET $targetColumn=NULL WHERE CAST($targetColumn as CHAR(20)) = '0000-00-00 00:00:00'");
                } else {
                    $this->out("Field '$targetColumn' cannot be null in the table '$tbl' or its type is not the target one: '$columnType'!");
                }
            }
        }
    }
}
