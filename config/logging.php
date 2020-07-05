<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['daily'],
            'ignore_exceptions' => false,
        ],

        'apiRequestLog' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/api-request.log'),
            'level'  => 'debug',
            'days'   => 14,
        ],

        'crontab' => [
            'driver' => 'single',
            'path'   => storage_path('logs/crontab.log'),
            'level'  => 'debug',
        ],

        'queue'       => [
            'driver' => 'single',
            'path'   => storage_path('logs/queue.log'),
            'level'  => 'debug',
        ],

        // 接口请求异常日志
        'apierrorlog' => [
            'driver' => 'single',
            'path'   => storage_path('logs/api-error.log'),
            'level'  => 'debug',
            'days'   => 14,
        ],

        // 接口请求异常日志
        'sqllog'      => [
            'driver' => 'daily',
            'path'   => storage_path('logs/sql.log'),
            'level'  => 'debug',
            'days'   => 14,
        ],

        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => 'debug',
            'days'   => 14,
        ],

        'slack' => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji'    => ':boom:',
            'level'    => 'critical',
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => 'debug',
            'handler'      => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver'    => 'monolog',
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
            'level'     => 'warn',
        ],

        'syslog'   => [
            'driver' => 'syslog',
            'level'  => 'debug',
        ],
        'stdout'   => [
            'driver'  => 'monolog',
            'handler' => StreamHandler::class,
            'with'    => [
                'stream' => 'php://stdout',
            ],
            'level'   => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => 'debug',
        ],

        'production_stack' => [
            'driver'   => 'stack',
            'tap'      => [Freshbitsweb\LaravelLogEnhancer\LogEnhancer::class],
            'channels' => ['daily'],
        ],

        'docker_stack'     => [
            'driver'   => 'stack',
            'tap'      => [Freshbitsweb\LaravelLogEnhancer\LogEnhancer::class],
            'channels' => ['stdout'],
        ],
    ],

];
