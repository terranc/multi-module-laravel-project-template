<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<h1>微信分享测试</h1>
</body>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $app->jssdk->buildConfig(array(
        "onMenuShareAppMessage",
        "onMenuShareTimeline",
        "updateAppMessageShareData",
        "updateTimelineShareData",
        "onMenuShareWeibo",
        "onMenuShareQZone"
    ), true) ?>);
</script>
</html>
