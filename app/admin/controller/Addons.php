<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use app\facade\AddonService;
use think\facade\View;
use expand\BootstrapAdmin;

class Addons extends Admins
{
    
    public function index()
    {
        $get_param = input('get.');
        $params = [];
        foreach ($get_param as $k => $v){
            if(!empty($v)){
                $params[$k] = $v;
            }
        }
        
        $list = AddonService::sendRequest('/addon/Addon/index', $params);
        
        $option = [
            'path' => (string)url('Addons/index'),
            'query' => page_param()['query'],
        ];
        $list_rows = cache('list_rows') ? : 15;
        $dataList = new BootstrapAdmin($list['data'], $list_rows, $list['current_page'], $list['total'], $simple = false, $option = $option);
        
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    public function install()
    {
        
    }
}