<?php
namespace app\common\taglib;

use think\template\TagLib;

class MayCms extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'may' => ['attr' => 'app,type,method,zdy', 'close' => 1, 'level' => 3],
    ];
    
    /**
     * @Description: (内容管理cms自定义标签)
     * @param string $tag[app] 模块[应用]
     * @param string $tag[type] 数据类型[class]
     * @param string $tag[method] 方法[function]
     * @param string $tag[return] 返回变量
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function tagMay($tag, $content)
    {
        $app = isset($tag['app']) && !empty($tag['app']) ? $tag['app'] : 'index';
        $type = isset($tag['type']) && !empty($tag['type']) ? $tag['type'] : 'Category';
        $method = isset($tag['method']) && !empty($tag['method']) ? $tag['method'] : 'lists';
        $return =isset($tag['return']) && !empty($tag['return']) ? $tag['return'] : 'data';
        $class = "app\\".$app."\\taglib\\".ucwords($type)."TagLib";
        $parse = '<?php ';
        $parse .= "$".$return." = app()->invokeMethod(['".$class."', '".$method."'], ['param'=>".self::arr_to_html($tag)."]);";
        $parse .= ' ?>';
        $parse .= $content;
        return $parse;
    }
    
    /**
     * @Description: (转换数据为HTML代码)
     * @param $data 数组
     * @return string
     */
    private static function arr_to_html($data)
    {
        if (is_array($data)) {
            $str = '[';
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $str .= "'$key'=>" . self::arr_to_html($val) . ",";
                } else {
                    if (is_int($val)) {
                        $str .= "'$key'=>$val,";
                    } else if (strpos($val, '$') === 0) {   // 变量处理
                        $str .= "'$key'=>$val,";
                    } else if (preg_match("/^([a-zA-Z_].*)\(/i", $val, $matches)) {   // 函数处理
                        if (function_exists($matches[1])) {
                            $str .= "'$key'=>$val,";
                        } else {
                            $str .= "'$key'=>'" . self::newAddslashes($val) . "',";
                        }
                    } else {
                        $str .= "'$key'=>'" . self::newAddslashes($val) . "',";
                    }
                }
            }
            $str = rtrim($str, ',');
            return $str . ']';
        }
        return false;
    }
    
    /**
     * @Description: (返回经addslashes处理过的字符串或数组)
     * @param $string 需要处理的字符串或数组
     * @return array|string
     */
    protected static function newAddslashes($string)
    {
        if (!is_array($string)) {
            return addslashes($string);
        }
        foreach ($string as $key => $val) {
            $string[$key] = self::newAddslashes($val);
        }
        return $string;
    }
}