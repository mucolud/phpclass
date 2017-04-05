<?php

/**
 * 请求类
 * User: zb
 * Date: 17-1-25
 * Time: 上午9:40
 */
class Request
{
    /*
     * 获取GET数据
     */
    public static function getQuery($key, $default = "")
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        } else {
            return $default;
        }
    }

    /*
     * 获取POST数据
     */
    public static function getPost($key, $default = "")
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        } else {
            return $default;
        }
    }


    /*
     * 获取GET或POST数据
     */
    public static function getVal($key, $default = "")
    {
        if (self::getQuery($key)) {
            return self::getQuery($key);
        }
        if (self::getPost($key)) {
            return self::getPost($key);
        }
        return $default;
    }

    /*
     * 过滤参数
     */
    public static function filterParam($params)
    {
        if (!empty($params)) {
            array_walk_recursive($params, function (&$value, $key) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            });
        }

        return $params;
    }

}