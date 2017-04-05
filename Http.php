<?php

/**
 * Http请求包
 * User: zb
 * Date: 17-1-25
 * Time: 上午10:01
 */
class Http
{
    /*
     * get请求
     */
    public static function Get($url, $param = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if ($param) {
            if (strpos($url, "?") !== false) {
                $url .= "&" . http_build_query($param);
            } else {
                $url .= "?" . http_build_query($param);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /*
     * post请求
     */
    public static function Post($url, $param)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_URL, $url);

        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /*
     * 返回json格式的post包
     */
    public static function Json($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /*
     * 获取ip
     */
    public static function IP()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }
        return $ip;
    }

    /*
     * 获取cookie数据
     */
    public static function getCookie($key, $default = "")
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /*
     * 设置cookie
     */
    public static function setCookie($key, $val, $second = null, $path = null, $domain = null, $httponly = null)
    {
        return setcookie($key, $val, $second + time(), $path, $domain, null, $httponly);
    }

    /*
     * 跳转
     */
    public static function Location($url)
    {
        header("Location: {$url}");
    }

    /*
     * header 附件下载
     */
    public static function Attachment($filename)
    {
        header("Content-type: application/octet-stream");
        header("Content-Length: " . filesize($filename));
        header("Content-Disposition: attachment; filename=" . basename($filename));
        readfile($filename);
    }

}