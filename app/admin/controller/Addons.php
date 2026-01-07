<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use app\facade\AddonService;
use think\facade\View;
use expand\BootstrapAdmin;
use think\Exception;

class Addons extends Admins
{
    /**
     * @Description: (获取插件列表)
     * @param string $local 扩展参数
     * @param string $category 插件类别
     * @param string $type 免费/收费
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function index()
    {
        $get_param = del_arr_empty(input('get.'));
        
        if(isset($get_param['local']) && $get_param['local'] == 'lists'){
            $local_addon_list = AddonService::getListAddonConfigIni();
            $dataList = [];
            foreach($local_addon_list as $k => $v){
                $dataList[] = [
                    'id' => $k,
                    'name' => $v['name'],
                    'title' => $v['title'],
                    'author' => $v['author'],
                    'infos' => $v['infos'],
                    'price' => '-',
                    'downloads' => '-',
                    'version_list' => [
                        ['version' => $v['version']]
                    ],
                ];
            }
            foreach($dataList as $k => $v){
                list($dataList[$k]['last_version'], $dataList[$k]['version'], $dataList[$k]['status_switchs'], $dataList[$k]['action_btns']) = addon_version($v, $local_addon_list);
            }
            $category_arr[] = ['text' => '全部', 'url' => (string)url('Addons/index', ['category' => '']), 'active' => 'active'];
            $type_arr[] = ['text' => '全部', 'url' => (string)url('Addons/index', ['type' => '']), 'active' => 'active'];
        }else{
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
                cache('list_rows_'.ADMINID, $params['list_rows']);
            }else{
                $params['list_rows'] = cache('list_rows_'.ADMINID) ? : 15;
            }
            
            $list = AddonService::sendRequest('/addon/Addon/index', $params);
            if(empty($list)){
                $list = ['total' => 0, 'per_page' => $params['list_rows'], 'current_page' => $params['page'], 'last_page' => 1, 'data' => [], 'category' => [], 'type' => []];
            }
            foreach($list['data'] as $k => $v){
                list($list['data'][$k]['last_version'], $list['data'][$k]['version'], $list['data'][$k]['status_switchs'], $list['data'][$k]['action_btns']) = addon_version($v, $local_addon_list);
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
    
    /**
     * @Description: (远程下载安装插件)
     * @param int $id 插件id
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function install()
    {
        if (request()->isPost()){
            $data = input('post.id', '', 'htmlspecialchars');
            $addon_member_info = session('addon_member_info');
            $data['token'] = !empty($addon_member_info['token']) ? $addon_member_info['token'] : '';
            $data['buy'] =  input('post.buy', '', 'htmlspecialchars') == '1' ? 1 : 0;
            try {
                AddonService::download($data['name'], $data);
            } catch (Exception $e) {
                if (substr($e->getMessage(), 0, 1) === '{') {
                    $res = json_decode($e->getMessage(), true);
                    $code = isset($res['code']) ? $res['code'] : 1;
                    $message = isset($res['message']) ? $res['message'] : '';
                    $url = isset($res['url']) ? $res['url'] : '';
                    $data = isset($res['data']) ? $res['data'] : [];
                    return ajax_return($code, $message, $url, $data);
                }else{
                    return ajax_return(1, $e->getMessage());
                }
            }
            
            $url = session('redirect_url') ? session('redirect_url') : url('index');
            return ajax_return(0, lang('action_success'), $url);
        }
    }
    
    /**
     * @Description: (卸载插件)
     * @param int $id 插件id
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
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
    
    /**
     * @Description: (启用禁用插件)
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
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
    
    /**
     * @Description: (插件用户登录)
     * @param string $username 登录用户名
     * @param string $password 密码
     * @return string|array
     * @author 子青时节 <654108442@qq.com>
     */
    public function memberInfo()
    {
        if(request()->isPost()){
            $username = input('post.username', '', 'htmlspecialchars');
            $password = input('post.password', '', 'htmlspecialchars');
            
            if(empty($username) || empty($password)){
                return ajax_return(1, '登录账户或密码不能为空');
            }
            $params = [
                'username' => $username,
                'password' => $password,
            ];
            $res = AddonService::sendRequest('/addon/Addon/memberLogin', $params);
            if($res['code'] == '0'){
                session('addon_member_info', $res['data']);
            }
            return json($res);
        }else{
            $addon_member_info = session('addon_member_info');
            if(!empty($addon_member_info)){
                View::assign('addon_member_info', $addon_member_info);
                return View::fetch();
            }else{
                return View::fetch('memberLogin');
            }
        }
    }
    
    /**
     * @Description: (插件用户退出登录)
     * @author 子青时节 <654108442@qq.com>
     */
    public function memberLoginOut()
    {
        if(request()->isPost()){
            session('addon_member_info', null);
            return ajax_return(0, '退出成功');
        }
    }
}