<?php

/**
 * @author didinonpqcms@gmail.com
 * 
 * load multiple sql dump
 */ 

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;

class DBMultiple extends \Codeception\Module
{

    protected $config = [
        'dumpPath' => '/root',
        'dumpFileExtension' => 'sql',
        'connection' => 'db'
    ];

    protected $db;
    protected $fullDumpPath;
    protected $dumpFiles = [];
    protected $sqls = [];

    public function getDbConnection()
    {
        if (($connection = $this->config['connection']) && is_string($connection)) {
            if (!$this->hasModule($connection)) {
                throw new \Exception('Cannot connect to ' . $connection);
            }
            return $this->getModule($connection);
        } else if (is_array($connection)) {
            /**
             * @todo
             */
        }
        return null;
    }

    public function loadDumpFiles()
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
            $this->db->driver->load($sqls);
        }
    }

    public function _initialize()
    {

        if (($this->fullDumpPath=Configuration::projectDir() . $this->config['dumpPath']) && !file_exists($this->fullDumpPath)) {
            throw new ModuleConfigException('dumpPath must be defined');
        }

        $this->db = $this->getDbConnection();

    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->loadDumpFiles();
    }
}