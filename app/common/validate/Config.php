<?php
namespace app\common\validate;

use think\Validate;

class Config extends Validate
{
    protected $rule = [
        'k' => 'require',
        'v' => 'require',
        'type' => 'require',
        'infos' => 'require',
        'texttype' => 'require',
        'textvalue' => 'require',
        'sorts' => 'require|integer|>=:1',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'k' => 'k_require',
        'v' => 'v_require',
        'type' => 'type_require',
        'infos' => 'infos_require',
        'texttype' => 'texttype_require',
        'textvalue' => 'textvalue_require',
        'sorts' => 'sorts_require',
        'status' => 'status_require',
    ];

    protected $scene = [
        'create' => ['k', 'type', 'infos', 'texttype', 'sorts', 'status'],
        'edit'   => ['k', 'type', 'infos', 'texttype', 'sorts', 'status'],
        'k'      => ['k'],
        'v'      => ['v'],
        'infos'  => ['infos'],
        'type'   => ['type'],
        'sorts'  => ['sorts'],
        'status' => ['status'],
    ];
}