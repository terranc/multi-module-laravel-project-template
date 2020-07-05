<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityStage;
use App\Models\ActivityStatistical;
use App\Models\CollectOrder;
use App\Models\CollectProduct;
use App\Models\CouponOrder;
use App\Models\Merchant;
use App\Models\MerchantBeanLog;
use App\Models\MerchantPointLog;
use App\Models\Room;
use App\Models\SeckillOrder;
use App\Models\SeckillProduct;
use App\Models\Staff;
use App\Models\StaffBeanLog;
use App\Models\StaffMoneyLog;
use App\Models\StaffPointLog;
use App\Models\StageMerchantStatis;
use App\Models\StageStaffStatis;
use App\Models\SystemMoneyLog;
use App\Models\User;
use App\Models\UserMoneyLog;
use App\Models\UserStatistical;
use App\Models\ViewLog;
use App\Models\WechatUser;
use App\Services\CacheService;
use CacheClient;
use Tests\TestCase;
use TestSeeder;
use Vinkla\Hashids\Facades\Hashids;

class ActivityApiTest extends TestCase {
    public $activity;
    public $wechatUser;
    public $stage;

    /**
     * 创建活动，配置活动设置里必要的设置
     * 奖励豆设置：浏览+1、裂变+2、集赞+3、售卡+4、进直播间+5、自签单+6、贡献签单+7；
     * 奖金设置：浏览+0.1、员工裂变+0.2、业主裂变+0.3、售卡+0.4；
     * 积分设置：浏览+1、裂变+2、业主裂变+3、售卡+4
     * 裂变刻度：3、5、18
     */
    public function testCreating(): void {
        $this->withExceptionHandling();
        $this->creatingData();
        $this->artisan('activity:update-status')->assertExitCode(0);
        $this->artisan('activity:update-stage-status')->assertExitCode(0);
    }

    public function testUser(): void {
        $this->getData();
        $activity = $this->activity;
        collect($this->wechatUser)->map(function($user) use ($activity) {
            $response = $this->cookie($user)->get('/api/yun/v1/activities/' . $activity->hash);
            $response->assertStatus(200);
        });
    }

