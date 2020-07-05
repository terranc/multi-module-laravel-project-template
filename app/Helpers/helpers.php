<?php

use Spatie\Url\Url;

if (!function_exists('full_url')) {
    function full_url($url) {
        if (strpos($url, 'http') !== 0) {
            return \Storage::disk(config('admin.upload.disk'))->url($url);
        }

        return $url;
    }
}
if (!function_exists('hash_generate')) {
    function hash_generate($length = 6) {
        $characters = str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length);

        return substr(str_shuffle($characters), 0, $length);
    }
}

if (!function_exists('all_imgs')) {
    function all_imgs($imgs, $name) {
        $data = [];
        if (isset($imgs[$name])) {
            if (is_string($imgs[$name])) {
                $data[] = ['img' => $imgs[$name]];
            } else {
                $data = $imgs[$name];
            }
        }

        $full_imgs = [];
        foreach ($data as $datum) {
            $full_imgs[] = full_url($datum['img']);
        }

        return $full_imgs;
    }
}

if (!function_exists('agent_base_path')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @return string
     */
    function agent_base_path($path = '') {
        $prefix = '/' . trim(config('agent.route.prefix'), '/');

        $prefix = ($prefix === '/') ? '' : $prefix;

        $path = trim($path, '/');

        if ($path === NULL || $path === '') {
            return $prefix ?: '/';
        }

        return $prefix . '/' . $path;
    }
}

if (!function_exists('agent_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     * @param mixed  $parameters
     * @param bool   $secure
     *
     * @return string
     */
    function agent_url($path = '', $parameters = [], $secure = NULL) {
        if (\Illuminate\Support\Facades\URL::isValidUrl($path)) {
            return $path;
        }

        $secure = $secure ?: (config('admin.https') || config('admin.secure'));

        return url(agent_base_path($path), $parameters, $secure);
    }
}

/**
 ** 下划线转驼峰
 ** 思路:
 ** step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
 ** step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
 *
 * @param        $un_camelize_words
 * @param string $separator
 *
 * @return string
 */
function camelize($un_camelize_words, $separator = '_') {
    $un_camelize_words = $separator . str_replace($separator, " ", strtolower($un_camelize_words));
    return ltrim(str_replace(" ", "", ucwords($un_camelize_words)), $separator);
}

/**
 ** 驼峰命名转下划线命名
 ** 思路:
 ** 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
 *
 * @param        $camel_caps
 * @param string $separator
 *
 * @return string
 */
function un_camelize($camel_caps, $separator = '_') {
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camel_caps));
}

function arr2str($arr, $glue = ',') {
    return implode($glue, $arr);
}

function str2arr($str, $glue = ',') {
    return explode($glue, $str);
}

function html_encode($str) {
    return htmlspecialchars($str);
}

function html_decode($str) {
    return htmlspecialchars_decode($str);
}

if (!function_exists('sql_debug')) {
    function sql_debug() {
        // AppServiceProvider 配置文件夹 local 默认开启
        \DB::enableQueryLog();
    }
}
if (!function_exists('getSql')) {
    function getSql() {
        //若没有开启 sql_debug 则需要手动调用
        return \DB::getQueryLog();
    }
}


if (!function_exists('array_change_key_case_recursive')) {
    function array_change_key_case_recursive($arr) {
        return array_map(function($item) {
            if (is_array($item)) {
                $item = array_change_key_case_recursive($item);
            }
            return $item;
        }, array_change_key_case($arr));
    }
}

function array_change_key_camel_recursive($arr) {
    return array_map(function($item) {
        if (is_array($item)) {
            $item = array_change_key_camel_recursive($item);
        }
        return $item;
    }, camel_case($arr));
}

/**
 *
 * +--------------------------------------------------------------------
 * Description 递归创建目录
 * +--------------------------------------------------------------------
 *
 * @param string $dir  需要创新的目录
 *                     +--------------------------------------------------------------------
 *
 * @return 若目录存在,或创建成功则返回为TRUE
 * +--------------------------------------------------------------------
 * @author gongwen
 * +--------------------------------------------------------------------
 */

function mkdirs($dir, $mode = 0777) {
    if (is_dir($dir) || mkdir($dir, $mode)) {
        return true;
    }
    if (!mkdirs(dirname($dir), $mode)) {
        return false;
    }
    return mkdir($dir, $mode);
}


//生成16位md5
function md5_16($str) {
    return substr(md5($str), 8, 16);
}

