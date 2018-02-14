<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Exception;
use Qobo\Utils\Utility\FileLock;

class CleanModulesDataShell extends Shell
{
    protected $modules = [];

    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Clean All Records a Module has');

        $parser->addOption('modules', [
            'short' => 'm',
            'help' => 'Module Names separated with "," eg. "Accounts,Contacts"',
            'default' => ''
        ]);

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @return void
     */
    public function main()
    {
        try {
            $lock = new FileLock('clean_records_' . md5(__FILE__) . '.lock');
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            $this->abort('Clean Module Data is already in progress');
        }

        $modulesstr = $this->param('modules');
        if (empty($modulesstr)) {
            $lock->unlock();
            $this->err("0 Modules Provided");
        }

        $modules = explode(",", $modulesstr);
        if (empty($modules)) {
            $lock->unlock();
            $this->err("0 Modules Provided");
        }

        $tables = ConnectionManager::get('default')->schemaCollection()->listTables();
        $this->modules = $modules;

        //tranform module names into lowercase.
        foreach ($this->modules as $key => $moduleName) {
            if (empty($moduleName)) {
                unset($this->modules[$key]);
                continue;
            }
            $this->modules[$key] = Inflector::tableize($moduleName);
        }

        $notExistsModules = array_diff($this->modules, $tables);

        foreach ($notExistsModules as $key => $moduleName) {
            $this->warn($moduleName . ' does not exists!');
        }

        $this->modules = array_intersect($this->modules, $tables);

        foreach ($this->modules as $moduleName) {
            $this->clearModuleData($moduleName);
        }

        $this->success('Clean Module Data Completed');

        // unlock file
        $lock->unlock();
    }

    /**
     * Clear All Module Records.
     *
     * @param string $moduleName module name.
     * @return int
     */
    protected function clearModuleData($moduleName)
    {
        $rowCount = 0;
        if (empty($moduleName)) {
            return $rowCount;
        }

        try {
            $table = TableRegistry::get($moduleName);
            $rowCount = $table->deleteAll([]);
        } catch (Exception $ex) {
        }
        $this->info($moduleName . ' Module Deleted Records: ' . $rowCount);

        return $rowCount;
    }
}
