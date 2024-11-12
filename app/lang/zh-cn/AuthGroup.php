<?php
return [
    //数据库字段
    'id'                => 'ID',
    'module'            => '所属模块',
    'level'             => '排序',
    'title'             => '角色名称',
    'status'            => '状态',
    'rules'             => '角色授权',
    'notation'          => '角色描述',
    'create_time'       => '创建时间',
    'update_time'       => '编辑时间',
    
    //数据验证提示
    'title_require'         => '角色名称不能为空',
    'level_require'         => '排序必须为数字整数',
    'status_require'        => '状态必须为数字整数（0,1）',
];