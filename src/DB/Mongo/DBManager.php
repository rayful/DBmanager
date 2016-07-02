<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/20
 * Time: 下午9:15
 */

namespace rayful\DB\Mongo;


abstract class DBManager extends DBConfig
{

    public function collection()
    {
        return $this->db()->{$this->collectionName()};
    }

    /**
     * 返回本对象的数据库集合名称
     * @return string
     */
    abstract protected function collectionName();

    public function findOne($query, $fields = [])
    {
        return $this->collection()->findOne($query, $fields);
    }

    public function save($data, $options = [])
    {
        return $this->collection()->save($data, $options);
    }

    public function update($criteria, $newObject, $options = [])
    {
        return $this->collection()->update($criteria, $newObject, $options);
    }

    public function remove($criteria, $options = [])
    {
        return $this->collection()->remove($criteria, $options);
    }

    public function ensureIndex($keys, $options = [])
    {
        $Collection = $this->collection();
        if(method_exists($Collection, "createIndex")){
            return $Collection->createIndex($keys, $options);
        }else if(method_exists($Collection, "ensureIndex")){
            return $Collection->ensureIndex($keys, $options);
        }
    }

    public function deleteIndex($keys)
    {
        return $this->collection()->deleteIndex($keys);
    }

    public function getGridFS()
    {
        return self::db()->getGridFS($this->collection()->getName());
    }

    public function getDBRef(array $ref)
    {
        return self::db()->getDBRef($ref);
    }

}