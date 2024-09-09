<?php
// 引入公共函数文件
$func_path = glob(__DIR__.'/function/'.'*.func.php');
foreach ($func_path as $v){
    include $v;
}