/**
 * 在数据列表中搜索（支持多维数组）
 *
 * @access public
 *
 * @param array $list      数据列表
 * @param mixed $condition 查询条件
 *                         支持 array('name'=>$value) 或者 name=$value
 *
 * @return array
 */
function array_where_recursive($list, $condition) {
    if (is_string($condition)) {
        parse_str($condition, $condition);
    }
    // 返回的结果集合
    $resultSet = [];
    foreach ($list as $key => $data) {
        $find = false;
        foreach ($condition as $field => $value) {
            if (isset($data[$field])) {
                if (0 === strpos($value, '/')) {
                    $find = preg_match($value, $data[$field]);
                } else if ($data[$field] == $value) {
                    $find = true;
                }
            }
        }
        if ($find) {
            $resultSet[] =   &$list[$key];
        }
    }
    return $resultSet;
}

/**
 * Array 转 Object
 */
function array2object($arr) {
    return json_decode(json_encode($arr));
}

// 获取毫秒
function get_microtime() {
    return (int)(microtime(true) * 1000);
}

// 格式化金钱
function money_formatter($fee, $float = false) {
    return number_format($float ? $fee : bcdiv($fee, 100), 2, '.', '');
}

function http_build_url($url_arr) {
    $new_url = $url_arr['scheme'] . "://" . $url_arr['host'];
    if (!empty($url_arr['port'])) {
        $new_url = $new_url . ":" . $url_arr['port'];
    }
    if (!empty($url_arr['path'])) {
        $new_url = $new_url . $url_arr['path'];
    }
    if (!empty($url_arr['query'])) {
        $new_url = $new_url . "?" . $url_arr['query'];
    }
    if (!empty($url_arr['fragment'])) {
        $new_url = $new_url . "#" . $url_arr['fragment'];
    }
    return $new_url;
}


if (!function_exists('fmt')) {
    function fmt($format, ...$args) {
        if (count($args) > 0) {
            $format = preg_replace("/\{[^\{]+\}/", '%s', $format);
            return sprintf($format, ...$args);
        } else {
            return $format;
        }
    }
}
if (!function_exists('url_set_params')) {
    function url_set_params(string $url, array $params): string {
        $sRes = $url;
        foreach ($params as $key => $value) {
            $redirect_url_parsed = Url::fromString($sRes);
            $sRes = $redirect_url_parsed->withQueryParameter($key, urlencode($value));
        }
        return $sRes;
    }
}
if (!function_exists('http_build_url')) {
    function http_build_url($url_arr) {
        $new_url = $url_arr['scheme'] . "://" . $url_arr['host'];
        if (!empty($url_arr['port'])) {
            $new_url = $new_url . ":" . $url_arr['port'];
        }
        $new_url = $new_url . $url_arr['path'];
        if (!empty($url_arr['query'])) {
            $new_url = $new_url . "?" . $url_arr['query'];
        }
        if (!empty($url_arr['fragment'])) {
            $new_url = $new_url . "#" . $url_arr['fragment'];
        }
        return $new_url;
    }
}
if (!function_exists('curl_request')) {
    function curl_request($url, $post = '', $cookie = '', $returnCookie = 0) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            [$header, $body] = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }
}

// 无重叠区间检测
if (!function_exists('check_overlap_intervals')) {
    function check_overlap_intervals($intervals = []) {
        $len = count($intervals);               //计算区间总数
        // 初始化判断区间是否为空
        if ($len == 0) {
            return 0;
        }
        // 对区间进行排序，以终止点进行排序
        usort($intervals, function($i, $j) {
            // 终止点相同则，起始点小的靠前
            if ($i[1] === $j[1]) {
                return $i[0] > $j[0];
            }
            //否则终止点小的靠前
            return $i[1] > $j[1];
        });
        $res = 1;                               //初始化最终结果
        $pre = 0;                               //记录前一个区间的下标
        for ($i = 1; $i < $len; ++$i) {                 //遍历整个数组
            //如果当前的区间起始小于前一个区间的结尾
            if ($intervals[$i][0] > $intervals[$pre][1]) {
                $res++;                         //找到一个新的不重叠区间
                $pre = $i;                      //标记该点的为新的前一个下标点，因为该点的结尾已经排序为最小的终止点
            }
        }
        return $len - $res;                     //题目求的是剔除的区间数，减法即可
    }
}

// UUID
if (!function_exists('uuid')) {
    function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
}

// 隐藏手机号码中间四位
function privacy_phone($phone = '') {
    return substr_replace($phone, '****', 3, 4);
}
