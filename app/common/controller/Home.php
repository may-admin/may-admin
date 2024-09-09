<?php
namespace app\common\controller;

use app\BaseController;
use app\common\model\SitiLang;
use think\facade\View;

class Home extends BaseController
{
    protected $middleware = ['home_load_lang_pack'];
    
    public function initialize()
    {
        parent::initialize();
        define('ISPJAX', request()->isPjax());
        define('ISMOBILE', request()->isMobile());
        define('CONTROLLER_NAME', request()->controller());
        define('ACTION_NAME', request()->action());
        $this->webBaseData();
    }
    
    private function webBaseData()
    {
        $lang = input('param.'.config('lang.detect_var'));
        
        $sitiLangModel = new SitiLang();
        $lang_default = $sitiLangModel->where([['defaults', '=', 1]])->find();
        $where = [['status', '=', 1]];
        if(!empty($lang)){
            $where[] = ['lang', '=', $lang];
        }else{
            $where[] = ['defaults', '=', 1];
        }
        $sitiLangData = $sitiLangModel->where($where)->find();
        if(empty($sitiLangData) && $lang == 'hk'){
            $sitiLangData = $sitiLangModel->where([['lang', '=', 'zh-cn']])->find();
            $sitiLangData->name = '繁體';
            $sitiLangData->lang = 'hk';
        }elseif(empty($sitiLangData) && $lang != 'hk'){
            $sitiLangData = $lang_default;
        }
        if($lang == 'hk'){
            define('LANGS_DEFAULT', 0);
        }else{
            define('LANGS_DEFAULT', $sitiLangData['defaults']);
        }
        define('LANGS_ID', $sitiLangData['id']);
        define('LANGS_NAME', $sitiLangData['name']);
        define('LANGS_LANG', $sitiLangData['lang']);
        View::assign('webBaseData', $sitiLangData);
    }
}