<?php
namespace app\admin\controller;

use think\facade\View;

class Admin
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