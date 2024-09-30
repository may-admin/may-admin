<?php
namespace app\admin\controller;

use app\common\controller\Admin as Admins;
use think\facade\View;
use app\common\model\AuthGroupAccess;

class Admin extends Admins
{
    protected $modelClass = '\app\common\model\Admin';
    
    protected function setLang()
    {
        $this->searchConf = [
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'username', 'title' => lang('username')]],
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'name', 'title' => lang('name')]],
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'mobile', 'title' => lang('mobile')]],
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'email', 'title' => lang('email')]],
            ['type' => 'ToTime', 'where' => 'between', 'widget_conf' => ['name' => 'a.create_time', 'title' => lang('create_time'), 'start_name' => '_start_time', 'end_name' => '_end_time']]
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
            $order = 'id desc';
        }
        $dataList = $this->cModel
        ->alias('a')
        ->join('auth_group_access b', 'a.id = b.uid and b.module = \'admin\'', 'left')
        ->join('auth_group c', 'b.group_id = c.id', 'left')
        ->field('a.*,c.title')
        ->where($where)->order($order)->paginate(page_param());
        
        session('redirect_url', request()->url());
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    public function create()
    {
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            $validate = 'app\common\validate\\'.CONTROLLER_NAME;
            $validate = new $validate;
            if (!$validate->scene('create')->check($data)) {
                return ajax_return(1, $validate->getError());
            }
            $result = $this->cModel->save($data);
            if ($result){
                $url = session('redirect_url') ? session('redirect_url') : url('index');
                return ajax_return(0, lang('action_success'), $url);
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }else{
            View::assign('data', []);
            return View::fetch();
        }
    }
    
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
            
            if(count($data) == 2){
                if (!$validate->scene($scene)->check($data)) {
                    return ajax_return(1, $validate->getError());
                }
            }else{
                if(!empty($data['old_password']) || !empty($data['password']) || !empty($data['repassword'])){
                    $password = $this->cModel->where('id', $data['id'])->value('password');
                    if (md5($data['old_password']) != $password){
                        return ajax_return(1, lang('old_password_error'));
                    }
                    if (!$validate->scene('edit_password')->check($data)) {
                        return ajax_return(1, $validate->getError());
                    }
                    unset($data['old_password'], $data['repassword']);
                }else{
                    unset($data['old_password'], $data['password'], $data['repassword']);
                    if (!$validate->scene('edit')->check($data)) {
                        return ajax_return(1, $validate->getError());
                    }
                }
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
            return View::fetch();
        }
    }
    
    public function delete()
    {
        if (request()->isPost()){
            $id = input('id');
            if (isset($id) && !empty($id)){
                $id_arr = explode(',', $id);
                $where[] = ['id', 'in', $id_arr];
                $result = $this->cModel->where($where)->delete();
                
                $where2[] = [['uid', 'in', $id_arr], ['module', '=', 'admin']];
                $authGroupAccessModel = new AuthGroupAccess();
                $authGroupAccessModel->where($where2)->delete();
                if ($result){
                    $url = session('redirect_url') ? session('redirect_url') : url('index');
                    return ajax_return(0, lang('action_success'), $url);
                }else{
                    return ajax_return(1, lang('action_fail'));
                }
            }
        }
    }
    
    public function editSelf()
    {
        if (request()->isPost()){
            $data = input('post.');
            $data['id'] = ADMINID;
            $validate = 'app\common\validate\\'.CONTROLLER_NAME;
            $validate = new $validate;
            if (!empty($data['password']) || !empty($data['repassword'])){
                if (!$validate->scene('edit_password')->check($data)) {
                    return ajax_return(1, $validate->getError());
                }
                unset($data['repassword']);
            }else{
                unset($data['password'], $data['repassword']);
                if (!$validate->scene('edit')->check($data)) {
                    return ajax_return(1, $validate->getError());
                }
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
            $data = $this->cModel->find(ADMINID);
            View::assign('data', $data);
            return View::fetch();
        }
    }
    
    public function authGroup()
    {
        $authGroupAccessModel = new AuthGroupAccess;
        if (request()->isPost()){
            $uid = input('post.id');
            $group_id = input('post.group_id');
            $data = $authGroupAccessModel->where([['uid', '=', $uid], ['module', '=', 'admin']])->find();
            if (!empty($data)){
                $data->group_id = $group_id;
                $result = $data->save();
            }else{
                $result = $authGroupAccessModel->save(['uid' => $uid, 'group_id' => $group_id, 'module' => 'admin']);
            }
            if ($result){
                $url = session('redirect_url') ? session('redirect_url') : url('index');
                return ajax_return(0, lang('action_success'), $url);
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }else{
            $id = input('get.id');
            $group_id = $authGroupAccessModel->where([['uid', '=', $id], ['module', '=', 'admin']])->value('group_id'); //当前管理员已拥有角色
            $data = $this->cModel->find($id);
            $data['group_id'] = $group_id;
            View::assign('data', $data);
            return View::fetch();
        }
    }
}