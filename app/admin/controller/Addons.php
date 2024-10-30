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
        $list = AddonService::sendRequest('/addon/Addon/index', $get_param);
        
        $option = [
            'path' => (string)url('Addons/index'),
            'query' => page_param()['query'],
        ];
        $list_rows = cache('list_rows') ? : 15;
        $dataList = new BootstrapAdmin($list['data'], $list_rows, $list['current_page'], $list['total'], $simple = false, $option = $option);
        
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    // 本地安装
    public function localInstall()
    {
        //  /admin.php/addon/local
        $file = request()->file('file');
        
        if ($file){
            try {
                $extend = [];
                $force = false;
                
                $info = AddonService::localInstall($file, $extend, $force);
            } catch (Exception $e) {
                return ajax_return(1, $e->getMessage());
            }
            
            var_dump($info);
            
        }else{
            return ajax_return(1, '请选择文件');
        }
    }
    
    public function install()
    {
        
    }
}