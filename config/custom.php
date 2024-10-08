<?php
return [
    //默认头像地址
    'default_avatar'        => '/static/global/common/img/avatar.png',
    //无缩略图地址
    'default_no_image'      => '/static/global/common/img/no_image.png',
    
    //AUTH 权限配置
    'AUTH_CONFIG'=>[
        'AUTH_ON'           => true,                    //认证开关
        'AUTH_TYPE'         => 1,                       //认证方式，1为实时认证；2为登录认证
        'AUTH_GROUP'        => 'auth_group',            //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'auth_group_access',     //用户-用户组关系表
        'AUTH_RULE'         => 'auth_rule',             //权限规则表
        'AUTH_USER'         => 'admin',                 //用户信息表
        'AUTH_GROUP_STATIC' => [1,3],                   //不可删除角色组
    ]
];
