<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/20
 * Time: 下午9:07
 */

namespace rayful\DB\Mongo;


use rayful\Tool\objectTool;

abstract class Data
{
    use objectTool;
    
    /**
     * 主键字段，所有数据表都存在。
     * @var MongoId
     */
    public $_id;

    public function __construct($param = null)
    {
        if ($param) {
            $this->findOne($param);
        }
    }

    function __toString()
    {
        return strval($this->name());
    }

    /**
     * 必须实现，一般返回这条数据的名称(可以是关乎这条数据的任何标识)，用于直接打印这个对象的时候将返回什么。
     * @return String
     */
    abstract public function name();

    /**
     * 声明数据库管理实例
     * @example return new ProductManager();    //是一个DBManager的子类实例
     * @return DBManager
     */
    abstract public function DBManager();

    /**
     * 根据参数自动在数据中找数据
     * @param string|\MongoId|array $param 智能类型，可以是数组(query),也可以是ID(可以是字符串类型或\MongoId类型)
     * @return $this
     */
    public function findOne($param)
    {
        $query = $this->genQueryByParam($param);
        $data = $this->DBManager()->findOne($query);
        return $this->set($data);
    }

    private function genQueryByParam($param)
    {
        if (is_array($param)) {
            $query = $param;
        } elseif (is_string($param)) {
            $mongoId = new \MongoId($param);
            $query = ['_id' => $mongoId];
        } elseif ($param instanceof \MongoId) {
            $mongoId = $param;
            $query = ['_id' => $mongoId];
        }
        return isset($query) ? $query : false;
    }

    /**
     * 检查当前实例是否是在数据库里面真实存在.
     * @return bool
     */
    public function isExists()
    {
        return $this->_id ? true : false;
    }

    public function save()
    {
        if (is_null($this->_id)) unset($this->_id);
        $data = $this->toArray();
        $result = $this->DBManager()->save($data);
        $this->_id = $data['_id'];
        return $result;
    }

    public function update($newObject)
    {
        return $this->DBManager()->update(['_id' => $this->_id], $newObject, ['multiple' => false]);
    }

    public function delete()
    {
        return $this->DBManager()->remove(['_id' => $this->_id]);
    }

    public function initDate($field = 'date')
    {
        $this->{$field} = new \MongoDate();
    }
}