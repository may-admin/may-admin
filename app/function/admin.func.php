<?php
/**
 * @Description: todo(操作按钮权限)
 * @param string $rule 权限节点
 * @param string $cationType 按钮样式
 * @param string $info 按钮文字
 * @param array || string  $param 参数
 * @param string $color 颜色
 * @param string $size 大小
 * @param string $icon 图标
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
 */
function auth_action($rule, $cationType='a', $info='infos', $param='', $color='primary', $size='xs', $icon='edit'){
    $auth = new \expand\Auth();
    $result = $auth->check($rule, ADMINID);
    if( $result ){
        $result = action_type($rule, $cationType, $info, $param, $color, $size, $icon);
    }
    return $result;
}

/**
 * @Description: todo(操作按钮样式)
 * @param string $rule
 * @param string $cationType
 * @param string $info
 * @param array || string  $param
 * @param string $color
 * @param string $size
 * @param string $icon
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2019年5月8日
 * @throws
 */
function action_type($rule, $cationType, $info, $param, $color, $size, $icon){
    switch ($cationType) {
        case "a":
            $res = "<a class=\"btn btn-".$color." btn-".$size."\" href=\"".url($rule, $param)."\"><i class=\"fa fa-".$icon."\"></i> ".$info."</a>";
            break;
        case "confirm":
            $res = "<a class=\"btn btn-".$color." btn-".$size." btn-confirm\" href=\"javascript:void(0);\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa fa-".$icon."\"></i> ".$info."</a>";
            break;
        case "clean_cache":
            $res = "<li><a href=\"javacript:void(0);\" class=\"btn-confirm\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa fa-".$icon."\"></i> ".$info."</a></li>";
            break;
        case "submit":
            $res = "<button type=\"submit\" class=\"btn btn-".$color." btn-".$size." submits\" data-loading-text=\"&lt;i class='fa fa-spinner fa-spin '&gt;&lt;/i&gt; ".$info."\">".$info."</button>";
            break;
    }
    return $res;
}