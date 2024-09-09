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
        switch ($cationType) {
            case "a":
                $result = "<a class=\"btn btn-".$color." btn-".$size."\" href=\"".url($rule, $param)."\"><i class=\"fa fa-".$icon."\"></i> ".$info."</a>";
                break;
            case "confirm":
                $result = "<a class=\"btn btn-".$color." btn-".$size." btn-confirm\" href=\"javascript:void(0);\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa fa-".$icon."\"></i> ".$info."</a>";
                break;
            case "clean_cache":
                $result = "<li><a href=\"javacript:void(0);\" class=\"btn-confirm\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa fa-".$icon."\"></i> ".$info."</a></li>";
                break;
            case "submit":
                $result = "<button type=\"submit\" class=\"btn btn-".$color." btn-".$size." submits\" data-loading-text=\"&lt;i class='fa fa-spinner fa-spin '&gt;&lt;/i&gt; ".$info."\">".$info."</button>";
                break;
        }
    }
    return $result;
}

/**
 * @Description: todo(列表table排序)
 * @param string $param
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
 */
function table_sort($param){
    $url_path = request()->baseUrl();
    $faStr = 'fa-sort';
    $get = request()->param();
    $get = array_filter($get);
    if( isset($get['_pjax']) ){ unset($get['_pjax']); }
    if( isset($get['_sort']) ){   //判断是否存在排序字段
        $sortArr = explode(',', $get['_sort']);
        if ( $sortArr[0] == $param ){   //当前排序
            if ($sortArr[1] == 'asc'){
                $faStr = 'fa-sort-asc';
                $sort = 'desc';
            }elseif ($sortArr[1] == 'desc'){
                $faStr = 'fa-sort-desc';
                $sort = 'asc';
            }
            $get['_sort'] = $param.','.$sort;
        }else{   //非当前排序
            $get['_sort'] = $param.',asc';
        }
    }else{
        $get['_sort'] = $param.',asc';
    }
    $paramStr = [];
    foreach ($get as $k=>$v){
        $paramStr[] = $k.'='.$v;
    }
    $paramStrs = implode('&', $paramStr);
    $url_path = $url_path.'?'.$paramStrs;
    return "<a class=\"fa ".$faStr."\" href=\"".$url_path."\"></a>";
}

/**
 * @Description: todo(列表状态值切换)
 * @param int $id
 * @param string $value
 * @param string $field
 * @param string $action
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2019年5月11日
 * @throws
 */
function list_status($id, $value, $field='status', $action='edit'){
    if ($value == 1){
        $str = "fa-check-circle text-green";
    }else{
        $str = "fa-times-circle text-red";
    }
    return "<a href=\"javascript:void(0);\" data-id=\"".$id."\" data-field=\"".$field."\" data-value=\"".$value."\" data-url=\"".url($action)."\" class=\"editimg fa ".$str."\"></a>";
}

/**
 * @Description: todo(列表编辑文本)
 * @param array $data
 * @param string $field
 * @param string $action
 * @author 苏晓信 <654108442@qq.com>
 * @date 2019年5月17日
 * @throws
 */
function list_write($data, $field, $action='edit'){
    return "<span class=\"list-write\" data-id=\"".$data['id']."\" data-field=\"".$field."\" data-url=\"".url($action)."\" >".$data[$field]."</span>";
}

/**
 * @Description: todo(渲染输出widget挂件)
 * @param string $name
 * @param array $data
 * @author 苏晓信 <654108442@qq.com>
 * @date 2019年4月24日
 * @throws
 */
function widget($name, $data = []){
    $info   = pathinfo($name);
    $action = $info['basename'];
    $class = 'app\\'.$info['dirname'];
    $class = str_replace('common/', 'common\widget\\', $class);
    return app()->invokeMethod([$class, $action], $data);
}