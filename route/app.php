<?php
use think\facade\Route;

// 内容管理cms路由
Route::rule('category/:dirs$', '\app\index\controller\Category@index');   //栏目
Route::rule(':mods/:id$', '\app\index\controller\Detail@index')->pattern(['id' => '\d+']);   //内容
