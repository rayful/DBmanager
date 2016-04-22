<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/21
 * Time: 下午10:19
 */

namespace Job;


use rayful\DB\Mongo\Data;
use rayful\DB\Mongo\DBManager;

class Job extends Data
{
    /**
     * 排序
     * @var int
     * @name 排序
     */
    public $sequence;

    /**
     * 职位名称
     * @var string
     * @name 职位名称
     */
    public $title;

    /**
     * 职位描述
     * @var string
     * @name 职位描述
     * @input textarea
     */
    public $desc;

    /**
     * 职位部门
     * @var string
     * @name 职位部门
     */
    public $department;

    /**
     * 职位要求
     * @var string
     * @name 职位要求
     * @input textarea
     */
    public $require;

    /**
     * 工作地点
     * @var string
     * @name 工作地点
     */
    public $location;

    /**
     * 是否有效
     * @var boolean
     * @name 是否有效
     */
    public $used;

    /**
     * 必须实现，一般返回这条数据的名称(可以是关乎这条数据的任何标识)，用于直接打印这个对象的时候将返回什么。
     * @return String
     */
    public function name()
    {
        return $this->title;
    }

    /**
     * 声明数据库管理实例
     * @example return new ProductManager();    //是一个DBManager的子类实例
     * @return DBManager
     */
    public function DBManager()
    {
        return new JobManager();
    }
}