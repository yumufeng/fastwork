<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:03
 */

use fastwork\facades\Config;
use fastwork\facades\Cookie;
use fastwork\facades\Env;


/**
 * 生成UUID
 * @param bool $base62
 * @return string
 * @throws Exception
 */
function uuid($base62 = true)
{
    $str = uniqid('', true);
    $arr = explode('.', $str);
    $str = $arr[0] . base_convert($arr[1], 10, 16);
    $len = 32;
    while (strlen($str) <= $len) {
        $str .= bin2hex(random_bytes(4));
    }
    $str = substr($str, 0, $len);
    if ($base62) {
        $str = str_replace(['+', '/', '='], '', base64_encode(hex2bin($str)));
    }
    return $str;
}


/**
 * 从数组中根据key取出值
 * @param array $arr
 * @param $key
 * @return mixed|null
 */
function array_get($arr, $key, $default = null)
{
    if (isset($arr[$key])) {
        return $arr[$key];
    } else if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        foreach ($keys as $v) {
            if (isset($arr[$v])) {
                $arr = $arr[$v];
            } else {
                return $default;
            }
        }
        return $arr;
    } else {
        return $default;
    }
}

/**
 * 获取协程ID
 * @return mixed
 */
function get_co_id()
{
    return \Swoole\Coroutine::getuid();
}

/**
 * 过滤xss
 * @param $str
 * @param null $allow_tags
 * @return string
 */
function filter_xss($str, $allow_tags = null)
{
    $str = strip_tags($str, $allow_tags);
    if ($allow_tags !== null) {
        while (true) {
            $l = strlen($str);
            $str = preg_replace('/(<[^>]+?)([\'\"\s]+on[a-z]+)([^<>]+>)/i', '$1$3', $str);
            $str = preg_replace('/(<[^>]+?)(javascript\:)([^<>]+>)/i', '$1$3', $str);
            if (strlen($str) == $l) {
                break;
            }
        }
    }
    return $str;
}

/**
 * 读取配置文件
 */
if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array $name 参数名
     * @param mixed $value 参数值
     * @return mixed
     */
    function config($name = '', $value = null)
    {
        if (is_string($name)) {
            if ('.' == substr($name, -1)) {
                return Config::pull(substr($name, 0, -1));
            }

            return 0 === strpos($name, '?') ? Config::has(substr($name, 1)) : Config::get($name);
        } else {
            return Config::set($name, $value);
        }
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @access public
     * @param  string    $name 环境变量名（支持二级 .号分割）
     * @param  string    $default  默认值
     * @return mixed
     */
    function env($name = null, $default = null)
    {
        return Env::get($name, $default);
    }
}
if (!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string|array  $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed         $value cookie值
     * @param mixed         $option 参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_null($name)) {
            // 清除
            Cookie::clear($value);
        } elseif ('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? Cookie::has(substr($name, 1), $option) : Cookie::get($name);
        } elseif (is_null($value)) {
            // 删除
            return Cookie::delete($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}
/**
 * 统一格式json输出
 */
function format_json($data, $code, $id)
{
    $arr = ['code' => $code, 'request_id' => $id];
    if ($code) {
        $arr['msg'] = $data;
    } else {
        $arr['msg'] = '';
        $arr['data'] = $data;
    }
    return json_encode($arr, JSON_UNESCAPED_UNICODE);
}