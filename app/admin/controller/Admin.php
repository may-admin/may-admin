<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use think\facade\View;

class Admin extends Admins
{
    public function index()
    {
        $data = [
            'html' => '您好！这是一个admin',
        ];
        View::assign('data', $data);
        return View::fetch();
    }
}