<?php
return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => env('common.prefix', ''),
            // 缓存有效期 0表示永久缓存
            'expire'     => (int) env('cache.expire', 0),
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接【默认】
        'redis' => [
            'type'     => env('redis.type', 'redis'),
            'host'     => env('redis.host', '127.0.0.1'),
            'port'     => env('redis.port', '6379'),
            'password' => env('redis.password', '123456'),
            'select'   => (int) env('cache.select', 0),
            // 全局缓存有效期（0为永久有效）
            'expire'   => 0,
            // 缓存前缀
            'prefix'   => env('common.prefix', ''),
            'timeout'  => 0,
        ],
        // 更多的缓存连接【token】
        'redis_token' => [
            'type'     => env('redis.type', 'redis'),
            'host'     => env('redis.host', '127.0.0.1'),
            'port'     => env('redis.port', '6379'),
            'password' => env('redis.password', '123456'),
            'select'   => (int) env('cache.token_select', 1),
            // 全局缓存有效期（0为永久有效）
            'expire'   => 0,
            // 缓存前缀
            'prefix'   => env('common.prefix', ''),
            'timeout'  => 0,
            // 'serialize'  => ['redis_token', 'redis_token'],
        ],
        // 更多的缓存连接【queue】
        'redis_queue' => [
            'type'     => env('redis.type', 'redis'),
            'host'     => env('redis.host', '127.0.0.1'),
            'port'     => env('redis.port', '6379'),
            'password' => env('redis.password', '123456'),
            'select'   => (int) env('queue.select', 2),
            // 全局缓存有效期（0为永久有效）
            'expire'   => 0,
            // 缓存前缀
            'prefix'   => env('common.prefix', ''),
            'timeout'  => 0,
            // 'serialize'  => ['redis_token', 'redis_token'],
        ],
    ],
];
