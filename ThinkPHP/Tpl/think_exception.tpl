<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>错误</title>
</head>
<body>
<div style="width: 100%;height: 1000px;position: fixed;top: 0;left: 0;background-color: rgba(0,0,0,0.35);">
    <div style="width: 320px;height: 180px;border-radius:3px;overflow:hidden;margin: auto;position: fixed;top: 0;left: 0;right: 0;bottom: 0;background-color: #fff;box-shadow: 0 0 15px #999;">
        <div style="width: 100%;height:50px;line-height: 50px;background-color: #cfcfcf;">
            <label style="margin-left:10px;color:#666;">错误</label>

            <div style="clear:both;"></div>
        </div>
        <div style="padding: 15px 10px 0;line-height: 30px;">
            <p style="background: url('/Public/pc/img/success.png') no-repeat 0 -2px;padding-left: 40px;">

                <label class="success">
                    <!--<?php echo strip_tags($e['message']);?>-->
                    提示：发生错误！
                </label>
            </p>
            <p style="text-align: right;margin-top: 20px;font-size: 12px;">
                <a id="href" href="javascript:history.back();" style="display: inline-block;width: 80px;height: 30px;background-color: #888;color:#fff;text-align: center;text-decoration: none;">返回</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
