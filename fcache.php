<?php

/*
 * 简单文件缓存类
 * key:32位　value:128位　总共能存储１０００个ｋｅｙ
 */

class Fcache
{
    private $fhandle = null;
    public function __construct($fpath)
    {
        if (!$fpath) {
            throw new Exception("文件路径不能为空");
        }
        if (!file_exists($fpath)) {
            $this->fhandle = fopen($fpath, "w+");
        } else {
            $this->fhandle = fopen($fpath, "r+");
        }
        if (!$this->fhandle) {
            throw new Exception("文件创建失败");
        }
        $this->init();
        flock($this->fhandle, LOCK_EX);
    }
    public function init()
    {
        if (!fgetc($this->fhandle)) {
            //写入44000个空位符，并首部标记已初始化 1000key
            fwrite($this->fhandle, pack("cLLx440000", 1, 9, 440009));
        }
    }
    public function get($key)
    {
        $meta = $this->find($key);
        if ($meta[0]) {
            if ($meta[0] == "exist") {
                fseek($this->fhandle, $meta[1]['start']);
                return trim(fread($this->fhandle, 128));
            }
        }
        return "";
    }
    public function set($key, $value, $expire = 0)
    {
        $expire = $expire ? $expire + time() : 0;
        $meta = $this->find($key);
        $key = strlen($key) > 32 ? substr($key, 0, 32) : $key;
        $key = $key . str_repeat("", 32 - strlen($key));
        $value = strlen($value) > 128 ? substr($value, 0, 128) : $value;
        $value = $value . str_repeat("", 128 - strlen($value));
        // var_dump($meta);
        // 已存在
        if ($meta[0] == "exist") {
            fseek($this->fhandle, $meta[1]['kstart']);
            fwrite($this->fhandle, pack("a32lll", $key, $meta[1]['start'], $meta[1]['end'], $expire));
            fseek($this->fhandle, $meta[1]['start']);
            fwrite($this->fhandle, pack("a128", $value));
            fflush($this->fhandle);
            return true;
        }
        //过去的key
        if ($meta[0] == "remove") {
            fseek($this->fhandle, $meta[1]['kstart']);
            fwrite($this->fhandle, pack("a32lll", $key, $meta[1]['start'], $meta[1]['end'], $expire));
            fseek($this->fhandle, $meta[1]['start']);
            fwrite($this->fhandle, pack("a128", $value));
            fflush($this->fhandle);
            return true;
        }
        //插入
        fseek($this->fhandle, $meta[1]['kend']);
        fwrite($this->fhandle, pack("a32lll", $key, $meta[1]['vend'], $meta[1]['vend'] + 128, $expire));
        fseek($this->fhandle, $meta[1]['vend']);
        fwrite($this->fhandle, pack("a128", $value));
        fseek($this->fhandle, 1);
        fwrite($this->fhandle, pack("L", $meta[1]['kend'] + 44));
        fseek($this->fhandle, 5);
        fwrite($this->fhandle, pack("L", $meta[1]['vend'] + 128));
        fflush($this->fhandle);
        return true;
    }
    private function find($key)
    {
        fseek($this->fhandle, 0);
        //首先获取所有key的meta信息
        $metas = fread($this->fhandle, 440009);
        if (!$metas) {
            return false;
        }
        $hmetas = unpack("ctag/Lkend/Lvend", $metas);
        //遍历数据所有数据
        $startIndex = 9;
        $keysMetas = [];
        for ($i = $startIndex; $i < strlen($metas); $i = $i + 44) {
            $tmp = substr($metas, $i, 44);
            $meta = @unpack("a32key/lstart/lend/lexpire", $tmp);
            if ($meta && trim($meta['key']) != "") {
                // var_dump($meta);
                $meta['kstart'] = $i;
                $keysMetas[] = $meta;
            }
        }
        foreach ($keysMetas as $met) {
            if (!isset($met['key'])) {
                continue;
            }
            if (trim($met['key']) == trim($key)) {
                //判断有没有过期
                if (intval($met['expire']) != 0 && intval($met['expire']) < time()) {
                    return ['remove', $met, $hmetas];
                }
                return ['exist', $met, $hmetas];
            }
        }
        //查找过期的key
        foreach ($keysMetas as $met) {
            if (!isset($met['key'])) {
                continue;
            }
            if (intval($met['expire']) != 0 && intval($met['expire']) < time()) {
                return ['remove', $met, $hmetas];
            }
        }
        return [false, $hmetas];
    }
    public function del($key)
    {
        $meta = $this->find($key);
        if ($meta[0] == "exist") {
            fseek($this->fhandle, $meta[1]['start']);
            fwrite($this->fhandle, pack("a128", ""));
            fseek($this->fhandle, $meta[1]['kstart']);
            fwrite($this->fhandle, pack("a32lll", $key, $meta[1]['start'], $meta[1]['end'], -1));
            fflush($this->fhandle);
        }
    }
    public function __destruct()
    {
        if ($this->fhandle) {
            flock($this->fhandle, LOCK_UN);
            fclose($this->fhandle);
        }
    }
}