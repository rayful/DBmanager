<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/21
 * Time: 下午10:21
 */

namespace Job;


use rayful\DB\Mongo\Data;
use rayful\DB\Mongo\DataSet;

class Jobs extends DataSet
{

    /**
     * 声明迭代器返回的对象实例
     * @example return new Product();   //Product是Data的子类
     * @return Data
     */
    protected function iterated()
    {
        return new Job();
    }

    protected function _request_used($value)
    {
        $this->_query['used'] = boolval($value);
    }

    protected function _request_title($value)
    {
        $this->_query['title'] = strval($value);
    }
}