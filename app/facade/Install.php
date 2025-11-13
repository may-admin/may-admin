<?php
namespace app\facade;

use think\Facade;

class Install extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\install\controller\index';
    }
}