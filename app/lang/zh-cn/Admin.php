<?php
return [
    //数据库字段
    'id'                => 'ID',
    'username'          => '用户名',
    'password'          => '密码',
    'name'              => '姓名',
    'email'             => '邮箱',
    'mobile'            => '手机号码',
    'sex'               => '性别',
    'qq'                => 'QQ',
    'avatar'            => '头像',
    'logins'            => '登录次数',
    'reg_ip'            => '注册IP',
    'last_time'         => '最后登录时间',
    'last_ip'           => '最后登录IP',
    'status'            => '状态',
    'create_time'       => '注册时间',
    'update_time'       => '编辑时间',
    
    'repassword'        => '确认密码',
    'old_password'      => '旧密码',
    'auth_group'        => '授权角色',
    'auth_group_title'  => '角色',
    
    //数据验证提示
    'username_require'      => '用户名长度6-50（只允许字母、数字、_和-）',
    'username_unique'       => '用户名已经被使用',
    'password_require'      => '密码长度6-32（必须包含大写字母，小写字母和数字）',
    'repassword_require'    => '确认密码错误',
    'name_require'          => '姓名长度1-20（只允许汉字、字母、数字、_和-）',
    'email_require'         => '邮箱格式错误',
    'email_unique'          => '邮箱已经被使用',
    'mobile_require'        => '手机号码格式错误',
    'mobile_unique'         => '手机号码已经被使用',
    'status_require'        => '状态必须为数字整数（0,1）',
    'old_password_require'  => '若需修改密码，则密码项都填写，否则则忽略密码项',
    'old_password_error'    => '旧密码错误',
];