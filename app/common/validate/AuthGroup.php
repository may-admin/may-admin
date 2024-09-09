<?php
namespace app\common\validate;

use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'title' => 'require',
        'level' => 'require|integer|>=:1',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'title' => 'title_require',
        'level' => 'level_require',
        'status' => 'status_require',
    ];

    protected $scene = [
        'create' => ['title', 'level', 'status'],
        'edit'   => ['title', 'level', 'status'],
        'title'  => ['title'],
        'status' => ['status'],
    ];
}