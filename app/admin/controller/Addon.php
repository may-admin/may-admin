<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use think\facade\View;

class Addon extends Admins
{
    public function index()
    {
        $dataList = [];
        
        View::assign('dataList', $dataList);
        return View::fetch();
    }
}