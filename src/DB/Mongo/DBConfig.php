<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/20
 * Time: 下午8:59
 */

namespace rayful\DB\Mongo;


abstract class DBConfig
{

    /**
     * 数据库实例
     * @var \MongoDB
     */
    private static $db;

    /**
     * 单例模式获得当前的数据库实例
     * @return \MongoDB
     */
    protected function db()
    {
        if (!isset(self::$db)) {
            $MongoClient = new \MongoClient($this->host());
            self::$db = $MongoClient->{$this->dbName()};
        }
        return self::$db;
    }

    private function host()
    {
        if(defined("MONGO_HOST")){
            return MONGO_HOST;
        }else{
            throw new \Exception("You must define MONGO_HOST constant.");
        }
    }
    
    private function dbName()
    {
        if(defined("MONGO_DB")){
            return MONGO_DB;
        }else{
            throw new \Exception("You must define MONGO_DB constant.");
        }
    }
} 