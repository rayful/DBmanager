<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/21
 * Time: 下午10:27
 */
spl_autoload_register("auto_load");

function auto_load($class_name)
{
    $class_name = str_replace("\\", "/", $class_name);  //支持命名空间
    $class_path = __DIR__ . "/" . $class_name . ".php";
    if (file_exists($class_path))
        require_once $class_path;
}