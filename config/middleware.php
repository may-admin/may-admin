<?php
return [
    // 别名或分组
    'alias'    => [
        'admin_load_lang_pack' => \app\middleware\AdminLoadLangPack::class,
        'admin_session' => \app\middleware\AdminSession::class,
        'admin_auth'   => \app\middleware\AdminAuth::class,
        'home_load_lang_pack' => \app\middleware\HomeLoadLangPack::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [],
];
