<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\model\AuthRule;
use expand\Auth;
use think\facade\View;

class Config extends Admin
{
    protected $modelClass = '\app\common\model\Config';
    
    protected function setLang()
    {
        $this->searchConf = [
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'k', 'title' => lang('k')]],
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'v', 'title' => lang('v')]],
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'infos', 'title' => lang('infos')]],
            ['type' => 'Input', 'where' => '=', 'widget_conf' => ['name' => 'type', 'title' => lang('type')]],
        ];
    }
    
    public function index()
    {
        $this->_indexSearch();
        
        $where = [];
        $getSearch = $this->_getSearch();
        if (!empty($getSearch)) $where[] = $getSearch;
        
        if (input('get._sort')){
            $order = explode(',', input('get._sort'));
            $order = $order[0].' '.$order[1];
        }else{
            $order = 'type asc,status desc,sorts desc,id desc';
        }
        $dataList = $this->cModel->where($where)->order($order)->paginate(page_param());
        
        session('redirect_url', request()->url());
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    public function sysMenu()
    {
        $sys_menu_id = AuthRule::where([['name', '=', 'Config/sysMenu']])->value('id');
        
        $dataList = AuthRule::field('id,name,title')->where([['pid', '=', $sys_menu_id], ['status', '=', 1]])->order('sorts desc')->select();
        
        $auth = new Auth();
        foreach ($dataList as $k => $v){
            if ($auth->check($v['name'], ADMINID) ){
                $type = explode('/', $v['name']);
                $v->types = $type[1];
                $config_lists = $this->cModel->where([['type', '=', $type[1]], ['status', '=', 1]])->order('sorts desc,id desc')->select();
                foreach($config_lists as $v2){
                    $v2[$v2->k] = $v2->v;
                }
                $v->config_lists = $config_lists;
            }else{
                unset($dataList[$k]);
            }
        }
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    public function save()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            $type = $data['types'];  //取出类型
            unset($data['types']);
            if(!empty($type)){
                if(is_array($data) && !empty($data)){
                    foreach ($data as $k=>$val) {
                        if (is_array($val)){
                            $val = implode(',', del_arr_empty($val));
                        }
                        $where = ['type' => $type, 'k' => $k];
                        $this->cModel->where($where)->save(['v' => $val]);
                    }
                    $this->dbconfig();
                    return ajax_return(0, lang('action_success'), '');
                }else{
                    return ajax_return(1, lang('action_fail'));
                }
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }
    }
    
    private function dbconfig()
    {
        $dbconfig_path = app()->getConfigPath().'dbconfig.php';
        $data = $this->cModel->field('k, v, type')->where([['status', '=', 1]])->order('type asc,sorts desc')->select();
        $type = "";
        $close = false;
        $str = "<?php\r\nreturn [";
        if (!empty($data)){
            foreach ($data as $k => $v){
                if (isset($v['v'][0]) && $v['v'][0] == '['){
                    $v['v'] = htmlspecialchars_decode($v['v']);
                }else{
                    $v['v'] = "\"".$v['v']."\"";
                }
                if ($type == $v['type']){
                    $str .= "\r\n        \"".$v['k']."\" => ".$v['v'].",";
                }else{
                    $type = $v['type'];
                    $str .= $close ? "\r\n    ]," : "";
                    $close = true;
                    $str .= "\r\n    \"".$v['type']."\" => [";
                    $str .= "\r\n        \"".$v['k']."\" => ".$v['v'].",";
                }
            }
        }
        $str .= "\r\n    ],\r\n];\r\n";
        if (!file_put_contents($dbconfig_path, $str)) {
            ajax_return(1, lang('dbconfig_error'));
        }
    }
}