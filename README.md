# DBmanger
能用的服务器管理包

安装:
```bash
composer require rayful/db-manager
```

### STEP 1 define Mongo Host and DB(一般放在全局的配置文件内)
```php
define("MONGO_HOST", "127.0.0.1");  //或者这里改成读取配置文件的方法
define("MONGO_DB", "xiyanghui");    //或者这里改成读取配置文件的方法
```

### STEP2 生成子类,一个表需要建立三个类(建议配合PHPStorm来创建子类,将得到需要实现什么方法的提示)

例子:数据库里面的Job表
---
Database Manager,数据库管理器
---
建议目录:src/Job/JobManager.php

```php
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

```
---
DataModel:数据结构的定义及单条记录的操作
---
建议目录:src/Job/Job.php
```php
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
```
---
DataSet Class:数据集
---
建议目录:src/Job/Jobs.php
```php
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
}
```

### STEP3 编写应用业务场景
* 单个职位

```php
//增
$Job = new \Job\Job();
$Job->title = "运营总监";
$Job->desc = "你是人才你就过来";
$Job->save();

//查
$id = $_REQUEST['id'];
$Job = new \Job\Job($id);
echo $Job->title;
echo $Job->desc;

//也可以传递一个$query或调用findOne()方法
$Job = new \Job\Job(['title'=>'运营总监']);
//或者
$Job = new \Job\Job();
$Job->findOne(['title'=>'运营总监']);   //当然这里的入参也是直接ID的,ID可以是字符串,也可以是MongoID类型，会自动转换

//改
$Job = new \Job\Job($id);
if ($Job->isExists()) {
    $Job->title = "技术总监";
    $Job->save();
} else {
    throw new Exception("职位不存在.");
}

//快捷修改
$Job = new \Job\Job($id);
$Job->update(['$set' => ['title' => $newTitle]]);

//删
$Job = new \Job\Job($id);
$Job->delete();
```

* 职位集(多个职位)

```php
//打印全部职位名称
$Jobs = new \Job\Jobs();
foreach ($Jobs as $Job) {
    echo $Job . "\n";
}

//按照一个条件,次序去找
$Jobs = new \Job\Jobs();
$Jobs->find(['title' => '行政助理']);   //入参为MongoDB的query参数,详情请见MongoDB的文档
foreach ($Jobs as $Job) {
    echo $Job . "\n";
}

//可以在构造函数内赋值query,与上面的


//支持排序,限制每页个数,支持分页
$Jobs = new \Job\Jobs();
$Jobs->find(['used' => true])->sort(['sequence' => 1])->limit(3);
echo $Jobs->paginate();
foreach ($Jobs as $Job) {
    echo $Job->title . ":" . $Job->desc . "\n\n";
}

//批量删(每个控制法)
$Jobs = new \Job\Jobs();
$Jobs->find(['used'=>false])->sort(['sequence'=>1])->limit(3);
foreach ($Jobs as $Job) {
    $Job->delete(); //这样子调用还可以调用Model的各种方法，比如检查是否有权限删等
}

//批量删(粗暴法)
$Jobs = new \Job\Jobs();
$Jobs->find(['used' => false])->remove(); //注意调用remove方法前必须要先调用find(或在构造函数时传入query参数)才能执行,安全性的考虑

//找到有多少个
$Jobs = new \Job\Jobs();
$count = $Jobs->find(['used' => false])->count();

//批量改(指定改条件)
$Jobs = new \Job\Jobs();
$Jobs->find(['used' => false])->update(['$set' => ['used' => true]]);

//按照指定条件及排序只找符合条件的第一个
$Jobs = new \Job\Jobs();
$Job = $Jobs->findOne(['used' => true], ['title' => 1]);    //第一个参数为query,第二个参数为sort方式

```

* 进阶应用：readRequest()方法

这个方法将直接读取$_REQUEST全局变量来决定搜索、排序、限制个数将使用什么条件.
##重要 使用这个方法必须是在后台,前台使用不过滤$_REQUEST会有被注入的危险
只要在Jobs类编写_request_{变量名称}()的方法,就能直接处理$_REQUEST的参数
比如:
```php
$_REQUEST = [
    'used'  =>  "1",
    'title' =>  '技术总监'    
];

class Jobs extends DataSet
{

    protected function _request_used($value)
    {
        $this->_query['used'] = boolval($value);
    }

    protected function _request_title($value)
    {
        $this->_query['title'] = strval($value);
    }
}

$Jobs = new \Job\Jobs();
$Jobs->readRequest();
```
等同于执行代码:
```php
$Jobs = new \Job\Jobs();
$query = [];
if (isset($_REQUEST['used']))
    $query['used'] = boolval($_REQUEST['used']);
if (isset($_REQUEST['title']))
    $query['title'] = strval($_REQUEST['title']);
$Jobs->find($query);
```

##DataSet基类自带有五个默认的_request_方法，只要前端按照这个request key来编写,将能自动处理request过来的参数
(所以如果调用readRequest()方法，请注意前端传递过来的变量名称和值类型要符合以下规则)
```php

    /**
     * 这个用在根据ID精确找
     * @param string $requestValue
     */
    protected function _request_id($requestValue){...}

    /**
     * 这个用在传递ID集批量找
     * @param array $requestValue
     */
    protected function _request_ids(array $requestValue){...}

    /**
     * 这个用在跨页全选,前端先通过getQuery()方法把当前搜索的query serialize()+base64_encode()传递过来，后端就能找回之前搜索的条件然后批量进行操作
     * @param string $requestValue
     */
    protected function _request_query($requestValue){...}

    /**
     * 这个用在前端指定每页显示多少个时有用
     * @param string $requestValue
     */
    protected function _request_limit($requestValue){...}

    /**
     * 这个用在前端指定排序方法时有用,可指定排序字段还有是正序还是反序
     * @param array $requestValue
     * @example ['field'=>'used','type'=>'1'] ['field'=>'title','type'=>'-1']
     */
    protected function _request_sort(array $requestValue){...}
```