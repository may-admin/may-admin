<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use think\facade\View;

class UploadFile extends Admin
{
    protected $modelClass = '\app\common\model\UploadFile';
    
    public function delete()
    {
        if (request()->isPost()){
            $id = input('id');
            if (isset($id) && !empty($id)){
                $id_arr = explode(',', $id);
                $where[] = ['id', 'in', $id_arr];
                $data = $this->cModel->where($where)->select();
                $public_path = public_path();
                $result = false;
                foreach($data as $v){
                    if (file_exists($public_path.$v['url'])) {
                        unlink($public_path.$v['url']);
                    }
                    $result = $v->delete();
                }
                if ($result){
                    $url = session('redirect_url') ? session('redirect_url') : url('index');
                    return ajax_return(0, lang('action_success'), $url);
                }else{
                    return ajax_return(1, lang('action_fail'));
                }
            }
        }
    }
}