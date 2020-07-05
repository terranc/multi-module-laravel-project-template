<?php
/**
 * Created by PhpStorm.
 * User: liugang
 * Date: 2019-11-19
 * Time: 10:09
 */

namespace App\Facades;

use App\Models\Activity;
use EasyWeChat\Factory;
use Illuminate\Support\Str;

/**
 * Class WechatFactory
 *
 * @package App\Services
 * @method static \EasyWeChat\Payment\Application            payment()
 * @method static \EasyWeChat\MiniProgram\Application        miniProgram()
 * @method static \EasyWeChat\OpenPlatform\Application       openPlatform()
 * @method static \EasyWeChat\OfficialAccount\Application    officialAccount()
 * @method static \EasyWeChat\BasicService\Application       basicService()
 * @method static \EasyWeChat\Work\Application               work()
 * @method static \EasyWeChat\OpenWork\Application           openWork()
 * @method static \EasyWeChat\MicroMerchant\Application      microMerchant()
 */
class WeChatFactory extends Factory {
    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments) {
        $confName = Str::snake($name);
        $config = array_merge(config('wechat.defaults', []), config("wechat.$confName.default"));
        if ($confName === 'payment' && isset($arguments[0])) {
            $paymentChannel = Activity::find($arguments[0])->payment_channel ?: 'default';
            $config = array_merge($config, config("wechat.$confName.$paymentChannel"));
        }
        $app = self::make($confName, $config);
        if (config('wechat.defaults.use_laravel_cache')) {
            $app['cache'] = app()['cache.store'];
        }
        // 适配laravels https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues.md#use-package-overtruewechat
        $app['request'] = \request();
        return $app;
    }
}
