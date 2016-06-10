<?php
/**
 * Created by PhpStorm.
 * User: kingmax
 * Date: 16/4/20
 * Time: 下午9:15
 */

namespace rayful\DB\Mongo;


use rayful\Tool\Pagination\MorePage;
use rayful\Tool\Pagination\Pagination;

abstract class DataSet implements \Iterator
{
    /**
     * 数据库游标
     * @var \MongoCursor
     */
    protected $_cursor;

    /**
     * 数据库搜索条件
     * @var array
     */
    protected $_query = [];

    /**
     * 数据库搜索条件
     * @var array
     */
    protected $_sort = [];

    /**
     * 数据指针是否永不超时,当数据量很大时间执行时间很长的时候,需要通过setImmortal方法将此属性设置为true
     * @var bool
     */
    protected $_immortal = false;

    /**
     * 最大个数
     * @var int
     */
    protected $_limit;

    /**
     * 跳过个数
     * @var int
     */
    protected $_skip = 0;

    /**
     * 当前位置
     * @var int
     */
    protected $_position = 0;

    /**
     * 数据库搜索字段,不设定默认搜索全部字段
     * @var array
     */
    protected $_fields = [];

    /**
     * 分页类
     * @var Pagination
     */
    protected $_pagination;

    function __construct($query = [])
    {
        $this->find($query);
    }

    function __toString()
    {
        $names = [];
        foreach ($this as $Iterated) {
            $names[] = $Iterated->name();
        }
        return implode(",", $names);
    }

    function __toArray()
    {
        $array = [];
        foreach ($this as $id => $Iterated) {
            $array[strval($id)] = $Iterated;
        }
        return $array;
    }

    /**
     * 声明迭代器返回的对象实例
     * @example return new Product();   //Product是Data的子类
     * @return Data
     */
    abstract protected function iterated();

