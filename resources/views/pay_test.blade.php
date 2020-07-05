<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>

    <!--for mobile web app-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="mobile-web-app-capable" content="yes"/>
    <meta name="theme-color" content="#000"/>
    <!-- Chrome, Firefox OS and Opera -->
    <meta name="msapplication-navbutton-color" content="#000"/>
    <!-- Windows Phone -->

    <title>微信支付</title>
</head>
<body>
<style>
    .paying {
        width: 100vw;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .icon {
        width: 100px;
        height: 83px;
        fill: #c3c3c3;
    }

    .tips {
        margin-top: 20px;
        color: #4b4b4b;
        font-size: 16px;
    }

    #backtoLink {
        color: #31b5a5;
        font-size: 16px;
        text-decoration: none;
    }

    #backtoLink:hover {
        text-decoration: underline;
    }

    .invalid {
        width: 100vw;
        height: 100vh;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #4b4b4b;
    }
</style>
<div id="paying" class="paying">
    <svg
            t="1504760155864"
            class="icon"
            viewBox="0 0 1256 1024"
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            p-id="2488"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            width="245.3125"
            height="200"
    >
        <defs>
            <style type="text/css"></style>
        </defs>
        <path
                d="M876.275308 315.158485c6.299427 0 12.505299 0.062371 18.742356 0.405409C862.179559 136.809843 677.562675 0 454.681447 0 208.629553 0 9.199659 166.810086 9.199659 372.570593c0 120.655866 68.545255 227.902059 174.85589 295.979535 1.372153 0.873188 4.085272 2.588379 4.085272 2.588379l-43.035693 134.813985 161.040809-82.017298c0 0 5.052016 1.434523 7.578024 2.18297 44.251919 12.349373 91.684736 18.991838 140.957486 18.991838 10.041662 0 20.052138-0.374223 29.937873-0.904373-9.137288-28.347424-14.158119-58.12937-14.158119-88.878061C470.461201 467.436229 652.209039 315.158485 876.275308 315.158485zM609.734681 183.712511c34.52211 0 62.526495 27.100012 62.526495 60.530637 0 33.430625-27.9732 60.593008-62.526495 60.593008-34.58448 0-62.557681-27.162383-62.557681-60.593008C547.177001 210.781338 575.119016 183.712511 609.734681 183.712511zM299.659398 304.836155c-34.553295 0-62.557681-27.162383-62.557681-60.593008 0-33.46181 27.9732-60.530637 62.557681-60.530637s62.620051 27.100012 62.620051 60.530637C362.279449 277.673773 334.243879 304.836155 299.659398 304.836155z"
                p-id="2489"
        ></path>
        <path
                d="M503.236935 657.510537c0 173.857961 168.556462 314.784261 376.406383 314.784261 41.601169 0 81.67426-5.675722 119.034231-16.122792 2.151785-0.59252 6.424169-1.839932 6.424169-1.839932l135.999025 69.356073-36.362042-113.951029c0 0 2.307711-1.434523 3.430381-2.18297 89.813619-57.505665 147.724692-148.098916 147.724692-250.074796 0-173.79559-168.494092-314.721891-376.281642-314.721891C671.793397 342.788647 503.236935 483.714947 503.236935 657.510537zM957.731271 549.079303c0-28.191497 23.638446-51.112681 52.796687-51.112681 29.251797 0 52.859057 22.889999 52.859057 51.112681 0 28.316238-23.638446 51.268608-52.859057 51.268608C981.369716 600.316726 957.731271 577.395541 957.731271 549.079303zM695.806067 549.079303c0-28.191497 23.700816-51.112681 52.859057-51.112681 29.220612 0 52.859057 22.889999 52.859057 51.112681 0 28.316238-23.669631 51.268608-52.859057 51.268608C719.475697 600.316726 695.806067 577.395541 695.806067 549.079303z"
                p-id="2490"
        ></path>
    </svg>
    <script>
        function back() {
            window.history.back();
        }
    </script>
    <div class="tips">正在唤起微信支付, 点击 <a id="backtoLink" onclick="back()">此处</a> 返回</div>
</div>
</body>
<script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript"></script>
<script>
    wx.config({
        debug: false,
        appId: "<?= $sAppId ?? '' ?>",
        timestamp: <?= request()->timestamp ?? '' ?>, // 必填，生成签名的时间戳
        nonceStr: "<?= request()->nonce_str ?? '' ?>", // 必填，生成签名的随机串
        signature: "<?= request()->pay_sign ?? '' ?>",// 必填，签名，见附录1
        jsApiList: '' // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });
    wx.ready(function () {
        var url = "<?= request()->redirect ?? '' ?>";
        wx.chooseWXPay({
            timestamp: <?= request()->timestamp ?? '' ?>,
            nonceStr: "<?= request()->nonce_str ?? '' ?>",
            package: "<?= request()->package ?? '' ?>",
            signType: "<?= request()->sign_type ?? '' ?>",
            paySign: "<?= request()->pay_sign ?? '' ?>", // 支付签名
            success: function (res) {
                if (res.errMsg == "chooseWXPay:ok") {
                    window.location.replace(url);
                } else {
                    window.location.replace(response_pay_res(url, "支付失败"));
                }
            },
            cancel: function (res) {
                window.location.replace(response_pay_res(url, "用户取消"));
            },
            fail: function (res) {
                window.location.replace(response_pay_res(url, "支付失败"));
            }
        });
    })


    function response_pay_res(url, msg) {
        var a = document.createElement("a");

        a.href = url;

        var queryStr = a.search.replace("?", "");

        var query = queryStr != null && queryStr !== "" ? deserialize(queryStr) : {};

        query.failed = msg;

        a.search = "?" + serialize(query);

        return a.href;
    }

    function deserialize(source, omitEmpty) {
        if (omitEmpty === void 0) {
            omitEmpty = true;
        }
        if (!source)
            return {};
        source = source.replace("?", "");
        var kvArr = source.split("&");
        var target = {};
        for (var _i = 0, kvArr_1 = kvArr; _i < kvArr_1.length; _i++) {
            var kv = kvArr_1[_i];
            var kvPair = kv.split("=");
            if (kvPair.length === 2) {
                var key = decodeURIComponent(kvPair[0]);
                var newValue = decodeURIComponent(kvPair[1]);
                if (newValue === "" && omitEmpty) {
                    continue;
                }
                if (key.slice(-2) === "[]") {
                    key = key.replace("[]", "");
                    target[key] = target[key] || [];
                    target[key].push(newValue);
                }
                else {
                    target[key] = newValue;
                }
            }
        }
        return target;
    }


    function serialize(obj, encode, omitEmpty) {
        if (encode === void 0) {
            encode = true;
        }
        if (omitEmpty === void 0) {
            omitEmpty = true;
        }
        var params = [];
        for (var key in obj) {
            var value = obj[key];
            if (value !== undefined) {
                if (Array.isArray(value)) {
                    for (var index in value) {
                        var item = value[index];
                        if (omitEmpty && item === undefined) {
                            continue;
                        }
                        if (encode) {
                            params.push(encodeURIComponent(key) + "[]=" + encodeURIComponent(item));
                        }
                        else {
                            params.push(key + "[]=" + item);
                        }
                    }
                }
                else {
                    if (omitEmpty && value === undefined) {
                        continue;
                    }
                    if (encode) {
                        params.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
                    }
                    else {
                        params.push(key + "=" + value);
                    }
                }
            }
        }
        if (params.length)
            return params.join("&");
        return "";
    }

</script>
</html>
