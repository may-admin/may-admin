<?php
return [
    //数据库字段
    'id'                => 'ID',
    'pid'               => '父级节点',
    'module'            => '所属模块',
    'level'             => '节点类型',
    'name'              => '节点地址',
    'title'             => '节点名称',
    'type'              => '类型',
    'status'            => '状态',
    'ismenu'            => '是否菜单',
    'condition'         => '条件',
    'icon'              => '节点图标',
    'sorts'             => '排序',
    'create_time'       => '创建时间',
    'update_time'       => '编辑时间',
    
    'auth_rule_top'     => '顶级节点',
    
    //数据验证提示
    'pid_require'       => '父级节点必须为数字整数',
    'pid_different'     => '不能选择自己作为父节点',
    'level_require'     => '节点类型必须为数字整数（1,2,3）',
    'name_unique'       => '节点地址已存在',
    'name_require'      => '节点地址必须为有效的URL地址',
    'title_require'     => '节点名称不能为空',
    'status_require'    => '状态必须为数字整数（0,1）',
    'ismenu_require'    => '是否菜单为数字整数（0,1）',
    'sorts_require'     => '排序必须为大于0数字整数',
];