    /**
     * 返回本对象的数据库集合名称
     * @return \MongoCollection
     */
    protected function collection()
    {
        return $this->iterated()->DBManager()->collection();
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Data
     */
    public function current()
    {
        $data = $this->getCursor()->current();
        $Object = $this->iterated();
        $Object->set($data);
        return $Object;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getCursor()->next();
        ++$this->_position;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->getCursor()->current()['_id'];
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->getCursor()->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return DataSet  Any returned value is ignored.
     */
    public function rewind()
    {
        $this->query();
        $this->next();
        return $this;
    }

    /**
     * 当前循环到哪一个位置(真正位置,考虑到skip这个属性)
     * @return int
     */
    public function position()
    {
        return $this->getPosition() + $this->getSkip();
    }

    final public function query()
    {
        $Cursor = $this->collection()->find($this->getQuery(), $this->getFields());
        if ($this->isImmortal()) $Cursor->immortal();
        if ($this->getSort()) $Cursor->sort($this->getSort());
        if ($this->getLimit()) $Cursor->limit($this->getLimit());
        if ($this->getSkip()) $Cursor->skip($this->getSkip());
        $this->setCursor($Cursor);

        return $this;
    }

    final public function find(array $query)
    {
        $this->_query = $query;
        return $this;
    }

    final public function findOne(array $query, array $sort = [])
    {
        $this->find($query)->sort($sort)->limit(1)->rewind()->current();
    }

    final public function ensureIndexAndFind($query)
    {
        $indexQuery = array_fill_keys(array_keys($query), 1);
        $this->iterated()->DBManager()->ensureIndex($indexQuery);
        return $this->find($query);
    }

    final public function limit($limit)
    {
        $this->_limit = intval($limit);
        return $this;
    }

    final public function sort(array $sort)
    {
        $this->_sort = $sort;
        return $this;
    }

    final public function skip($skip)
    {
        $this->_skip = intval($skip);
        return $this;
    }

    private function getCursor()
    {
        return $this->_cursor;
    }

    private function setCursor(\MongoCursor $Cursor)
    {
        $this->_cursor = $Cursor;
        return $this;
    }

    final public function getQuery()
    {
        return $this->_query;
    }

    final public function getSort()
    {
        return $this->_sort;
    }

    private function isImmortal()
    {
        return $this->_immortal;
    }

    final public function setImmortal($immortal)
    {
        $this->_immortal = $immortal;
        return $this;
    }

    final public function getLimit()
    {
        return $this->_limit;
    }

    final public function getSkip()
    {
        return $this->_skip;
    }

    final public function getPosition()
    {
        return $this->_position;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function setFields($fields)
    {
        $this->_fields = $fields;
        return $this;
    }

    public function count()
    {
        return $this->collection()->count($this->getQuery());
    }

    public function update($newObject)
    {
        return $this->collection()->update($this->getQuery(), $newObject, ['multiple' => true, 'safe' => true]);
    }

    public function remove()
    {
        if (!$this->getQuery()) throw new \Exception("批量删除必须指定一个删除条件(query)。请检查。");
        return $this->collection()->remove($this->getQuery());
    }

    public function readRequest()
    {
        if ($_REQUEST) {
            $this->setByRequest($_REQUEST);
        }
    }

    public function setByRequest(array $request)
    {
        foreach ($request as $requestId => $requestValue) {
            if ($requestId !== "") {
                $method = "_request_" . $requestId;
                if (method_exists($this, $method)) {
                    $this->{$method}($requestValue);
                }
            }
        }
        return $this;
    }

    /**
     * 这个用在根据ID精确找
     * @param string $requestValue
     */
    protected function _request_id($requestValue)
    {
        $this->find([
            '_id' => new \MongoId($requestValue)
        ]);
    }

    /**
     * 这个用在传递ID集批量找
     * @param array $requestValue
     */
    protected function _request_ids(array $requestValue)
    {
        $this->find([
            '_id' => ['$in' => array_map(function ($id) {
                if (is_string($id)) {
                    return new \MongoId($id);
                }
            }, $requestValue)]
        ]);
    }

    /**
     * 这个用在跨页全选,前端先通过getQuery()方法把当前搜索的query serialize()+base64_encode()传递过来，后端就能找回之前搜索的条件然后批量进行操作
     * @param string $requestValue
     */
    protected function _request_query($requestValue)
    {
        $this->find(unserialize(base64_decode($requestValue)));
    }

    /**
     * 这个用在前端指定每页显示多少个时有用
     * @param $requestValue
     */
    protected function _request_limit($requestValue)
    {
        $this->limit(intval($requestValue));
    }

    /**
     * 这个用在前端指定排序方法时有用,可指定排序字段还有是正序还是反序
     * @param array $requestValue
     * @example ['field'=>'used','type'=>'1'] ['field'=>'title','type'=>'-1']
     */
    protected function _request_sort(array $requestValue)
    {
        if (!empty($requestValue['field']) && !empty($requestValue['type'])) {
            $this->sort([
                $requestValue['field'] => (intval($requestValue['type']) > 0 ? 1 : -1)
            ]);
        }
    }

    /**
     * 这里指定用哪个分页类来分类.如果返回Pagination的子类,可以改变默认的显示方式.
     * @return MorePage
     */
    protected function genPagination()
    {
        return new MorePage();
    }

    /**
     * 在foreach(即执行实际的数据库查询)前调用此方法将可以自动分页.直接打印此方法返回的实例将可以显示出分页.
     * @param string $key 通过URL Query的什么参数来传递当前页码值
     * @return Pagination
     */
    final public function paginate($key = "page")
    {
        if (is_null($this->_pagination)) {
            $Pagination = $this->genPagination()->setKey($key)->setTotal($this->count())->setLimit($this->_limit);
            $this->skip($Pagination->getSkip());
            $this->_pagination = $Pagination;
        }
        return $this->_pagination;
    }

}