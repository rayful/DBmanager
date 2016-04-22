<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/22
 * Time: 上午10:37
 */

namespace Question;


use rayful\DB\Mongo\Data;
use rayful\DB\Mongo\DBManager;

class Question extends Data
{
    public $text;

    /**
     * 必须实现，一般返回这条数据的名称(可以是关乎这条数据的任何标识)，用于直接打印这个对象的时候将返回什么。
     * @return String
     */
    public function name()
    {
        return $this->text;
    }

    /**
     * 声明数据库管理实例
     * @example return new ProductManager();    //是一个DBManager的子类实例
     * @return DBManager
     */
    public function DBManager()
    {
        return new QuestionManager();
    }
}