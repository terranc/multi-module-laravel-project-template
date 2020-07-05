<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\LiveOrder;
use App\Models\LiveProduct;
use App\Models\Redpacket;
use App\Models\Room;
use App\Models\User;
use App\Models\UserMedalTask;
use App\Models\WechatUser;
use App\Services\RoomService;
use App\Models\LiveMedalLog;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    protected $testRoomId;
    protected $testActivityId;
    protected $testUserId;
    protected $testWuser;

    /**
     * @param $activityId
     * @param $roomId
     */
    public function createProducts($activityId, $roomId): void
    {
        $merchants = Merchant::whereActivityId($activityId)->get();
        foreach ($merchants as $merchant) {
            /** @var Merchant $merchant */
            if (!LiveProduct::whereMerchantId($merchant->id)->exists()) {
                LiveProduct::create([
                    'activity_id' => $activityId,
                    'room_id' => $roomId,
                    'merchant_id' => $merchant->id,
                    'name' => $merchant->name,
                    'img' => $merchant->logo,
                    'live_price' => 100000,
                    'market_price' => 500000,
                    'visible' => 1,
                ]);
            }
        }
    }

    private function getReq($openId = '')
    {
        if (empty($openId)) {
            $openId = $this->testWuser->openid;
        }
        $token = encrypt(['openid' => $openId]);
        return $this->disableCookieEncryption()->withCookie('token', $token);
    }

    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();
        \CacheClient::flush();
        \CacheClient::flushAll();
        list($activity, $room) = $this->getActivityAndRoom(false);
        $this->createProducts($activity['id'], $room['id']);
        $this->testActivityId = $activity['id'];
        $this->testRoomId = $room['id'];

        $wuser = WechatUser::whereHas('user', function(Builder $query) {
            $query->where('phone', '<>', '')->where('activity_id', $this->testActivityId);
        })->first();
        $this->testWuser = $wuser;
        $user = $this->ensureUser($room['id'], $activity['id'], $wuser);
        $this->testUserId = $user->id;
    }

    private function reqPost($uri, $data = [])
    {
        $data['activity_id'] = $this->testActivityId;
        return $this->getReq()->post($uri, $data);
    }

    private function reqGet($uri)
    {
        if (strpos($uri, "?") === false) {
            $uri = "{$uri}?activity_id=$this->testActivityId";
        } else {
            $uri = $uri . "&activity_id=$this->testActivityId";
        }
        return $this->getReq()->get($uri);
    }

    /**
     * A basic feature test example.
     * @return void
     * @throws \Throwable
     */
    public function testGetRoomInfo()
    {
        LiveProduct::whereActivityId($this->testActivityId)->where('visible', 1)->firstOrFail()->setPlay();
        $response = $this->reqGet("/api/room/v1/room/get_room_info?room_id=$this->testRoomId");
        if ($response->status() != 200) {
            dump($response->decodeResponseJson());
        }
//        dd($response->json());
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "room_id",
            "title",
            "popularity",
            "cover",
            "description",
            "notice",
            "news_ticker",
            "status",
            "play_url",
            "im_id",
            "gifts" => [
                [
                    "id",
                    "name",
                    "num",
                    "price",
                ],
            ],
            "products" => [
                [
                    "id",
                    "name",
                    "description",
                    "market_price",
                    "display_price",
                    "sold_num",
                    "visible",
                    "merchant" => [
                        "name",
                        "logo_url",
                    ]
//                    "stores" => [
//                        [
//                            "name",
//                            "address",
//                            "phone",
//                        ],
//                    ],
                ],
            ],
            "current_product" => [
                "id",
                "name",
                "description",
                "market_price",
                "display_price",
                'sold_num',
                'visible',
                "merchant" => [
                    "name",
                    "logo_url",
                ],
                "stores" => [
                    [
                        "name",
                        "address",
                        "phone",
                    ],
                ],
            ],
            "current_medal" => [
                "activity_id",
                "merchant_id",
                "seller_name",
                "seller_title",
                "seller_avatar",
                "interact_times",
                "name",
                "img_url",
            ],
        ]);
    }

    public function testSendMessage()
    {
        $uri = '/api/room/v1/room/send_message';
        $resp = $this->reqPost($uri, ['room_id' => $this->testRoomId]);
        $resp->assertStatus(400);
        $resp->assertJsonStructure(['message']);
        $resp = $this->reqPost($uri, ['content' => 'Anything']);
        $resp->assertStatus(400);
        $resp->assertJsonStructure(['message']);
        $resp = $this->reqPost($uri, ['room_id' => $this->testRoomId, 'content' => 'Anything']);
        if ($resp->status() != 200) {
            dump($resp->decodeResponseJson());
        }
        $resp->assertStatus(200);
    }

    public function testGetUserInfo()
    {
        $uri = "/api/room/v1/room/get_user_info";
        $resp = $this->reqGet($uri);
        $resp->assertStatus(400);
        $resp = $this->reqGet($uri . "?room_id=$this->testRoomId");
        $resp->assertStatus(200);
        $resp->assertJsonStructure([
            "room_id",
            "im_token",
            "im_name",
            "im_id",
            "chatroom_im_id",
        ]);
    }

    public function testSendFreeGift()
    {
        $uri = 'api/room/v1/gift/send_free';
        $resp = $this->reqPost($uri, ['gift_id' => 3, 'room_id' => $this->testRoomId]);
        $resp->assertStatus(400);
        $resp = $this->reqPost($uri, ['gift_id' => 1, 'room_id' => $this->testRoomId]);
        $resp->assertStatus(200);
    }

    public function testSendGift()
    {
        $uri = 'api/room/v1/gift/send';
        $resp = $this->reqPost(
            $uri,
            ['gift_id' => 1, 'room_id' => $this->testRoomId, 'number' => 2]
        );
        $resp->assertStatus(500);
        $resp = $this->reqPost(
            $uri,
            ['gift_id' => 3, 'room_id' => $this->testRoomId, 'number' => 2]
        );
        if ($resp->status() != 200) {
            dump($resp->decodeResponseJson());
        }
        $resp->assertStatus(200);
        $resp->assertJsonStructure([
            "pay_params" => [
                "timestamp",
                "nonce_str",
                "package",
                "sign_type",
                "pay_sign",
            ],
            "order_code",
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testGrabRedpacket()
    {
        // 先充50块钱
        /** @var RoomService $s */
        $s = app(RoomService::class);
        $merchant = Merchant::whereActivityId($this->testActivityId)->firstOrFail();
        $merchant->increment('red_package_pool', 5000);
        $pac = $s->sendRoomRedpacket($merchant->id, $this->testActivityId, 50, 1);
        $resp = $this->reqPost("/api/room/v1/redpacket/{$pac->id}/grab");
        dump($resp->json());
        $resp->assertStatus(200);
        $this->assertEquals(true, $resp->decodeResponseJson()['get']);
        $resp = $this->reqPost("/api/room/v1/redpacket/{$pac->id}/grab");
        $resp->assertStatus(200);
        $this->assertEquals(false, $resp->decodeResponseJson()['get']);
    }

    public function testSendRedpacketAll()
    {
        $merchant = Merchant::whereActivityId($this->testActivityId)->firstOrFail();
        /** @var RoomService $s */
        $s = app(RoomService::class);
        $redpacket = $s->sendRoomRedpacket($merchant->id, $this->testActivityId, $merchant->red_package_pool, 1);
        $this->assertEquals($merchant->id, $redpacket->merchant_id);
        $this->assertEquals($merchant->red_package_pool, $redpacket->total_amount);
    }

    public function testCreateOrder()
    {
        LiveOrder::whereUserId($this->testUserId)->delete();
        $product = LiveProduct::whereActivityId($this->testActivityId)
            ->where('visible', 1)->firstOrFail();
        $resp = $this->reqPost(
            '/api/room/v1/product/create_order',
            [
                'product_id' => $product->id,
            ]
        );
        if ($resp->status() != 200) {
            dump($resp->decodeResponseJson());
        }
        $resp->assertStatus(200);
        $resp->assertJsonStructure([
            "pay_params" => [
                "timestamp",
                "nonce_str",
                "package",
                "sign_type",
                "pay_sign",
            ],
            "order_code",
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testFetchMedal()
    {
        LiveMedalLog::whereUserId($this->testUserId)->delete();
        UserMedalTask::whereUserId($this->testUserId)->delete();
        $product = LiveProduct::whereActivityId($this->testActivityId)->where('play_status', '<>', LiveProduct::PLAY_STATUS_PLAYING)->firstOrFail();
        $product->setPlay();
        $resp = $this->reqPost(
            '/api/room/v1/medal/fetch',
            [
                'activity_id' => $this->testActivityId,
                'merchant_id' => $product->merchant_id,
            ]
        );
        if ($resp->status() != 200) {
            dump($resp->decodeResponseJson());
        }
        $resp->assertStatus(200);
        $resp = $this->reqPost(
            '/api/room/v1/medal/fetch',
            [
                'activity_id' => $this->testActivityId,
                'merchant_id' => $product->merchant_id,
            ]
        );
        $resp->assertStatus(500);
    }

    public function testListUserMedal()
    {
        $resp = $this->reqGet("/api/room/v1/medal/list_user_medals");
        $resp->assertStatus(200);
        $resp->assertJsonStructure(
            [
                "list" => [
                    '*' => [
                        "img",
                        "name",
                        "get",
                    ],
                ]
            ]
        );
    }

    public function testGetUserOrders()
    {
        $resp = $this->reqGet('/api/room/v1/product/get_user_orders');
        $resp->assertStatus(200);
//        dd($resp->decodeResponseJson());
        $resp->assertJsonStructure(
            [
                "list" => [
                    "*" => [
                        "code",
                        "amount",
                        "earnest",
                        "status",
                        "created_at",
                        "name",
                        "phone",
                        "product_info" => [
                            'id',
                            'name',
                            'description',
                            'market_price',
                            'display_price',
                            'img_url',
                            'sold_num',
                            'visible',
                            'merchant' => [
                                'name',
                                'logo_url',
                            ],
                            'stores' => [
                                "*" => [
                                    "name",
                                    "address",
                                    "phone",
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @throws \Throwable
     */
    public function testRedPacketMerchantBalance()
    {
        $activityId = $this->testActivityId;
        $merchant = Merchant::whereActivityId($activityId)->firstOrFail();
        $originalRedPacketBalance = $merchant->red_package_pool;
        $amountForTest = 10000;
        $merchant->increment('red_package_pool', $amountForTest);
        $beforeAmount = $originalRedPacketBalance + $amountForTest;
        /** @var RoomService $s */
        $s = app(RoomService::class);
        $s->sendRoomRedpacket($merchant->id, $activityId, 99, 3);
        $this->assertEquals(
            $beforeAmount - 99,
            Merchant::whereActivityId($activityId)->value('red_package_pool')
        );
        $merchant->update(['red_package_pool' => $originalRedPacketBalance]);
    }

    /**
     * @param bool $getNew
     *
     * @return array
     * @throws \Throwable
     */
    private function getActivityAndRoom($getNew = false)
    {
        /** @var RoomService $s */
        $s = app(RoomService::class);
        if ($getNew) {
            $activity = $this->get("/api/common/v1/test/create_test_activity")->decodeResponseJson();
        } else {
            $activity = Activity::latest()->firstOrFail();
        }
        $room = Room::whereActivityId($activity['id'])->first();
        if (!$room) {
            $room = $s->createRoom($activity['id'], $activity['name'], $activity['agent_id'], '', '', 'ali');
            $room->update(['can_order' => Room::CAN_ORDER_YES]);
        }
        return [$activity, $room];
    }

    private function ensureUser($roomId, $activityId, WechatUser $wuser): User
    {
        $resp = $this->getReq($wuser->openid)->get("/api/room/v1/room/get_user_info?room_id=$roomId&activity_id=$activityId");
        $resp->assertStatus(200);
        $user = User::where('wechat_user_id', $wuser->id)
            ->where('activity_id', $activityId)->firstOrFail();
        /** @var User $user */
        return $user;
    }

    /**
     * 测试红包2
     * @throws \Throwable
     */
    public function testRedPacket2()
    {
        // 1。 创建活动 创建房间
        list($activity, $room) = $this->getActivityAndRoom();
        $activityId = $activity['id'];
        $roomId = $room['id'];
        /** @var RoomService $s */
        $s = app(RoomService::class);
        // 2. 充值红包金额
        $amountForTest = 10000;
        $merchant = Merchant::whereActivityId($activityId)->firstOrFail();
        $originalRedPacketBalance = $merchant->red_package_pool;
        $merchant->increment('red_package_pool', $amountForTest);
        // 3. 发送A的品牌红包，3个1元
        $totalAmount = 300;
        $totalNum = 3;
        $pac = $s->sendRoomRedpacket($merchant->id, $activityId, $totalAmount, $totalNum);
        // 模拟4个业主领取红包，看是否有多领、少领，品牌红包余额是否正确
        $wechatUsers = WechatUser::limit($totalNum + 1)->get();
        // 创建用户
        $userBeforeBalance = [];
        foreach ($wechatUsers as $wuser) {
            /** @var WechatUser $wuser */
            $user = $this->ensureUser($roomId, $activityId, $wuser);
            $resp = $this->getReq($wuser->openid)->get("/api/room/v1/room/get_user_info?room_id=$roomId&activity_id=$activityId");
            $resp->assertStatus(200);
            $userBeforeBalance[$user->id] = $user->balance;
        }

        $getCount = 0;
        $notGetCount = 0;
        $userGetAmount = [];
        foreach ($wechatUsers as $wuser) {
            /** @var WechatUser $wuser */
            $resp = $this->getReq($wuser->openid)->post("/api/room/v1/redpacket/$pac->id/grab", [
                'activity_id' => $activityId,
            ])->decodeResponseJson();
            $user = User::where('wechat_user_id', $wuser->id)
                ->where('activity_id', $activityId)->firstOrFail();
            dump($resp);
            if ($resp['get'] == true) {
                $getCount++;
                $userGetAmount[$user->id] = $resp['amount'];
            } else {
                $notGetCount++;
                $userGetAmount[$user->id] = 0;
            }
        }
        $userAfterBalance = [];
        foreach ($wechatUsers as $wuser) {
            /** @var WechatUser $wuser */
            $user = User::where('wechat_user_id', $wuser->id)
                ->where('activity_id', $activityId)->firstOrFail();
            $userAfterBalance[$user->id] = $user->balance;
        }
        $this->assertEquals($totalNum, $getCount, "抢到的红包数量" . $totalNum . '|' . $getCount);
        $userGetTotalAmount = 0;
        foreach ($userGetAmount as $value) {
            $userGetTotalAmount += $value;
        }
        $this->assertEquals($totalAmount, $userGetTotalAmount, "抢到的红包金额");
        $this->assertEquals(
            Redpacket::REDPACKET_STATUS_OVER,
            Redpacket::findOrFail($pac->id)->status,
            "红包状态"
        );
        // 收尾 恢复余额
        $merchant->update(['red_package_pool' => $originalRedPacketBalance]);
    }

    /**
     * @throws \Throwable
     */
    public function testMedals()
    {
        list($activity, $room) = $this->getActivityAndRoom(true);
        $activityId = $activity['id'];
        $roomId = $room['id'];
        Room::whereId($roomId)->update([
            'popularity_rate' => 7,
            'medal_count' => Merchant::whereActivityId($activityId)->count()
        ]);
        $this->createProducts($activityId, $roomId);
        $products = LiveProduct::whereActivityId($activity['id'])->get();
        $wuser = WechatUser::firstOrFail();
        $this->ensureUser($roomId, $activityId, $wuser);
        foreach ($products as $product) {
            if (!$product->room_id) {
                $product->update(['room_id' => $roomId]);
            }
            $anotherProduct = LiveProduct::whereActivityId($activity['id'])
                ->where('merchant_id', '!=', $product->merchant_id)->firstOrFail();
            $product->setPlay();
            $resp = $this->getReq($wuser->openid)->post(
                "/api/room/v1/medal/fetch",
                [
                    'activity_id' => $activityId,
                    'merchant_id' => $anotherProduct->merchant_id,
                ]
            );
            $resp->assertStatus(500);
            $resp = $this->getReq($wuser->openid)->post(
                "/api/room/v1/medal/fetch",
                [
                    'activity_id' => $activityId,
                    'merchant_id' => $product->merchant_id,
                ]
            );
            $resp->assertStatus(200);
        }
        $data = $this->getReq($wuser->openid)->get("/api/room/v1/medal/list_user_medals?activity_id=$activityId")->decodeResponseJson();
        $this->assertEquals(UserMedalTask::STATUS_FINISH, $data['task_status']);
    }
}
