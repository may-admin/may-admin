<?php
namespace app\common\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Db;

class Admin extends BaseController
{
    protected $cModel;                  //当前控制器关联模型
    protected $modelClass = false;      //数据模型命名空间
    protected $searchConf = [];         //搜索功能配置
    protected $middleware = ['admin_load_lang_pack', 'admin_session', 'admin_auth'];
    
    public function initialize()
    {
        parent::initialize();
        if (class_exists($this->modelClass)){
            $this->cModel = new $this->modelClass;
        }
    }
    
    /**
     * @Description: (基础单表列表显示)
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
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
            $order = 'sorts desc,id desc';
        }
        $dataList = $this->cModel->where($where)->order($order)->paginate(page_param());
        
        session('redirect_url', request()->url());
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    /**
     * @Description: (基础单表新增)
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function create()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            $validate = 'app\common\validate\\'.CONTROLLER_NAME;
            $validate = new $validate;
            if (!$validate->scene('create')->check($data)) {
                return ajax_return(1, $validate->getError());
            }
            $result = $this->cModel->save(del_arr_empty($data));
            if ($result){
                $url = session('redirect_url') ? session('redirect_url') : url('index');
                return ajax_return(0, lang('action_success'), $url);
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }else{
            View::assign('data', []);
            return View::fetch('edit');
        }
    }
    
    /**
     * @Description: (基础单表编辑)
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function edit()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            $scene = 'edit';
            if (count($data) == 2){
                foreach ($data as $k =>$v){
                    $scene = $k!='id' ? $k : '';
                }
            }
            $validate = 'app\common\validate\\'.CONTROLLER_NAME;
            $validate = new $validate;
            if (!$validate->scene($scene)->check($data)) {
                return ajax_return(1, $validate->getError());
            }
            $result = $this->cModel->find($data['id']);
            foreach ($data as $k => $v){
                $result->$k = $v;
            }
            $result->save();
            if ($result){
                $url = session('redirect_url') ? session('redirect_url') : url('index');
                return ajax_return(0, lang('action_success'), $url);
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }else{
            $id = input('get.id');
            $data = $this->cModel->find($id);
            View::assign('data', $data);
            return View::fetch('edit');
        }
    }
    
    /**
     * @Description: (基础单表删除)
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function delete()
    {
        if (request()->isPost()){
            $id = input('id');
            if (isset($id) && !empty($id)){
                $id_arr = explode(',', $id);
                $where[] = ['id', 'in', $id_arr];
                $result = $this->cModel->where($where)->delete();
                if ($result){
                    $url = session('redirect_url') ? session('redirect_url') : url('index');
                    return ajax_return(0, lang('action_success'), $url);
                }else{
                    return ajax_return(1, lang('action_fail'));
                }
            }
        }
    }
    
    /**
     * @Description: (列表搜索功能)
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    final protected function _indexSearch()
    {
        $this->setLang();
        
        $col = ['title_col' => '3', 'content_col' => '9', 'validate_col' => '0'];
        $searchConf = [];
        foreach ($this->searchConf as $k => $v){
            if($v['type'] == 'ToTime'){
                $start_time = input('param.'.$v['widget_conf']['start_name']);
                $v['data'][$v['widget_conf']['start_name']] = $start_time;
                $end_time = input('param.'.$v['widget_conf']['end_name']);;
                $v['data'][$v['widget_conf']['end_name']] = $end_time;
            }else{
                $param = $v['widget_conf']['name'];
                $v['data'][$param] = input('param.'.str_replace('.', '_', $param));
                $v['widget_conf'] = array_merge($v['widget_conf'], $col);
            }
            $searchConf[] = $v;
        }
        $this->searchConf = $searchConf;
        View::assign('searchConf', $searchConf);
    }
    
    protected function setLang()
    {
    }
    
    /**
     * @Description: (列表搜索功能条件获取)
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    final protected function _getSearch()
    {
        $map = [];
        foreach ($this->searchConf as $v){
            if($v['type'] == 'ToTime'){
                $time = [];
                $keys = array_keys($v['data']);
                $last_key = end($keys);
                if(!empty($v['data'][$v['widget_conf']['start_name']]) || !empty($v['data'][$v['widget_conf']['end_name']])){
                    foreach ($v['data'] as $k2 => $v2){
                        if($k2 == $last_key){
                            $time[] = !empty($v2) ? strtotime($v2) + 86399 : time();
                        }else{
                            $time[] = !empty($v2) ? strtotime($v2) : 0;
                        }
                    }
                }
                if(!empty($time)){
                    $map[] = [$v['widget_conf']['name'], 'between', $time];
                }
            }else{
                foreach($v['data'] as $k2 => $v2){
                    if($v2 !== null && $v2 !== ''){
                        if($v['where'] == 'like'){
                            $map[] = [$k2, $v['where'], '%'.$v2.'%'];
                        }else{
                            $map[] = [$k2, $v['where'], $v2];
                        }
                    }
                }
            }
        }
        return $map;
    }
}