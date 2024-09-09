<?php
return [
    //数据库字段
    'id'                => 'ID',
    'k'                 => '键',
    'v'                 => '值',
    'type'              => '类型',
    'infos'             => '描述',
    'prompt'            => '文本框提示',
    'texttype'          => '文本类型',
    'textvalue'         => '文本选项值',
    'sorts'             => '排序',
    'status'            => '状态',
    
    'dbconfig_error'    => '配置文件生成失败，请检查！',
    
    //数据验证提示
    'k_require'             => '键不能为空',
    'v_require'             => '值不能为空',
    'type_require'          => '类型不能为空',
    'infos_require'         => '描述不能为空',
    'texttype_require'      => '文本类型不能为空',
    'textvalue_require'     => '文本选项值不能为空',
    'sorts_require'         => '排序必须为大于0数字整数',
    'status_require'        => '状态必须为数字整数（0,1）',
];