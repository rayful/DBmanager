<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/21
 * Time: 下午10:27
 */
require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/../vendor/autoload.php";


//这几行代码仅调试的时候需要,真正生产环境时就不需要了
spl_autoload_register("auto_load_src");

function auto_load_src($class_name)
{
    $class_name = str_replace("rayful\\", "", $class_name);  //支持命名空间
    $class_name = str_replace("\\", "/", $class_name);  //支持命名空间
    $class_path = __DIR__ . "/../src/" . $class_name . ".php";
    if (file_exists($class_path))
        require_once $class_path;
}
//----------------------------------

define("MONGO_HOST", "127.0.0.1");  //或者这里改成读取配置文件的方法
define("MONGO_DB", "xiyanghui");    //或者这里改成读取配置文件的方法

//打印全部职位名称
$Jobs = new \Job\Jobs();
foreach ($Jobs as $Job) {
    echo $Job . "\n";
}

//按照一个条件,次序去找
$Jobs = new \Job\Jobs();
$Jobs->find(['title'=>'行政助理']);
foreach ($Jobs as $Job) {
    echo $Job . "\n";
}

$Jobs = new \Job\Jobs();
$Jobs->find(['used'=>true])->sort(['sequence'=>1])->limit(3);
echo $Jobs->paginate();
foreach ($Jobs as $Job) {
    echo $Job->title.":".$Job->desc . "\n\n";
}

//增
$Job = new \Job\Job();
$Job->title = "运营总监";
$Job->desc = "需要高端人才";
$Job->save();

//查
$id = $_REQUEST['id'];
$Job = new \Job\Job($id);
echo $Job->title;
echo $Job->desc;

//改
$Job = new \Job\Job($id);
if($Job->isExists()){
    $Job->title = "技术总监";
    $Job->save();
}else{
    throw new Exception("职位不存在.");
}

//删
$Job = new \Job\Job($id);
$Job->delete();

//批量删
$Jobs = new \Job\Jobs();
$Jobs->find(['used'=>true])->sort(['sequence'=>1])->limit(3);
echo $Jobs->paginate();
foreach ($Jobs as $Job) {
    $Job->delete();
}
