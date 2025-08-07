<?php
namespace app\common\controller;

use app\BaseController;

class Index extends BaseController
{
    protected $middleware = ['index_load_lang_pack'];
    
    public function initialize()
    {
        parent::initialize();
        define('ISPJAX', request()->isPjax());
        define('ISMOBILE', request()->isMobile());
        define('CONTROLLER_NAME', request()->controller());
        define('ACTION_NAME', request()->action());
    }
}