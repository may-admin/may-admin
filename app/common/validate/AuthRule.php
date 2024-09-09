<?php
namespace app\common\validate;

use think\Validate;

class AuthRule extends Validate
{
    protected $rule = [
        'pid' => 'require|integer|different:id',
        'title' => 'require',
        'name' => 'require|checkAuthRule|/^[a-zA-Z0-9\/\-\_]+$/',
        'level' => 'require|in:1,2,3',
        'status' => 'require|in:0,1',
        'ismenu' => 'require|in:0,1',
        'sorts' => 'require|integer|>=:1',
    ];

    protected $message = [
        'pid' => 'pid_require',
        'pid.different' => 'pid_different',
        'title' => 'title_require',
        'name' => 'name_require',
        'level' => 'level_require',
        'status' => 'status_require',
        'ismenu' => 'ismenu_require',
        'sorts' => 'sorts_require',
    ];

    protected $scene = [
        'create' => ['pid', 'title', 'name', 'level', 'status', 'ismenu', 'sorts'],
        'edit'   => ['pid', 'title', 'name', 'level', 'status', 'ismenu', 'sorts'],
        'title'  => ['title'],
        'status' => ['status'],
        'ismenu' => ['ismenu'],
        'sorts'  => ['sorts'],
    ];
    
    //自定义权限节点验证规则
    protected function checkAuthRule($value, $rule, $data=[])
    {
        $authRuleModel =  new \app\common\model\AuthRule();
        if (empty($data['id'])){
            $where = [
                ['module', '=', $data['module']],
                ['name', '=', $data['name']],
            ];
            $res = $authRuleModel->where($where)->find();
            if (!empty($res)){
                return lang('name_unique');
            }else{
                return true;
            }
        }else{
            $where = [
                ['id', '<>', $data['id']],
                ['module', '=', $data['module']],
                ['name', '=', $data['name']],
            ];
            $res = $authRuleModel->where($where)->find();
            if (!empty($res)){
                return lang('name_unique');
            }else{
                return true;
            }
        }
    }
}