<?php

/**
 * 响应类
 * User: zb
 * Date: 17-1-25
 * Time: 上午9:49
 */
class Response
{
    /*
     * 返回json格式，用于ajax
     */
    public static function toJson($ret, $msg = "", $data = null, $cbk = null)
    {
        $r = ['ret' => $ret, 'msg' => $msg, 'data' => $data];
        if ($cbk) {
            exit($cbk . '(' . json_encode($r, JSON_UNESCAPED_UNICODE) . ')');
        } else {
            exit(json_encode($r, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 返回jsonp格式
     */
    public static function toJsonp($cbk, $ret, $msg = "", $data = null)
    {
        $r = ['ret' => $ret, 'msg' => $msg, 'data' => $data];
        exit($cbk . '(' . json_encode($r, JSON_UNESCAPED_UNICODE) . ')');
    }

    /*
     * 返回js
     */
    public static function toJavascript($msg, $location = "")
    {
        header('Content-Type:text/html;charset=utf-8');
        $js = '<script type="text/javascript">';
        if (!empty($msg)) {
            $js .= "alert('$msg');";
        }
        if (!empty($url)) {
            $js .= "window.location = '$location';";
        }
        exit($js . '</script>');
    }
}