<?php


namespace app\utils;

/**
 * 生成随机码
 * Class RandomCodeUtil
 * @package app\utils
 */
class RandomCodeUtil
{


    public static function getCode($length = 8)
    {
        $str = substr(md5(time()), 0, $length);
        return $str;
    }

    /**
     * token生成
     * @return string
     */
    public static function createUniqueToken()
    {
        //客户端+IP+时间戳+随机数组成的字符串
        $data = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time() . rand();
        //使用sha1加密
        return sha1($data);
    }


    /**
     * 生成密码加密字符串
     * @param int $length
     * @return false|string
     */
    public static function randomSalt($length = 8)
    {
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $name = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), $length);
        return $name;
    }

    /**
     * 产生随机数字验证码
     * @param int $length
     * @return string
     */
    public static function randomNumber($length = 4) {
        $key = '';
        $pattern='1234567890';
        for( $i=0; $i<$length; $i++ ) {
            $key .= $pattern[mt_rand(0, 9)];
        }
        return $key;
    }
}