    /**
     * H 提交员工注册申请
     * 审核 H 为活动负责人
     * P 提交员工申请
     * 审核P 为品牌 1 负责人
     * X 提交员工注册申请
     * 审核 X 为活动 1 的员工
     * Y 提交员工注册申请
     * 审核 Y 为活动 1 的员工
     */
    public function testStaffActivityConfig(): void {
        $this->getData();
        // 活动负责人
        $response = $this->cookie($this->wechatUser['staffH'])
            ->post('/api/staff/v1/activities/' . $this->activity->id . '/join', [
                'phone'      => '13800001111',
                'name'       => 'staffH',
                'inviter_id' => 1,
            ]);
        $response->assertStatus(200);
        Staff::orderBy('id', 'desc')->first()->update([
            'status' => Staff::STATUS_YES,
            'role'   => Staff::ROLE_ACTIVITY_ADMIN,
        ]);
        // 活动负责人充值（品牌奖金池）
        $response = $this->cookie($this->wechatUser['staffH'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/manual_recharge/wechat/test?type=merchant&money=1000&role=activity_admin');
        $response->assertStatus(200);
        $this->activityBrandBonusPool(1000);
        // 活动负责人充值（员工奖金池）
        $response = $this->cookie($this->wechatUser['staffH'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/manual_recharge/wechat/test?type=staff&money=2000&role=activity_admin');
        $response->assertStatus(200);
        $this->activityStaffBonusPool(2000);
        $this->activityBrandStaffPool(2000);
        // 活动负责人充值（推广费用）
        $response = $this->cookie($this->wechatUser['staffH'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/manual_recharge/wechat/test?type=budget&money=3000&role=activity_admin');
        $response->assertStatus(200);
        $this->activityBudgetPool(3000);
        // 活动负责人充值（直播红包）
        $response = $this->cookie($this->wechatUser['staffH'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/manual_recharge/wechat/test?type=live_red_package&money=4000&role=activity_admin');
        $response->assertStatus(200);
        $this->activityRedPackagePool(4000);

        // 品牌负责人
        $response = $this->cookie($this->wechatUser['staffP'])
            ->post('/api/staff/v1/activities/' . $this->activity->id . '/join', [
                'phone'       => '13800001112',
                'name'        => 'staffP',
                'inviter_id'  => 1,
                'merchant_id' => 2,
                'store_id'    => 2,
            ]);
        $response->assertStatus(200);
        Staff::orderBy('id', 'desc')->first()->update([
            'status' => Staff::STATUS_YES,
            'role'   => Staff::ROLE_BRAND_ADMIN,
        ]);
        // 品牌负责人充值
        $response = $this->cookie($this->wechatUser['staffP'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/recharge/merchant/wechat/test');
        $response->assertStatus(200);
        $this->activityBudgetPool(3060);
        $this->activityRedPackagePool(4070);
        $this->activityStaffBonusPool(2020);
        $this->activityBrandBonusPool(1040);
        $this->activityBrandStaffPool(2020);

        // 员工 X
        $response = $this->cookie($this->wechatUser['staffX'])
            ->post('/api/staff/v1/activities/' . $this->activity->id . '/join', [
                'phone'       => '13800001113',
                'name'        => 'staffX',
                'inviter_id'  => 1,
                'merchant_id' => 2,
                'store_id'    => 2,
            ]);
        $response->assertStatus(200);
        Staff::orderBy('id', 'desc')->first()->update(['status' => Staff::STATUS_YES]);
        // 充值
        $response = $this->cookie($this->wechatUser['staffX'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/recharge/staff/wechat/test');
        $response->assertStatus(200);
        $this->activityStaffBonusPool(2030);

        // 员工 Y
        $response = $this->cookie($this->wechatUser['staffY'])
            ->post('/api/staff/v1/activities/' . $this->activity->id . '/join', [
                'phone'       => '13800001114',
                'name'        => 'staffY',
                'inviter_id'  => 1,
                'merchant_id' => 2,
                'store_id'    => 2,
            ]);
        $response->assertStatus(200);
        Staff::orderBy('id', 'desc')->first()->update(['status' => Staff::STATUS_YES]);
        // 充值
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/recharge/staff/wechat/test');
        $response->assertStatus(200);
        $this->activityStaffBonusPool(2040);

        $this->assertCount(4, Staff::all());

        // 设置阶段任务
        $response = $this->cookie($this->wechatUser['staffH'])
            ->post('/api/staff/v1/activities/' . $this->activity->id . '/setting/stages/1/tasks/staff', [
                'views'            => 1,
                'fissions'         => 1,
                'collects'         => 1,
                'coupons'          => 1,
                'self_signs'       => 1,
                'contribute_signs' => 1,
                'live_nums'        => 1,
                'live_orders'      => 1,
                'signups'          => 1,
                'forwards'         => 0,
            ]);
        $response->assertStatus(200);
    }

    /**
     * 员工 X 邀请业主 A（测试有效浏览，员工X奖励豆+1、积分+1、奖金+0.1，品牌1奖励豆+1、积分+1）
     */
    public function testActivityAFromXView(): void {
        $this->viewLog('userA', 'staffX');
        $builder = ViewLog::orderBy('id', 'desc')->first();

        // 有效浏览
        $this->assertEquals(ViewLog::STATUS_YES, $builder->status);
        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffX'])->first();
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 奖金+0.1
        $this->staffMoneyLog($staff->id, 10, StaffMoneyLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分 + 1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);

        $this->staffBeanLogTotal($staff->id, 1);
        $this->staffPointLogTotal($staff->id, 1);
        $this->staffMoneyLogTotal($staff->id, 10);

        $this->merchantBeanLogTotal($staff->merchant_id, 1);
        $this->merchantPointLogTotal($staff->merchant_id, 1);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLogTotal(2);

        $this->activityBudgetPool(3050);
    }

    /**
     * 员工 X 邀请业主 B（测试异地浏览，员工 X 有一次无效浏览记录）
     */
    public function testActivityBFromXView(): void {
        $this->viewLog('userB', 'staffX', false);

        // 无效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_NO, '异地浏览'], [$builder->status, $builder->memo]);
    }

    /**
     * 业主 A 邀请员工 Y（测试员工浏览，业主A有一次无效浏览记录）
     */
    public function testActivityYFromAView(): void {
        $this->viewLog('staffY', 'userA');

        // 无效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_NO, '员工无效'], [$builder->status, $builder->memo]);
    }

    /**
     * 业主 B 邀请业主 A（测试非首次浏览，业主 B 有一次无效浏览记录）
     */
    public function testActivityAFromBView(): void {
        $this->viewLog('userA', 'userB');

        // 无效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_NO, '已浏览过,staffX的链接'], [$builder->status, $builder->memo]);
    }

    /**
     * 员工 Y 邀请业主 A 报名活动（测试归属人重绑定，员工Y增加一次无效浏览、一个报名客户）
     */
    public function testActivityAFromYSignUp(): void {
        $this->viewLog('userA', 'staffY');

        // 无效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_NO, '已浏览过,staffX的链接'], [$builder->status, $builder->memo]);

        $code = random_int(100001, 999999);
        $phone = '13800002222';
        CacheClient::put(fmt(CacheService::KEY_SMS_CODE, $phone), $code, config('sms.cache_code_time'));
        // 报名
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/sign_up', [
                'from_uid' => $this->wechatUser['staffY'],
                'name'     => 'userA',
                'phone'    => $phone,
                'code'     => $code,
                'scene'    => 'signup',
            ]);

        $response->assertStatus(200);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        $statis = StageStaffStatis::where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('staff_id', $staff->id)
            ->first();
        $this->assertEquals(1, $statis->signup_count);
    }

    public function testSignUpError(): void {
        $this->getData();
        $code = random_int(100001, 999999);
        $phone = '13800002222';
        CacheClient::put(fmt(CacheService::KEY_SMS_CODE, $phone), $code, config('sms.cache_code_time'));
        // 短信验证码不正确
        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/sign_up', [
                'from_uid' => $this->wechatUser['staffY'],
                'name'     => 'userB',
                'phone'    => $phone,
                'code'     => $code - 1,
                'scene'    => 'signup',
            ]);
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '短信验证码不正确');
        // 员工不能参与活动报名
        $response = $this->cookie($this->wechatUser['staffX'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/sign_up', [
                'from_uid' => $this->wechatUser['staffY'],
                'name'     => 'staffX',
                'phone'    => $phone,
                'code'     => $code,
                'scene'    => 'signup',
            ]);
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '员工不能参与活动报名');
        // 每个手机号码只能报名一次
        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/sign_up', [
                'from_uid' => $this->wechatUser['staffY'],
                'name'     => 'userB',
                'phone'    => $phone,
                'code'     => $code,
                'scene'    => 'signup',
            ]);
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '每个手机号码只能报名一次');
    }

    /**
     * 业主A邀请业主C有效浏览（测试员工裂变，业主A奖金+0.1，员工Y奖励豆+1、积分+1，品牌1奖励豆+1、积分+1）
     */
    public function testActivityCFromAView(): void {
        $this->viewLog('userC', 'userA');

        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $user = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        // 业主奖金+0.1
        $this->userMoneyLog($user->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);

        $this->userMoneyLogTotal($user->id, 10);

        $this->staffBeanLogTotal($staff->id, 1);
        $this->staffPointLogTotal($staff->id, 1);

        $this->merchantBeanLogTotal($staff->merchant_id, 2);
        $this->merchantPointLogTotal($staff->merchant_id, 2);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLogTotal(4);

        $this->activityBudgetPool(3040);
    }

    /**
     * 业主A邀请业主D有效浏览（测试员工裂变，业主A奖金+0.1，员工Y奖励豆+1、积分+1，品牌1奖励豆+1、积分+1）
     */
    public function testActivityDFromAView(): void {
        $this->viewLog('userD', 'userA');
        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $user = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        // 业主奖金+0.1
        $this->userMoneyLog($user->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);

        $this->userMoneyLogTotal($user->id, 20);

        $this->staffBeanLogTotal($staff->id, 2);
        $this->staffPointLogTotal($staff->id, 2);

        $this->merchantBeanLogTotal($staff->merchant_id, 3);
        $this->merchantPointLogTotal($staff->merchant_id, 3);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLogTotal(6);

        $this->activityBudgetPool(3030);
    }

    /**
     * 业主A邀请业主E有效浏览（测试员工裂变，业主A奖金+0.1，员工Y奖励豆+3、积分+3、奖金+0.2，品牌1奖励豆+3、积分+3）
     */
    public function testActivityEFromAView(): void {
        $this->viewLog('userE', 'userA');

        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $user = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        // 业主奖金+0.1
        $this->userMoneyLog($user->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 有效浏览
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);
        // 裂变
        // 奖励豆+2
        $this->staffBeanLog($staff->id, 2, StaffBeanLog::TYPE_FISSIONS);
        // 积分+2
        $this->staffPointLog($staff->id, 2, StaffPointLog::FISSION_REWARD);
        // 奖金+0.2
        $this->staffMoneyLog($staff->id, 20, StaffMoneyLog::FISSION_REWARD);
        // 品牌奖励豆+2
        $this->merchantBeanLog($staff->merchant_id, 2, MerchantBeanLog::FISSION_REWARD);
        // 品牌积分+2
        $this->merchantPointLog($staff->merchant_id, 2, MerchantPointLog::FISSION_REWARD);

        $this->userMoneyLogTotal($user->id, 30);

        $this->staffBeanLogTotal($staff->id, 5);
        $this->staffPointLogTotal($staff->id, 5);
        $this->staffMoneyLogTotal($staff->id, 20);

        $this->merchantBeanLogTotal($staff->merchant_id, 6);
        $this->merchantPointLogTotal($staff->merchant_id, 6);

        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLog(20, SystemMoneyLog::STAFF_FISSION);
        $this->systemMoneyLogTotal(10);

        $this->activityBudgetPool(3000);
    }

    /**
     * 业主E邀请业主F有效浏览（测试业主裂变，业主E奖励+0.1，员工Y奖励豆+1、积分+1，品牌1奖励豆+1、积分+1）
     */
    public function testActivityFFromEView(): void {
        $this->viewLog('userF', 'userE');

        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $user = User::where('wechat_user_id', $this->wechatUser['userE'])->first();
        // 业主奖金+0.1
        $this->userMoneyLog($user->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);

        $userA = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        $this->userMoneyLogTotal($userA->id, 30);

        $this->userMoneyLogTotal($user->id, 10);

        $this->staffBeanLogTotal($staff->id, 6);
        $this->staffPointLogTotal($staff->id, 6);

        $this->merchantBeanLogTotal($staff->merchant_id, 7);
        $this->merchantPointLogTotal($staff->merchant_id, 7);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLogTotal(12);

        $this->activityBudgetPool(2990);
    }

    /**
     * 业主E邀请业主G有效浏览（测试业主裂变，业主E奖励+0.1，员工Y奖励豆+1、积分+1，品牌1奖励豆+1、积分+1）
     */
    public function testActivityGFromEView(): void {
        $this->viewLog('userG', 'userE');

        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $user = User::where('wechat_user_id', $this->wechatUser['userE'])->first();
        // 业主奖金+0.1
        $this->userMoneyLog($user->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);

        $this->userMoneyLogTotal($user->id, 20);

        $this->staffBeanLogTotal($staff->id, 7);
        $this->staffPointLogTotal($staff->id, 7);

        $this->merchantBeanLogTotal($staff->merchant_id, 8);
        $this->merchantPointLogTotal($staff->merchant_id, 8);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLogTotal(14);

        $this->activityBudgetPool(2980);
    }

    /**
     * 业主E邀请业主L有效浏览（测试业主裂变，业主E奖励+0.1，业主A奖金+0.3，员工Y奖励豆+3、积分+1，品牌1奖励豆+3、积分+1）
     */
    public function testActivityLFromEView(): void {
        $this->viewLog('userL', 'userE');
        // 有效浏览
        $builder = ViewLog::orderBy('id', 'desc')->first();
        $this->assertEquals([ViewLog::STATUS_YES, '有效浏览'], [$builder->status, $builder->memo]);

        $userE = User::where('wechat_user_id', $this->wechatUser['userE'])->first();
        // 业主E奖金+0.1
        $this->userMoneyLog($userE->id, 10, UserMoneyLog::LOG_TYPE_VIEW);

        $userA = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        // 业主A奖金+0.3
        $this->userMoneyLog($userA->id, 30, UserMoneyLog::LOG_TYPE_FISSION);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 有效浏览
        // 奖励豆+1
        $this->staffBeanLog($staff->id, 1, StaffBeanLog::TYPE_VIEWS);
        // 积分+1
        $this->staffPointLog($staff->id, 1, StaffPointLog::VIEW_REWARD);
        // 品牌奖励豆+1
        $this->merchantBeanLog($staff->merchant_id, 1, MerchantBeanLog::VIEW_REWARD);
        // 品牌积分+1
        $this->merchantPointLog($staff->merchant_id, 1, MerchantPointLog::VIEW_REWARD);
        // 裂变
        // 奖励豆+2
        $this->staffBeanLog($staff->id, 2, StaffBeanLog::TYPE_FISSIONS);
        // 品牌奖励豆+2
        $this->merchantBeanLog($staff->merchant_id, 2, MerchantBeanLog::FISSION_REWARD);

        $this->userMoneyLogTotal($userA->id, 60);

        $this->userMoneyLogTotal($userE->id, 30);

        $this->staffBeanLogTotal($staff->id, 10);
        $this->staffPointLogTotal($staff->id, 8);

        $this->merchantBeanLogTotal($staff->merchant_id, 11);
        $this->merchantPointLogTotal($staff->merchant_id, 9);

        $this->systemMoneyLog(10, SystemMoneyLog::VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_VIEW);
        $this->systemMoneyLog(5, SystemMoneyLog::SYSTEM_USER_FISSION);
        $this->systemMoneyLog(30, SystemMoneyLog::USER_FISSION);
        $this->systemMoneyLogTotal(18);

        $this->activityBudgetPool(2940);
    }

    /**
     * 业主L购卡9.9（测试购卡奖励，业主E奖金+0.4，员工Y奖励豆+4、积分+4，品牌1奖励豆+4、积分+4）
     */
    public function testLCoupon(): void {
        $this->getData();
        // 购卡
        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/coupons/pay', ['from_uid' => $this->wechatUser['userE']]);
        $response->assertStatus(200);

        $userL = User::where('wechat_user_id', $this->wechatUser['userL'])->first();
        $order = CouponOrder::where('activity_id', $this->activity->id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('user_id', $userL->id)
            ->orderBy('id', 'desc')
            ->first();

        // 支付回调
        //        $response = $this->post('/api/yun/v1/coupons_notify/test', [
        //            'out_trade_no' => $order->number,
        //            'result_code'  => 'SUCCESS',
        //        ]);
        //
        //        $response->assertStatus(200);

        $userE = User::where('wechat_user_id', $this->wechatUser['userE'])->first();
        // 业主E奖金+0.4
        $this->userMoneyLog($userE->id, 40, UserMoneyLog::LOG_TYPE_COUPON);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 售卡
        // 奖励豆+4
        $this->staffBeanLog($staff->id, 4, StaffBeanLog::TYPE_COUPON);
        // 积分+4
        $this->staffPointLog($staff->id, 4, StaffPointLog::COUPON_REWARD);
        // 品牌奖励豆+4
        $this->merchantBeanLog($staff->merchant_id, 4, MerchantBeanLog::COUPON_REWARD);
        // 品牌积分+4
        $this->merchantPointLog($staff->merchant_id, 4, MerchantPointLog::COUPON_REWARD);

        $this->userMoneyLogTotal($userE->id, 70);

        $this->staffBeanLogTotal($staff->id, 14);
        $this->staffPointLogTotal($staff->id, 12);

        $this->merchantBeanLogTotal($staff->merchant_id, 15);
        $this->merchantPointLogTotal($staff->merchant_id, 13);

        $this->systemMoneyLog(410, SystemMoneyLog::SYSTEM_COUPON);
        $this->systemMoneyLogTotal(19);
    }

    /**
     * 业主A购卡9.9（测试购卡奖励，员工Y奖金+0.4、奖励豆+4、积分+4，品牌1奖励豆+4、积分+4）
     */
    public function testACoupon(): void {
        $this->getData();
        // 购卡
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/coupons/pay', ['from_uid' => $this->wechatUser['staffY']]);
        $response->assertStatus(200);

        $userA = User::where('wechat_user_id', $this->wechatUser['userA'])->first();
        $order = CouponOrder::where('activity_id', $this->activity->id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('user_id', $userA->id)
            ->orderBy('id', 'desc')
            ->first();

        // 支付回调
        //        $response = $this->post('/api/yun/v1/coupons_notify/test', [
        //            'out_trade_no' => $order->number,
        //            'result_code'  => 'SUCCESS',
        //        ]);
        //
        //        $response->assertStatus(200);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 售卡
        // 奖励豆+4
        $this->staffBeanLog($staff->id, 4, StaffBeanLog::TYPE_COUPON);
        // 积分+4
        $this->staffPointLog($staff->id, 4, StaffPointLog::COUPON_REWARD);
        // 品牌奖励豆+4
        $this->merchantBeanLog($staff->merchant_id, 4, MerchantBeanLog::COUPON_REWARD);
        // 品牌积分+4
        $this->merchantPointLog($staff->merchant_id, 4, MerchantPointLog::COUPON_REWARD);

        $this->userMoneyLogTotal($userA->id, 60);

        $this->staffBeanLogTotal($staff->id, 18);
        $this->staffPointLogTotal($staff->id, 16);

        $this->merchantBeanLogTotal($staff->merchant_id, 19);
        $this->merchantPointLogTotal($staff->merchant_id, 17);

        $this->systemMoneyLog(410, SystemMoneyLog::SYSTEM_COUPON);
        $this->systemMoneyLogTotal(20);

        // 重复购卡
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/coupons/pay', ['from_uid' => $this->wechatUser['staffY']]);
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '请勿重复购买');
    }

    /**
     * @see 秒杀
     */
    public function testASeckill(): void {
        $this->getData();
        // 发起秒杀
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/1');
        $response->assertStatus(200);

        // 助力秒杀 （不能给自己助力）
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '不能给自己助力');

        $response = $this->cookie($this->wechatUser['staffH'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '员工不能参与秒杀助力');

        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '每人只能助力一次');

        $response = $this->cookie($this->wechatUser['userC'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userD'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '助力人数已满');

        // 任务比对
        $order = SeckillOrder::find(1);
        $this->assertEquals($order->help_num, $order->helping_num);
        $this->assertEquals(1, $order->status);

        // 支付
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1/pay');
        $response->assertStatus(200);
        $product = SeckillProduct::find($order->id);
        $this->assertEquals(1, $product->left_num);

        //        $order = SeckillOrder::find(1);
        //        $response = $this->post('/api/yun/v1/seckill_notify/test', [
        //            'out_trade_no' => $order->number,
        //            'result_code'  => 'SUCCESS',
        //        ]);
        //        $response->assertStatus(200);

        // 库存
        $product = SeckillProduct::find($order->seckill_product_id);
        $this->assertEquals(1, $product->sold_num);
        $this->assertEquals(1, $product->left_num);

        // 员工统计
        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        $statis = StageStaffStatis::where('staff_id', $staff->id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals(1, $statis->seckill_count);

        $merchantStatis = StageMerchantStatis::where('merchant_id', $staff->merchant_id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals(1, $merchantStatis->seckill_count);
    }


    /**
     * @see 集赞
     */
    public function testACollect() {
        $this->getData();
        // 发起集赞
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collects');
        $response->assertStatus(200);

        // 集赞助力 （不能给自己助力）
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '不能给自己集赞');

        $response = $this->cookie($this->wechatUser['staffH'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '员工不能参与集赞助力');

        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '每人只能集赞助力一次');

        $response = $this->cookie($this->wechatUser['userC'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userD'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collect_orders/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '集赞助力人数已满');

        // 任务比对
        $order = CollectOrder::find(1);
        $this->assertEquals($order->help_num, $order->helping_num);
        $this->assertEquals(1, $order->status);

        // 库存
        $product = CollectProduct::find($order->collect_product_id);
        $this->assertEquals(1, $product->product_count);

        // 员工统计
        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        $statis = StageStaffStatis::where('staff_id', $staff->id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals(1, $statis->collect_count);

        $merchantStatis = StageMerchantStatis::where('merchant_id', $staff->merchant_id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals(1, $merchantStatis->collect_count);
    }

    /**
     * @see
     */
    public function testStartCollect(): void {
        $this->getData();
        // 员工
        $response = $this->cookie($this->wechatUser['staffX'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collects');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '员工不能参与活动集赞');

        // 未报名
        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collects');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '请先报名');

        // 只能参加一次集赞
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/collects');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '每人只能参加一次集赞');
    }

    /**
     * @see 直播间领勋章
     */
    public function testLlive(): void {
        // 开启直播
        Room::find(1)->update(['status' => Room::STATUS_LIVING]);
        $locution = [
            'location'          => '湖南省长沙市五一广场', // 长沙
            'is_valid_location' => true,
        ];
        $this->getData();
        // 设置地址
        $hashUid = Hashids::connection('user')->encode($this->wechatUser['staffY']);
        $rsa_public_key = config('testing.rsa_public_key');
        $encryptData = '{"from_user_hash":"' . $hashUid . '","is_valid_location":true}';
        openssl_public_encrypt($encryptData, $encrypted, $rsa_public_key);
        $token = base64_encode($encrypted);
        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->hash . '/location', array_merge($locution, [
                'scene'          => 'collect_medal',
                'from_user_hash' => $hashUid,
                'token'          => $token,
            ]));
        $response->assertStatus(200);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 奖励豆+5
        $this->staffBeanLog($staff->id, 5, StaffBeanLog::TYPE_INTO_LIVE);
        // 品牌奖励豆+5
        $this->merchantBeanLog($staff->merchant_id, 5, MerchantBeanLog::INTO_LIVE);
    }

    /**
     * 业主L直播间下单品牌1商品（测试下单奖励，员工Y奖励豆+6，品牌1奖励豆+6）
     */
    public function testLLiveOrderBrandOne(): void {
        $this->getData();
        $code = random_int(100001, 999999);
        $phone = '13800002223';
        CacheClient::put(fmt(CacheService::KEY_SMS_CODE, $phone), $code, config('sms.cache_code_time'));
        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/sign_up', [
                'from_uid' => $this->wechatUser['userE'],
                'name'     => 'userL',
                'phone'    => $phone,
                'code'     => $code,
                'scene'    => 'live',
            ]);

        $response->assertStatus(200);
        $response = $this->cookie($this->wechatUser['userL'])->post('/api/room/v1/product/create_order', [
            'product_id'  => 1,
            'activity_id' => 1,
        ]);
        $response->assertStatus(200);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 自签单
        // 奖励豆+6
        $this->staffBeanLog($staff->id, 6, StaffBeanLog::TYPE_SELF_SIGN);
        // 品牌奖励豆+6
        $this->merchantBeanLog($staff->merchant_id, 6, MerchantBeanLog::SELF_SIGN);
    }

    /**
     * 业主L直播间下单品牌2商品（测试下单奖励，员工Y奖励豆+7，品牌1奖励豆+7）
     */
    public function testLLiveOrderBrandTwo(): void {
        $this->getData();
        $response = $this->cookie($this->wechatUser['userL'])->post('/api/room/v1/product/create_order', [
            'product_id'  => 2,
            'activity_id' => 1,
        ]);
        $response->assertStatus(200);

        $staff = Staff::where('wechat_user_id', $this->wechatUser['staffY'])->first();
        // 签单
        // 奖励豆+7
        $this->staffBeanLog($staff->id, 7, StaffBeanLog::TYPE_CONTRIBUTE_SIGN);
        // 品牌奖励豆+7
        $this->merchantBeanLog($staff->merchant_id, 7, MerchantBeanLog::TYPE_CONTRIBUTE_SIGN);
    }

    /**
     * @see 员工、未报名、库存不够不能发起秒杀，
     */
    public function testStartSeckill(): void {
        $this->getData();
        // 员工
        $response = $this->cookie($this->wechatUser['staffX'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '员工不能参与秒杀');

        // 未报名
        $response = $this->cookie($this->wechatUser['userB'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '请先报名');

        // 每个产品只能参加一次秒杀
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/1');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '每个产品只能参加一次秒杀');

        // 库存不够
        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/2');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '很遗憾，秒杀产品已抢光');
    }

    /**
     * @see 秒杀支付相关
     */
    public function testSeckillPay(): void {
        $this->getData();

        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/1/pay');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '请勿重复支付');

        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/3');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/2/pay');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '请先完成任务');

        $response = $this->cookie($this->wechatUser['userG'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/2');
        $response->assertStatus(200);

        SeckillProduct::find(3)->update(['is_hidden' => 1]);
        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/2/pay');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '产品已经下架');

        SeckillProduct::find(3)->update(['is_hidden' => 0, 'sold_num' => 10]);
        $response = $this->cookie($this->wechatUser['userL'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/2/pay');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '秒杀产品数量不够');

        SeckillProduct::find(3)->update(['is_hidden' => 0, 'sold_num' => 0]);

        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_products/3');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userE'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/3');
        $response->assertStatus(200);

        $response = $this->cookie($this->wechatUser['userA'])
            ->post('/api/yun/v1/activities/' . $this->activity->id . '/seckill_orders/3/pay');
        $response->assertStatus(400);
        $msg = \GuzzleHttp\json_decode($response->getContent(), true)['message'];
        $this->assertEquals($msg, '秒杀数量超过限制');
    }

    /**
     * 验证 A 信息
     */
    public function testAStatistical(): void {
        $this->getData();
        $response = $this->cookie($this->wechatUser['userA'])->get('/api/yun/v1/activities/' . $this->activity->hash);
        $response->assertStatus(200);
        $response->assertJson([
            'seckill_limit'     => 1,
            'current_user_info' => [
                'user_id'                 => 5,
                'signed_up'               => true,
                'bonus'                   => 60,
                'seckills'                => 1,
                'can_buy_seckill_product' => false,
                'is_collect'              => true,
            ],
        ]);

        $response = $this->cookie($this->wechatUser['userA'])
            ->get('/api/yun/v1/activities/' . $this->activity->id . '/my/seckill_orders');
        $response->assertStatus(200);

        $response->assertJson([
            [
                'status' => 'done',
            ],
        ]);
    }

    /**
     * 验证 E 信息
     */
    public function testEStatistical(): void {
        $this->getData();
        $response = $this->cookie($this->wechatUser['userE'])->get('/api/yun/v1/activities/' . $this->activity->hash);
        $response->assertStatus(200);
        $response->assertJson([
            'current_user_info' => [
                'user_id'                 => 9,
                'signed_up'               => false,
                'bonus'                   => 70,
                'coupons'                 => 1,
                'can_buy_seckill_product' => true,
            ],
        ]);
    }

    /**
     * E 的优惠券
     */
    public function testECoupon(): void {
        $this->getData();
        $response = $this->cookie($this->wechatUser['userE'])
            ->get('/api/yun/v1/activities/' . $this->activity->id . '/my/coupons');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /**
     * E 的奖励
     */
    public function testERewardRecords(): void {
        $this->getData();
        $response = $this->cookie($this->wechatUser['userE'])
            ->get('/api/yun/v1/activities/' . $this->activity->id . '/my/reward_records');
        $response->assertStatus(200);
        $response->assertJsonCount(4);
    }

    /**
     * @see Y排行数据
     */
    public function testYRank(): void {
        $this->getData();
        $this->artisan('rank:update-staff')->assertExitCode(0);
        // 浏览排行
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=views&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 6,
            ],
        ]);
        // 裂变
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=fissions&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 2,
            ],
        ]);
        // 报名
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=signups&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 2,
            ],
        ]);
        // 售卡
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=coupons&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 2,
            ],
        ]);
        // 自签单
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=self_signs&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 1,
            ],
        ]);
        // 贡献签单
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=contributed_signs&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 1,
            ],
        ]);
        // 集赞
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/ranking/mine?ranking_role=staff&ranking_type=collects&period=all');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'amount' => 1,
            ],
        ]);
    }

    public function testStage(): void {
        $this->getData();
        $this->artisan('listener:staff_finish_status')->assertExitCode(0);

        // 排行
        $response = $this->cookie($this->wechatUser['staffY'])
            ->get('/api/staff/v1/activities/' . $this->activity->id . '/bonus/stages/1/ranking/mine?ranking_role=staff');
        $response->assertStatus(200);
        $response->assertJson([
            'ranking' => 1,
            'data'    => [
                'beans'            => 39,
                'estimated_amount' => 39.78,
                'is_finished'      => true,
                'name'             => 'staffY',
                'views'            => [
                    'actual'   => 6,
                    'required' => 1,
                ],
                'signups'          => [
                    'actual'   => 2,
                    'required' => 1,
                ],
                'self_signs'       => [
                    'actual'   => 1,
                    'required' => 1,
                ],
                'contribute_signs' => [
                    'actual'   => 1,
                    'required' => 1,
                ],
                'fissions'         => [
                    'actual'   => 2,
                    'required' => 1,
                ],
                'live_nums'        => [
                    'actual'   => 1,
                    'required' => 1,
                ],
                'live_orders'      => [
                    'actual'   => 2,
                    'required' => 1,
                ],
                'collects'         => [
                    'actual'   => 1,
                    'required' => 1,
                ],
                'coupons'          => [
                    'actual'   => 2,
                    'required' => 1,
                ],
            ],
        ]);
    }

    public function testActivityRetryView(): void {
        $this->getData();
        $this->viewLog('userM', 'staffP', false);
        $builder = ViewLog::orderBy('id', 'desc')->first();
        // 无效
        $this->assertEquals(ViewLog::STATUS_NO, $builder->status);

        $this->viewLog('userM', 'staffP');
        $builder = ViewLog::orderBy('id', 'desc')->first();
        // 有效
        $this->assertEquals(ViewLog::STATUS_YES, $builder->status);

        $this->viewLog('userM', 'staffP');
        $builder = ViewLog::orderBy('id', 'desc')->first();
        // 无效
        $this->assertEquals(ViewLog::STATUS_NO, $builder->status);

        $this->viewLog('userM', 'staffP');
        $builder = ViewLog::orderBy('id', 'desc')->first();
        // 无效
        $this->assertEquals(ViewLog::STATUS_NO, $builder->status);

        $this->viewLog('userM', 'staffP');
        $builder = ViewLog::orderBy('id', 'desc')->first();
        // 无效
        $this->assertEquals(ViewLog::STATUS_NO, $builder->status);
    }

    private function cookie($uid): ActivityApiTest {
        $wechatUser = WechatUser::find($uid);
        $token = encrypt(['openid' => $wechatUser->openid]);
        return $this->disableCookieEncryption()->withCookie('token', $token);
    }

    private function creatingData(): void {
        CacheClient::flushAll();
        $this->seed(TestSeeder::class);
        $this->wechatUser = WechatUser::all()->pluck('id', 'nickname');
        $this->activity = Activity::first();
    }

    private function getData(): void {
        $this->wechatUser = WechatUser::all()->pluck('id', 'nickname');
        $this->activity = Activity::first();
        $this->stage = ActivityStage::first();
    }

    private function viewLog($user, $fromUser, $local = true): void {
        if ($local) {
            $locution = [
                'location'          => '湖南省长沙市五一广场', // 长沙
                'is_valid_location' => $local,
                'latitude'          => '28.196350',
                'longitude'         => '112.977330',
            ];
        } else {
            $locution = [
                'location'          => '湖南省常德市万达广场', // 常德
                'is_valid_location' => $local,
                'latitude'          => '29.057950',
                'longitude'         => '111.681360',
            ];
        }
        $this->getData();
        $hashUid = Hashids::connection('user')->encode($this->wechatUser[$fromUser]);
        $rsa_public_key = config('testing.rsa_public_key');
        $is_valid_location = $local ? 'true' : 'false';
        $encryptData = '{"from_user_hash":"' . $hashUid . '","is_valid_location":' . $is_valid_location . '}';
        openssl_public_encrypt($encryptData, $encrypted, $rsa_public_key);
        $token = base64_encode($encrypted);
        // 设置地址
        $response = $this->cookie($this->wechatUser[$user])
            ->post('/api/yun/v1/activities/' . $this->activity->hash . '/location', array_merge($locution, [
                'from_user_hash' => $hashUid,
                'token'          => $token,
            ]));
        $response->assertStatus(200);
    }

    private function systemMoneyLogTotal($num): void {
        $total = SystemMoneyLog::where('activity_id', $this->activity->id)
            ->where('activity_stage_id', $this->stage->id)
            ->count();
        $this->assertEquals($num, $total);
    }

    private function systemMoneyLog($num = 1, $type = 0): void {
        $moneyLog = SystemMoneyLog::where('activity_id', $this->activity->id)
            ->where('activity_stage_id', $this->stage->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $moneyLog->money);
    }

    private function staffBeanLog($staffId, $num = 1, $type = 0): void {
        $beanLog = StaffBeanLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $beanLog->bean);
    }

    private function staffBeanLogTotal($staffId, $num): void {
        $beanLogTotal = StaffBeanLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('bean');
        $this->assertEquals($num, $beanLogTotal);
        $statis = StageStaffStatis::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals($num, $statis->bean_count);
    }

    private function staffPointLog($staffId, $num = 1, $type = 0): void {
        $pointLog = StaffPointLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $pointLog->point);
    }

    private function staffPointLogTotal($staffId, $num): void {
        $pointLogTotal = StaffPointLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('point');
        $this->assertEquals($num, $pointLogTotal);
        $staff = Staff::find($staffId);
        $this->assertEquals($num, $staff->point);
    }

    private function staffMoneyLog($staffId, $num, $type = 0): void {
        $moneyLog = StaffMoneyLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $moneyLog->money);
    }

    private function staffMoneyLogTotal($staffId, $num): void {
        $moneyLogTotal = StaffMoneyLog::where('staff_id', $staffId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('money');
        $this->assertEquals($num, $moneyLogTotal);
        $staff = Staff::find($staffId);
        $this->assertEquals($num, $staff->withdrawable);
    }

    private function merchantBeanLog($merchantId, $num = 1, $type = 0): void {
        $merchantBeanLog = MerchantBeanLog::where('merchant_id', $merchantId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $merchantBeanLog->bean);
    }

    private function merchantBeanLogTotal($merchantId, $num): void {
        $merchantBeanLogTotal = MerchantBeanLog::where('merchant_id', $merchantId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('bean');
        $this->assertEquals($num, $merchantBeanLogTotal);
        $statis = StageMerchantStatis::where('merchant_id', $merchantId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->first();
        $this->assertEquals($num, $statis->bean_count);
    }

    private function merchantPointLog($merchantId, $num = 1, $type = 0): void {
        $merchantPointLog = MerchantPointLog::where('merchant_id', $merchantId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $merchantPointLog->point);
    }

    private function merchantPointLogTotal($merchantId, $num): void {
        $merchantPointLogTotal = MerchantPointLog::where('merchant_id', $merchantId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('point');
        $this->assertEquals($num, $merchantPointLogTotal);
        $merchant = Merchant::find($merchantId);
        $this->assertEquals($num, $merchant->point);
    }

    private function userMoneyLog($userId, $num = 1, $type = 0): void {
        $moneyLog = UserMoneyLog::where('user_id', $userId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        $this->assertEquals($num, $moneyLog->money);
    }

    private function userMoneyLogTotal($userId, $num): void {
        $moneyLogTotal = UserMoneyLog::where('user_id', $userId)
            ->where('activity_stage_id', $this->stage->id)
            ->where('activity_id', $this->activity->id)
            ->sum('money');
        $this->assertEquals($num, $moneyLogTotal);
        $userStatistical = UserStatistical::where('user_id', $userId)->first();
        $this->assertEquals($num, $userStatistical->all_bonus);
    }

    private function activityBudgetPool($money): void {
        $statistical = ActivityStatistical::where('activity_id', $this->activity->id)->first();
        $this->assertEquals($money, $statistical->budget_pool);
    }

    private function activityRedPackagePool($money): void {
        $statistical = ActivityStatistical::where('activity_id', $this->activity->id)->first();
        $this->assertEquals($money, $statistical->red_package_pool);
    }

    private function activityBrandBonusPool($money): void {
        $statistical = ActivityStatistical::where('activity_id', $this->activity->id)->first();
        $this->assertEquals($money, $statistical->brand_bonus_pool);
    }

    private function activityStaffBonusPool($money): void {
        $statistical = ActivityStatistical::where('activity_id', $this->activity->id)->first();
        $this->assertEquals($money, $statistical->staff_bonus_pool);
    }

    private function activityBrandStaffPool($money): void {
        $statistical = ActivityStatistical::where('activity_id', $this->activity->id)->first();
        $this->assertEquals($money, $statistical->brand_staff_pool);
    }
}
