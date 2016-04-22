<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/22
 * Time: 上午10:36
 */

namespace Question;


use rayful\DB\Mongo\DBManager;

class QuestionManager extends DBManager
{

    /**
     * 返回本对象的数据库集合名称
     * @return string
     */
    protected function collectionName()
    {
        return "question";
    }
}