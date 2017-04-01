<?php

class FLog
{
    static private $flog = null;
    static private $fname = "";

    // index.php 入口时候调用，后面无需调用
    public static function Init($fname)
    {
        self::$fname = $fname;
        $f = fopen($fname, "a+");
        if (!$f) {
            throw new  Exception("创建日志文件失败,请检查是否有权限");
        }
        fclose($f);
    }

    private static function open()
    {
        $f = fopen(self::$fname, "a+");
        if (!$f) {
            throw new  Exception("创建日志文件失败,请检查是否有权限");
        }
        self::$flog = $f;

    }

    private static function close()
    {
        if (self::$flog) {
            fclose(self::$flog);
        }
    }

    static function Info($value)
    {
        self::open();
        $info = debug_backtrace();
        $file = $info[0]['file'];
        $linenum = $info[0]['line'];
        fprintf(self::$flog, "%s %s:%s [%s]  %s\n", date("Y-m-d H:i:s"), $file, $linenum, "INFO", json_encode($value));
        self::close();
    }

    static function Warn($value)
    {
        self::open();
        $info = debug_backtrace();
        $file = $info[0]['file'];
        $linenum = $info[0]['line'];
        fprintf(self::$flog, "%s %s:%s [%s]  %s\n", date("Y-m-d H:i:s"), $file, $linenum, "WARN", json_encode($value));
        self::close();
    }

    static function Error($value)
    {
        self::open();
        $info = debug_backtrace();
        $file = $info[0]['file'];
        $linenum = $info[0]['line'];
        fprintf(self::$flog, "%s %s:%s [%s]  %s\n", date("Y-m-d H:i:s"), $file, $linenum, "ERROR", json_encode($value));
        self::close();
    }

    static function Fatal($value)
    {
        self::open();
        $info = debug_backtrace();
        $file = $info[0]['file'];
        $linenum = $info[0]['line'];
        fprintf(self::$flog, "%s %s:%s [%s]  %s\n", date("Y-m-d H:i:s"), $file, $linenum, "FATAL", json_encode($value));
        self::close();
    }
}
