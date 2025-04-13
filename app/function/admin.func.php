<?php
/**
 * @Description: (操作按钮权限)
 * @param string $rule 权限节点
 * @param string $cationType 按钮样式
 * @param string $info 按钮文字
 * @param string|string $param 参数
 * @param string $color 颜色
 * @param string $size 大小
 * @param string $icon 图标
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function auth_action($rule, $cationType='a', $info='infos', $param='', $color='primary', $size='xs', $icon='edit'){
    $auth = new \expand\Auth();
    $result = $auth->check($rule, ADMINID);
    if( $result ){
        switch ($cationType) {
            case "a":
                $result = "<a class=\"btn btn-".$color." btn-".$size."\" href=\"".url($rule, $param)."\"><i class=\"fa-solid fa-".$icon."\"></i> ".$info."</a>";
                break;
            case "confirm":
                $result = "<a class=\"btn btn-".$color." btn-".$size." btn-confirm\" href=\"javascript:void(0);\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa-solid fa-".$icon."\"></i> ".$info."</a>";
                break;
            case "clean_cache":
                $result = "<li><a href=\"javacript:void(0);\" class=\"btn-confirm\" data-url=\"".url($rule)."\" data-id=\"".$param."\" data-title=\"".$info."\" ><i class=\"fa-solid fa-".$icon."\"></i> ".$info."</a></li>";
                break;
            case "submit":
                $result = "<button type=\"submit\" class=\"btn btn-".$color." btn-".$size." submits\" data-loading-text=\"&lt;i class='fa-solid fa-spinner fa-spin '&gt;&lt;/i&gt; ".$info."\">".$info."</button>";
                break;
        }
    }
    return $result;
}

/**
 * @Description: (权限节点)
 * @param string $module 模块
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function auth_rule_select($module){
    $authRuleModel = new \app\common\model\AuthRule();
    $list = $authRuleModel->treeList($module, 1);
    $option = ['0' => lang('auth_rule_top')];
    if (!empty($list)){
        foreach ($list as $v){
            if ($v['h_layer'] < 3){
                if ($v['h_layer'] > 1){
                    $lv = '　　├ ';
                }else{
                    $lv = '';
                }
                $option[$v['id']] = $lv.$v['title'];
            }
        }
    }
    return $option;
}

/**
 * @Description: todo(分配权限节点)
 * @param string $module 模块
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function auth_rule_check($module){
    $authRuleModel = new \app\common\model\AuthRule();
    $list = $authRuleModel->treeList($module, 1);
    $option = [];
    if (!empty($list)){
        foreach ($list as $v){
            $option[$v['id']] = [$v['level'], $v['title']];
        }
    }
    return $option;
}

/**
 * @Description: (权限组)
 * @param string $module 模块
 * @param boolean $please_select 是否请选择
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function auth_group($module, $please_select = false){
    $authGroupModel = new \app\common\model\AuthGroup();
    $list = $authGroupModel->where([['module', '=', $module], ['status', '=', 1]])->order('level ASC,id ASC')->select();
    if($please_select){
        $option[0] = lang('please_select');
    }else{
        $option = [];
    }
    foreach ($list as $v){
        $option[$v->id] = $v->title;
    }
    return $option;
}

/**
 * @Description: (列表table排序)
 * @param string $param 排序方式
 * @return string
 * @author 子青时节 <654108442@qq.com>
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
    return "<a class=\"fa-solid ".$faStr."\" href=\"".$url_path."\"></a>";
}

/**
 * @Description: (列表状态值切换)
 * @param int $id id
 * @param string $value 字段值
 * @param string $field 字段
 * @param string $action 编辑
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function list_status($id, $value, $field='status', $action='edit'){
    if ($value == 1){
        $str = "fa-check-circle text-green";
    }else{
        $str = "fa-times-circle text-red";
    }
    return "<a href=\"javascript:void(0);\" data-id=\"".$id."\" data-field=\"".$field."\" data-value=\"".$value."\" data-url=\"".url($action)."\" class=\"editimg fa-solid ".$str."\"></a>";
}

/**
 * @Description: (列表编辑文本)
 * @param array $data 列表数据
 * @param string $field 字段
 * @param string $action 编辑
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function list_write($data, $field, $action='edit'){
    return "<span class=\"list-write\" data-id=\"".$data['id']."\" data-field=\"".$field."\" data-url=\"".url($action)."\" >".$data[$field]."</span>";
}

/**
 * @Description: (渲染输出widget挂件)
 * @param string $name 挂件名称
 * @param array $data 数组数据
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function widget($name, $data = []){
    $info   = pathinfo($name);
    $action = $info['basename'];
    $class = 'app\\'.$info['dirname'];
    $class = str_replace('common/', 'common\widget\\', $class);
    return app()->invokeMethod([$class, $action], $data);
}

/**
 * @Description: (渲染输出widget挂件)
 * @param string $name 挂件名称
 * @param array $data 数组数据
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function level_text($level){
    $arr = config('selectlist.auth_level')['data'];
    return $arr[$level];
}

/**
 * @Description: (插件列表版本)
 * @param string $data 列表数据
 * @param array $local_addon_list 本地已安装插件
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function addon_version($data, $local_addon_list){
    $last_version = '';
    $version = '';
    $status_switchs = '';
    $action_btns = '';
    if(empty($data['jump_url'])){
        $last_version = $data['version_list'][0]['version'];
        if(isset($local_addon_list[$data['name']])){
            $version .= '<span class="position-relative">'.$local_addon_list[$data['name']]['version'];
            
            $checked = $local_addon_list[$data['name']]['status'] == 1 ? 'checked="checked"' : '';
            $status_switchs .= '<span class="checkbox-inline switch-box sm"><input type="checkbox" class="switchs_btn" id="switchs_addon'.$data['id'].'" data-id="'.$data['id'].'" data-field="'.$data['name'].'" data-url="'.url('Addons/statusToggle').'" '.$checked.' /><label for="switchs_addon'.$data['id'].'"><em></em></label></span>';
            
            if($local_addon_list[$data['name']]['version'] != $data['version_list'][0]['version']){
                $version .= '<span class="position-absolute p-1 bg-danger rounded-circle"></span>';
                
                $action_btns .= '<div class="btn-group">';
                $action_btns .= '<button type="button" class="btn btn-warning btn-xs btn-confirm" data-url="'.url('Addons/install').'" data-id=\''.json_encode(['name' => $data['name'], 'version' => $last_version]).'\' data-title="升级 - '.$data['title'].'-'.$last_version.'" ><i class="fa-solid fa-cloud-arrow-up"></i> 升级</button>';
                $action_btns .= '<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-bs-toggle="dropdown"></button>';
                $action_btns .= '<ul class="dropdown-menu">';
                foreach($data['version_list'] as $v){
                    $action_btns .= '<li><a class="dropdown-item btn-confirm" href="javascript:void(0);" data-url="'.url('Addons/install').'" data-id=\''.json_encode(['name' => $data['name'], 'version' => $v['version']]).'\' data-title="安装 - '.$data['title'].'-'.$v['version'].'" >'.$v['version'].'</a></li>';
                }
                $action_btns .= '</ul>';
                $action_btns .= '</div>';
            }
            $version .= '</span>';
            
            $action_btns .= ' <a class="btn btn-danger btn-xs btn-confirm" href="javascript:void(0);" data-url="'.url('Addons/uninstall').'" data-id="'.$data['name'].'" data-title="卸载 - '.$data['title'].'" data-message="<p class=\'text-danger\'>当前插件所有文件、数据将被删除，且无法恢复!!!</p><p class=\'text-danger\'>如有重要数据请先备份【数据库】后再操作!!!</p>" ><i class="fa-solid fa-trash"></i> 卸载</a>';
        }else{
            $version .= $data['version_list'][0]['version'];
            
            $action_btns .= '<div class="btn-group">';
            $action_btns .= '<button type="button" class="btn btn-primary btn-xs btn-confirm" data-url="'.url('Addons/install').'" data-id=\''.json_encode(['name' => $data['name'], 'version' => $last_version]).'\' data-title="安装 - '.$data['title'].'-'.$last_version.'" ><i class="fa-solid fa-cloud-arrow-down"></i> 安装</button>';
            if(count($data['version_list']) > 1){
                $action_btns .= '<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown"></button>';
                $action_btns .= '<ul class="dropdown-menu">';
                foreach($data['version_list'] as $v){
                    $action_btns .= '<li><a class="dropdown-item btn-confirm" href="javascript:void(0);" data-url="'.url('Addons/install').'" data-id=\''.json_encode(['name' => $data['name'], 'version' => $v['version']]).'\' data-title="安装 - '.$data['title'].'-'.$v['version'].'" >'.$v['version'].'</a></li>';
                }
                $action_btns .= '</ul>';
            }
            $action_btns .= '</div>';
        }
    }else{
        $last_version = '-';
        $version .= '-';
        $action_btns .= '<a class="btn btn-success btn-xs" target="_blank" href="'.$data['jump_url'].'"><i class="fa-solid fa-eye"></i> 点击查看</a>';
    }
    return [$last_version, $version, $status_switchs, $action_btns];
}