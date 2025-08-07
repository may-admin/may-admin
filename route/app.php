<?php
use think\facade\Route;

// 内容管理cms路由
Route::rule('category/:may_cms_dirs$', '\app\index\controller\Category@index');   //栏目
Route::rule(':may_cms_mods/:may_cms_id$', '\app\index\controller\Detail@index')->pattern(['may_cms_id' => '\d+']);   //内容
