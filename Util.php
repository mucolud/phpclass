<?php

/**
 * 工具包
 * User: zb
 * Date: 17-1-25
 * Time: 上午10:08
 */
class Util
{

    /*
     * 生成验证码
     * @font_file 字体文件默认为/public/t1.ttf
     */
    public static function createVerifyCode($key, $font_file = "")
    {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $text = "";
        $num = array();
        for ($i = 0; $i < 4; $i++) {
            $num[$i] = rand(0, 25);
            $text .= $str[$num[$i]];
        }
        $_SESSION[$key] = strtolower($text);

        $im_x = 160;
        $im_y = 40;
        $im = imagecreatetruecolor($im_x, $im_y);
        $text_c = ImageColorAllocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $tmpC0 = mt_rand(100, 255);
        $tmpC1 = mt_rand(100, 255);
        $tmpC2 = mt_rand(100, 255);
        $buttum_c = ImageColorAllocate($im, $tmpC0, $tmpC1, $tmpC2);
        imagefill($im, 16, 13, $buttum_c);

        if (!$font_file) {
            $font = realpath(BASE_PATH . '/public/t1.ttf');
        }

        for ($i = 0; $i < strlen($text); $i++) {
            $tmp = substr($text, $i, 1);
            $array = array(-1, 1);
            $p = array_rand($array);
            $an = $array[$p] * mt_rand(1, 10); //角度
            $size = 28;
            imagettftext($im, $size, $an, 15 + $i * $size, 35, $text_c, $font, $tmp);
        }

        $distortion_im = imagecreatetruecolor($im_x, $im_y);

        imagefill($distortion_im, 16, 13, $buttum_c);
        for ($i = 0; $i < $im_x; $i++) {
            for ($j = 0; $j < $im_y; $j++) {
                $rgb = imagecolorat($im, $i, $j);
                if ((int)($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) <= imagesx($distortion_im) && (int)($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) >= 0) {
                    imagesetpixel($distortion_im, (int)($i + 10 + sin($j / $im_y * 2 * M_PI - M_PI * 0.1) * 4), $j, $rgb);
                }
            }
        }
        //加入干扰象素;
        $count = 160; //干扰像素的数量
        for ($i = 0; $i < $count; $i++) {
            $randcolor = ImageColorallocate($distortion_im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($distortion_im, mt_rand() % $im_x, mt_rand() % $im_y, $randcolor);
        }

        $rand = mt_rand(5, 30);
        $rand1 = mt_rand(15, 25);
        $rand2 = mt_rand(5, 10);
        for ($yy = $rand; $yy <= +$rand + 2; $yy++) {
            for ($px = -80; $px <= 80; $px = $px + 0.1) {
                $x = $px / $rand1;
                if ($x != 0) {
                    $y = sin($x);
                }
                $py = $y * $rand2;

                imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
            }
        }

        //设置文件头;
        Header("Content-type: image/JPEG");

        //以PNG格式将图像输出到浏览器或文件;
        ImagePNG($distortion_im);

        //销毁一图像,释放与image关联的内存;
        ImageDestroy($distortion_im);
        ImageDestroy($im);
    }


    /*
     * 时间戳
     */
    public static function unix($time = "")
    {
        return $time ? strtotime($time) : time();
    }

    /*
     * 格式化时间
     */
    public static function date($unixTime, $format = "Y-m-d H:i:s")
    {
        return date($format, $unixTime);
    }

    /*
     * 时间戳:天
     * 支持设置前后几天，正负
     */
    public static function dayUnix($day = 0)
    {
        $datFormat = $day > 0 ? "+{$day} day" : "-{$day} day";
        return strtotime(date("Y-m-d", strtotime($datFormat)));
    }

    /*
     * 时间：天
     * 支持设置前后几天，正负
     */
    public static function dayStr($day = 0)
    {
        $datFormat = $day > 0 ? "+{$day} day" : "-{$day} day";
        return date("Y-m-d", strtotime($datFormat));
    }

    /*
     * 隐藏中间几位字符串
     */
    public static function hideStr($str, $b, $e, $open = true)
    {
        if (!$open) {
            return $str;
        }
        $bstr = mb_substr($str, 0, $b, 'utf8');
        $estr = mb_substr($str, $e, -1, 'utf8');
        if ($e == -1) {
            $estr = "";
        }
        return $bstr . ($e - $b >= mb_strlen($str) || $e - $b <= 0 ? "***" : str_repeat("*", $e - $b)) . $estr;
    }

    /*
     * 多久之前
     */
    public static function beforeDisNow($time, $str = null)
    {
        isset($str) ? $str : $str = 'm-d';
        $way = time() - $time;
        $r = '';
        if ($way < 60) {
            $r = '刚刚';
        } elseif ($way >= 60 && $way < 3600) {
            $r = floor($way / 60) . '分钟前';
        } elseif ($way >= 3600 && $way < 86400) {
            $r = floor($way / 3600) . '小时前';
        } elseif ($way >= 86400 && $way < 2592000) {
            $r = floor($way / 86400) . '天前';
        } elseif ($way >= 2592000 && $way < 15552000) {
            $r = floor($way / 2592000) . '个月前';
        } else {
            $r = date("$str", $time);
        }
        return $r;
    }

    /*
     * 加密
     */
    public static function encrypt($string, $key = '', $expiry = 0, $operation = 'encode')
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        // 密匙
        $key = md5($key ? $key : "#&0%o#d8$*s&5u^@*^s456");

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /*
     * 解密
     */
    public static function decrypt($string, $key = '')
    {
        return self::encrypt($string, $key, 0, "DECODE");
    }
}