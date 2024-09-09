<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use think\facade\View;
use app\common\model\AuthGroupAccess;

class AuthGroup extends Admin
{
    protected $modelClass = '\app\common\model\AuthGroup';
    
    protected function setLang()
    {
        $this->searchConf = [
            ['type' => 'Input', 'where' => 'like', 'widget_conf' => ['name' => 'title', 'title' => lang('title')]],
        ];
    }
    
    public function index()
    {
        $this->_indexSearch();
        
        $where[] = ['module', '=', 'admin'];
        $getSearch = $this->_getSearch();
        if (!empty($getSearch)) $where[] = $getSearch;
        
        if (input('get._sort')){
            $order = explode(',', input('get._sort'));
            $order = $order[0].' '.$order[1];
        }else{
            $order = 'level asc,id desc';
        }
        $dataList = $this->cModel->where($where)->order($order)->paginate(page_param());
        
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
            View::assign('module', ['module' => 'admin']);
            return View::fetch('edit');
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
            View::assign('module', ['module' => 'admin']);
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
                
                $where2[] = ['group_id', 'in', $id_arr];
                $authGroupAccessModel = new AuthGroupAccess();
                $authGroupAccessModel->where($where2)->delete();
                if ($result){
                    return ajax_return(0, lang('action_success'), url('index'));
                }else{
                    return ajax_return(1, lang('action_fail'));
                }
            }
        }
    }
    
//     public function indexMember()
//     {
//         $this->_indexSearch();
        
//         $where[] = ['module', '=', 'member'];
//         $getSearch = $this->_getSearch();
//         if (!empty($getSearch)) $where[] = $getSearch;
        
//         if (input('get._sort')){
//             $order = explode(',', input('get._sort'));
//             $order = $order[0].' '.$order[1];
//         }else{
//             $order = 'level asc,id asc';
//         }
//         $dataList = $this->cModel->where($where)->order($order)->paginate(page_param());
        
//         View::assign('dataList', $dataList);
//         return View::fetch();
//     }
    
//     public function createMember()
//     {
//         if (request()->isPost()){
//             $data = input('post.', '', 'htmlspecialchars');
//             $validate = 'app\common\validate\\'.CONTROLLER_NAME;
//             $validate = new $validate;
//             if (!$validate->scene('create')->check($data)) {
//                 return ajax_return($validate->getError());
//             }
//             $result = $this->cModel->save($data);
//             if ($result){
//                 return ajax_return(lang('action_success'), url('indexMember'));
//             }else{
//                 return ajax_return(lang('action_fail'));
//             }
//         }else{
//             View::assign('data', []);
//             View::assign('module', ['module' => 'member']);
//             return View::fetch('edit');
//         }
//     }
    
//     public function editMember()
//     {
//         if (request()->isPost()){
//             $data = input('post.', '', 'htmlspecialchars');
//             $id = $data['id'];
//             if (count($data) == 2){
//                 foreach ($data as $k =>$v){
//                     $fv = $k!='id' ? $k : '';
//                 }
//                 $validate = 'app\common\validate\\'.CONTROLLER_NAME;
//                 $validate = new $validate;
//                 if (!$validate->scene($fv)->check($data)) {
//                     return ajax_return($validate->getError());
//                 }
//                 $result = $this->cModel->where('id', $id)->save($data);
//             }else{
//                 $validate = 'app\common\validate\\'.CONTROLLER_NAME;
//                 $validate = new $validate;
//                 if (!$validate->scene('edit')->check($data)) {
//                     return ajax_return($validate->getError());
//                 }
//                 $data['rules'] = $this->cModel->setRulesAttr($data['rules'], $data);
//                 $result = $this->cModel->where('id', $id)->save($data);
//             }
//             if ($result){
//                 return ajax_return(lang('action_success'), url('indexMember'));
//             }else{
//                 return ajax_return(lang('action_fail'));
//             }
//         }else{
//             $id = input('get.id');
//             $data = $this->cModel->find($id);
//             View::assign('data', $data);
//             View::assign('module', ['module' => 'member']);
//             return View::fetch('edit');
//         }
//     }
    
//     public function deleteMember()
//     {
//         if (request()->isPost()){
//             $id = input('id');
//             if (isset($id) && !empty($id)){
//                 $id_arr = explode(',', $id);
//                 $where[] = ['id', 'in', $id_arr];
//                 $result = $this->cModel->where($where)->delete();
                
//                 $where2[] = ['group_id', 'in', $id_arr];
//                 $authGroupAccessModel = new AuthGroupAccess();
//                 $authGroupAccessModel->where($where2)->delete();
//                 if ($result){
//                     return ajax_return(lang('action_success'), url('indexMember'));
//                 }else{
//                     return ajax_return(lang('action_fail'));
//                 }
//             }
//         }
//     }
}