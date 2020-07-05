<?php

namespace Modules\Common\Services;

use App\Exceptions\Api\ApiRequestException;
use App\Models\SystemMoneyLog;
use DB;
use App\Services\CacheService;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class CommonService {
    /**
     * @see 发送短信
     */
    public static function sendSms(string $phone) {
        $oEasySms = new EasySms(config('sms'));
        try {
            $code = rand(100001, 999999);
            $ret = $oEasySms->send($phone, [
                'content' => '',
                'template' => config('sms.template'),
                'data' => [
                    'code' => $code,
                ],
            ]);
            $status = $ret['aliyun']['status'] ?? '';
            $res_code = $ret['aliyun']['result']['Code'] ?? '';
            if ($status === 'success' && $res_code === 'OK') {
                // 短信验证码存redis 10分钟
                \CacheClient::put(fmt(CacheService::KEY_SMS_CODE, $phone), $code, config('sms.cache_code_time'));
            } else {
                throw new ApiRequestException('短信发送异常');
            }
        } catch (NoGatewayAvailableException  $exception) {
            $message = $exception->getException('aliyun')->getMessage();
            throw new ApiRequestException($message ?: '短信发送异常');
        }
        return true;
    }
}

