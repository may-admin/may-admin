<?php
return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => env('sission.type', 'file'),
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => env('sission.expire', 28800),
    // 前缀
    'prefix'         => env('common.prefix', ''),
];
