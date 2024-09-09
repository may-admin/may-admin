<?php
namespace app\index\controller;

use app\common\controller\Home;
use think\facade\View;

class Index extends Home
{
    public function index()
    {
        echo "index-index";
        // View::assign('parent', ['id' => '0']);
        // return View::fetch();
    }
}