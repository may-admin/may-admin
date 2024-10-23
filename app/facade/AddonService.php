<?php
namespace app\facade;

use think\Facade;

class AddonService extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\controller\AddonService';
    }
}