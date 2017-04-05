<?php

/**
 * 验证类
 * User: zb
 * Date: 17-1-25
 * Time: 上午10:45
 */
/*----------------------------------------s
    $v = new Validation(["name" => '',
        "age" => '3344',
        'email'=>'aa.bb@qq.com',
        'phone' =>"151581"
    ]);
    $v->debug()->obj("name")->name("姓名")->notEmpty();
    $v->obj("age", "年龄")->notEmpty()->min(3)->max(5)->range(1, 3);
    $v->obj("email","邮箱")->notEmpty()->email();
    $v->obj("phone","电话")->notEmpty()->phone();
    if ($v->hasError()) {
        var_dump($v->getError());
    }
 -------------------------------------------*/
class Validation
{
    private $info = [];
    private $errMsg = [];
    private $objinfo = null;
    private $exist = true;
    private $debug = false;
    private $fieldName = "";

    public function __construct($info)
    {
        $this->info = $info;
    }

    /*
     * 是否开启debug模式
     */
    public function debug()
    {
        $this->debug = true;
        return $this;
    }

    /*
     * 初始化验证对象
     */
    public function obj($name, $fieldName = "")
    {
        if (!isset($this->info[$name])) {
            $this->exist = false;
            if ($this->debug) {
                $this->errMsg[] = "{$name}验证对象不存在";
            }
            $this->objinfo = null;
        } else {
            $this->exist = true;
            $this->objinfo = $this->info[$name];
            $this->fieldName = $fieldName;
        }
        return $this;
    }

    /*
     * 判断是否有错误
     */
    public function hasError()
    {
        if ($this->errMsg) {
            return true;
        }
        return false;
    }

    /*
     * 获取错误数组
     */
    public function getError()
    {
        return $this->errMsg;
    }

    /*
     * 验证提示命名
     */
    public function name($name)
    {
        $this->fieldName = $name;
        return $this;
    }

    /*
     * 不为空
     */
    public function notEmpty($tipMsg = "")
    {
        if (!$this->objinfo) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "{$this->fieldName}不能为空";
        }
        return $this;
    }


    /*
     * 至少几位 不包含
     */
    public function min($num, $tipMsg = "")
    {
        if ($this->exist && mb_strlen($this->objinfo) < $num) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "{$this->fieldName}少于{$num}位";
        }
        return $this;
    }

    /*
     * 至多几位 不包含
     */
    public function max($num, $tipMsg = "")
    {
        if ($this->exist && mb_strlen($this->objinfo) > $num) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "{$this->fieldName}多于{$num}位";
        }
        return $this;
    }

    /*
     * 范围 包含
     */
    public function range($min, $max, $tipMsg = "")
    {
        if ($this->exist && !($min <= mb_strlen($this->objinfo) && mb_strlen($this->objinfo) <= $max)) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "{$this->fieldName}长度不在[{$min},{$max}]内";
        }
        return $this;
    }

    /*
     * 是否是数字
     */
    public function isNumber($tipMsg = "")
    {
        if ($this->exist && is_numeric($this->objinfo)) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "{$this->fieldName}非数字";
        }
        return $this;
    }

    /*
     * 邮箱
     */
    public function email($tipMsg = "")
    {
        if ($this->exist && !preg_match("/^([0-9A-Za-z\\-_\\.]+)@(([0-9a-z]+\\.)?[0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $this->objinfo)) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "邮箱地址不正确";
        }
        return $this;
    }

    /*
     * 手机
     */
    public function phone($tipMsg = "")
    {
        if ($this->exist && !preg_match("/^1[34578]{1}\d{9}$/", $this->objinfo)) {
            $this->errMsg[] = $tipMsg ? $tipMsg : "电话号码不正确";
        }
        return $this;
    }

}