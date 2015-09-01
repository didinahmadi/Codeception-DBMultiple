<?php

/**
 * @author didinonpqcms@gmail.com
 * 
 * load multiple sql dump
 */ 

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Driver\Db as Driver;

class DBMultiple extends \Codeception\Module\Db
{

    protected $config = [
        'dumpPath' => '/root',
        'dumpFileExtension' => 'sql',
        'populate'  => true,
        'cleanup'   => true,
        'reconnect' => false,
        'dump'      => null
    ];

    protected static $dbs = [];

    protected $fullDumpPath;
    protected $dumpFiles = [];
    protected $sqls = [];


    private function loadDumpFiles()
    {
        $dir_iterator = new \RecursiveDirectoryIterator($this->fullDumpPath);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if (preg_match('/(.*)\.'. preg_quote($this->config['dumpFileExtension']) .'$/', $file)) {
                $this->dumpFiles[] = $file;

                $sql = file_get_contents($file);
                $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
                if (!empty($sql)) {
                    $this->sqls[] = explode("\n", $sql);
                }
            }
        }

        foreach ($this->sqls as $sqls) {
            $this->driver->load($sqls);
        }
    }

    public function _initialize()
    {

        if (($this->fullDumpPath=Configuration::projectDir() . $this->config['dumpPath']) && !file_exists($this->fullDumpPath)) {
            throw new ModuleConfigException('dumpPath must be defined');
        }
    }

    protected function getDb($database)
    {
        if (!array_key_exists($database, self::$dbs)) {
            $dsn = preg_replace('/dbname=(.*);?/', "dbname=".$database, $this->config['dsn']);
            $driver = Driver::create($dsn, $this->config['user'], $this->config['password']);
            self::$dbs[$database] = $driver;
        }
        return self::$dbs[$database];
    }

    /**
     * Count rows in database
     *
     * @param string $table    Table name
     * @param array  $criteria Search criteria [Optional]
     *
     * @return int
     */
    protected function countInDatabase($table, array $criteria = [], $database = null)
    {
        return (int) $this->proceedSeeInDatabase($table, 'count(*)', $criteria, $database);
    }

    public function seeInDatabase($table, $criteria = [], $database = null)
    {
        $res = $this->countInDatabase($table, $criteria, $database);
        $this->assertGreaterThan(0, $res, 'No matching records found');
    }

    public function dontSeeInDatabase($table, $criteria = [], $database = null)
    {
        $res = $this->countInDatabase($table, $criteria, $database);
        $this->assertLessThan(1, $res);
    }


    protected function proceedSeeInDatabase($table, $column, $criteria, $database = null)
    {
        if ($database) {
            $driver = $this->getDb($database);
            $query = $this->driver->select($column, $table, $criteria);
            $this->debugSection('Query', $query, json_encode($criteria));
            $sth = $driver->getDbh()->prepare($query);
            if (!$sth) {
                $this->fail("Query '$query' can't be executed.");
            }
            $sth->execute(array_values($criteria));
            return $sth->fetchColumn();
        } else {
            return parent::proceedSeeInDatabase($table, $column, $criteria);
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        parent::_before($test);
        $this->loadDumpFiles();
    }

    public function resetData()
    {
        $this->loadDumpFiles();
    }
}