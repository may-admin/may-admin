<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use think\facade\View;

class AuthRule extends Admin
{
    protected $modelClass = '\app\common\model\AuthRule';
    
    public function index()
    {
        $dataList = $this->cModel->treeList('admin');
        View::assign('dataList', $dataList);
        return View::fetch();
    }
    
    public function create()
    {
        $this->cleanCache();
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            $validate = 'app\common\validate\\'.CONTROLLER_NAME;
            $validate = new $validate;
            if (!$validate->scene('create')->check($data)) {
                return ajax_return(1, $validate->getError());
            }
            $result = $this->cModel->save($data);
            if ($result){
                return ajax_return(0, lang('action_success'), url('index'));
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
        $this->cleanCache();
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
                return ajax_return(0, lang('action_success'), url('index'));
            }else{
                return ajax_return(1, lang('action_fail'));
            }
        }else{
            $id = input('get.id');
            $data = $this->cModel->find($id);
            View::assign('data', $data);
            View::assign('module', ['module' => 'admin']);
            return View::fetch('edit');
        }
    }
    
    public function delete()
    {
        $this->cleanCache();
        if (request()->isPost()){
            $id = input('id');
            if (isset($id) && !empty($id)){
                $id_arr = explode(',', $id);
                $where[] = ['id', 'in', $id_arr];
                $result = $this->cModel->where($where)->delete();
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
//         $dataList = $this->cModel->treeList('member');
//         View::assign('dataList', $dataList);
//         return View::fetch();
//     }
    
//     public function createMember()
//     {
//         $this->cleanCache();
//         if (request()->isPost()){
//             $data = input('post.', '', 'htmlspecialchars');
//             $validate = 'app\common\validate\\'.CONTROLLER_NAME;
//             $validate = new $validate;
//             if (!$validate->scene('create')->check($data)) {
//                 return ajax_return(1, $validate->getError());
//             }
//             $result = $this->cModel->save($data);
//             if ($result){
//                 return ajax_return(0, lang('action_success'), url('indexMember'));
//             }else{
//                 return ajax_return(1, lang('action_fail'));
//             }
//         }else{
//             View::assign('data', []);
//             View::assign('module', ['module' => 'member']);
//             return View::fetch('edit');
//         }
//     }
    
//     public function editMember()
//     {
//         $this->cleanCache();
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
//                     return ajax_return(1, $validate->getError());
//                 }
//                 $result = $this->cModel->where('id', $id)->save($data);
//             }else{
//                 $validate = 'app\common\validate\\'.CONTROLLER_NAME;
//                 $validate = new $validate;
//                 if (!$validate->scene('edit')->check($data)) {
//                     return ajax_return(1, $validate->getError());
//                 }
//                 $result = $this->cModel->where('id', $id)->save($data);
//             }
//             if ($result){
//                 return ajax_return(0, lang('action_success'), url('indexMember'));
//             }else{
//                 return ajax_return(1, lang('action_fail'));
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
//         $this->cleanCache();
//         if (request()->isPost()){
//             $id = input('id');
//             if (isset($id) && !empty($id)){
//                 $id_arr = explode(',', $id);
//                 $where[] = ['id', 'in', $id_arr];
//                 $result = $this->cModel->where($where)->delete();
//                 if ($result){
//                     return ajax_return(0, lang('action_success'), url('indexMember'));
//                 }else{
//                     return ajax_return(1, lang('action_fail'));
//                 }
//             }
//         }
//     }
    
    protected function cleanCache()
    {
        cache('DB_TREE_AUTHRULE_admin_', null);
        cache('DB_TREE_AUTHRULE_admin_1', null);
        cache('DB_TREE_AUTHRULE_member_', null);
        cache('DB_TREE_AUTHRULE_member_1', null);
    }
}