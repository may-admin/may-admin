<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use app\facade\AddonService;
use think\facade\View;
use expand\BootstrapAdmin;
use think\Exception;

class Addons extends Admins
{
    public function index()
    {
        $get_param = del_arr_empty(input('get.'));
        
        $local_addon_list = AddonService::getListAddonConfigIni();
        
        $params = [];
        isset($get_param['category']) ? $params['category'] = $get_param['category'] : '' ;   //插件类别
        if(!isset($get_param['type'])){   //免费、付费、已安装
        }else if($get_param['type'] == 'free' || $get_param['type'] == 'price'){
            $params['type'] = $get_param['type'];
        }else if($get_param['type'] == 'install'){
            $addon_arr = array_keys($local_addon_list);
            $params['type'] = implode(',', $addon_arr);
        }
        if(isset($get_param['page']) && intval($get_param['page']) > 0){   //当前页
            $params['page'] = intval($get_param['page']);
        }else{
            $params['page'] = 1;
        }
        if(isset($get_param['list_rows']) && intval($get_param['list_rows']) > 0){   //每页数量
            $params['list_rows'] = intval($get_param['list_rows']);
            cache('list_rows', $params['list_rows']);
        }else{
            $params['list_rows'] = cache('list_rows') ? : 15;
        }
        
        $list = AddonService::sendRequest('/addon/Addon/index', $params);
        if(empty($list)){
            $list = ['total' => 0, 'per_page' => $params['list_rows'], 'current_page' => $params['page'], 'last_page' => 1, 'data' => [], 'category' => [], 'type' => []];
        }
        foreach($list['data'] as $k => $v){
            list($list['data'][$k]['version'], $list['data'][$k]['status_switchs'], $list['data'][$k]['action_btns']) = addon_version($v, $local_addon_list);
        }
        
        $option = [
            'path' => (string)url('Addons/index'),
            'query' => page_param()['query'],
        ];
        $dataList = new BootstrapAdmin($list['data'], $list['per_page'], $list['current_page'], $list['total'], $simple = false, $option = $option);
        
        $category_arr = [];
        foreach($list['category'] as $k => $v){
            $get_param1 = $get_param;
            $active = !empty($k) && isset($get_param1['category']) && $get_param1['category'] == $k || empty($k) && !isset($get_param1['category']) ? 'active' : '';
            
            $get_param1['category'] = $k;
            $category_arr[] = [
                'text' => $v,
                'url' => (string)url('Addons/index', $get_param1),
                'active' => $active,
            ];
        }
        
        $type_arr = [];
        foreach($list['type'] as $k2 => $v2){
            $get_param2 = $get_param;
            $active = (!empty($k2) && isset($get_param2['type']) && $get_param2['type'] == $k2) || (empty($k2) && !isset($get_param2['type'])) ? 'active' : '';
            
            $get_param2['type'] = $k2;
            $type_arr[] = [
                'text' => $v2,
                'url' => (string)url('Addons/index', $get_param2),
                'active' => $active,
            ];
        }
        
        session('redirect_url', request()->url());
        View::assign('dataList', $dataList);
        View::assign('category_arr', $category_arr);
        View::assign('type_arr', $type_arr);
        return View::fetch();
    }
    
    /**
     * @Description: (本地上传安装插件)
     * @param object $addon_upload_file 上传文件
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function localInstall()
    {
        $file = request()->file('addon_upload_file');
        if ($file){
            try {
                $extend = [];
                $info = AddonService::localInstall($file, $extend);
            } catch (Exception $e) {
                return ajax_return(1, $e->getMessage());
            }
            
            $url = session('redirect_url') ? session('redirect_url') : url('index');
            return ajax_return(0, lang('action_success'), $url, $info);
        }else{
            return ajax_return(1, '请选择文件');
        }
    }
    
    public function install()
    {
        
    }
    
    public function uninstall()
    {
        if (request()->isPost()){
            $name = input('post.id', '', 'htmlspecialchars');
            try {
                AddonService::uninstall($name);
            } catch (Exception $e) {
                $code = substr($e->getMessage(), 0, 5);
                if($code == 'code2'){
                    $up = explode(',', $e->getMessage())[1];
                    $url = session('redirect_url') ? session('redirect_url') : url('index');
                    return ajax_return(2, '存在版本差异文件，插件已备份:<br />'.$up.'<br /><a href="'.$up.'" class="text-danger" target="_blank" >点击下载</a>', $url);
                }else{
                    return ajax_return(1, $e->getMessage());
                }
            }
            
            $url = session('redirect_url') ? session('redirect_url') : url('index');
            return ajax_return(0, lang('action_success'), $url);
        }
    }
    
    public function statusToggle()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            unset($data['id']);
            $name = '';
            $val = '';
            foreach ($data as $k => $v){
                $name = $k;
                $val = $v;
            }
            try {
                if($val == 'true'){   // 启用插件
                    AddonService::enable($name);
                }elseif($val == 'false'){   // 禁用插件
                    AddonService::disable($name);
                }
            } catch (Exception $e) {
                return ajax_return(1, $e->getMessage());
            }
            return ajax_return(0, lang('action_success'), '');
        }
    }
}