<?php
// 全局参数配置 config('global.xxx')
return [
    // redis key 的缓存时间
    'redis_key_expire'            => env('REDIS_KEY_EXPIRE', 3600 * 24 * 30),
];
