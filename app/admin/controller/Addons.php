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
        
        if (class_exists('\app\common\model\Addon')){
            $params = [];
            isset($get_param['category']) ? $params['category'] = $get_param['category'] : '' ;
            if(!isset($get_param['type'])){
            }else if($get_param['type'] == 'free' || $get_param['type'] == 'price'){
                $params['type'] = $get_param['type'];
            }else if($get_param['type'] == 'install'){
                $addon_arr = array_keys($local_addon_list);
                $params['type'] = implode(',', $addon_arr);
            }
            $list = AddonService::sendRequest('/addon/Addon/index', $params);
            foreach($list['data'] as $k => $v){
                list($list['data'][$k]['version'], $list['data'][$k]['status_switchs'], $list['data'][$k]['action_btns']) = addon_version($v, $local_addon_list);
            }
        }else{
            $list = ['total' => 0, 'per_page' => 15, 'current_page' => 1, 'last_page' => 1, 'data' => []];
        }
        $option = [
            'path' => (string)url('Addons/index'),
            'query' => page_param()['query'],
        ];
        $list_rows = cache('list_rows') ? : 15;
        $dataList = new BootstrapAdmin($list['data'], $list_rows, $list['current_page'], $list['total'], $simple = false, $option = $option);
        
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
                $force = false;
                $info = AddonService::localInstall($file, $extend, $force);
            } catch (Exception $e) {
                return ajax_return(1, $e->getMessage());
            }
            return ajax_return(0, '安装成功', '', $info);
        }else{
            return ajax_return(1, '请选择文件');
        }
    }
    
    public function install()
    {
        
    }
    
    public function statusToggle()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            unset($data['id']);
            $addon = '';
            $val = '';
            foreach ($data as $k => $v){
                $addon = $k;
                $val = $v == 'true' ? 1 : 0;
            }
            $ini = AddonService::getAddonConfigIni($addon);
            $ini['status'] = $val;
            AddonService::setAddonConfigIni($addon, $ini);
        }
    }
}