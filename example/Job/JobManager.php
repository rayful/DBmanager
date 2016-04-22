<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/21
 * Time: 下午10:20
 */

namespace Job;


use rayful\DB\Mongo\DBManager;

class JobManager extends DBManager
{

    /**
     * 返回本对象的数据库集合名称
     * @return string
     */
    protected function collectionName()
    {
        return "job";
    }
}