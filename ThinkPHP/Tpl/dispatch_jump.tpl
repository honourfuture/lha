<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no,target-densitydpi = medium-dpi">
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
    </style>
</head>
<body>
<div style="width: 100%;height: 1000px;position: fixed;top: 0;left: 0;background-color: rgba(0,0,0,0.35);">
    <div style="width: 320px;height: 180px;border-radius:3px;overflow:hidden;margin: auto;position: fixed;top: 0;left: 0;right: 0;bottom: 0;background-color: #fff;box-shadow: 0 0 15px #999;">
        <div style="width: 100%;height:50px;line-height: 50px;background-color: #cfcfcf;">
            <label style="margin-left:10px;color:#666;">温馨提示</label>
            <label style="font-size: 12px;color:#888;float:right;display: block;margin-right: 10px;"><b id="wait"><?php echo($waitSecond); ?></b>秒后跳转</label>

            <div style="clear:both;"></div>
        </div>
        <div style="padding: 25px 10px;line-height: 30px;">
            <p style="background: url('__PUBLIC__/pc/img/success.png') no-repeat 0 -2px;;height: 32px;padding-left: 40px;">
                <?php if(isset($message)) {?>
                <label class="success"><?php echo($message); ?></label>
                <?php }else{?>
                <label class="error"><?php echo($error); ?></label>
                <?php }?>
                </label>
            <p style="text-align: right;margin-top: 20px;font-size: 12px;">
                <a id="href" href="<?php echo($jumpUrl); ?>" style="display: inline-block;width: 80px;height: 30px;background-color: #888;color:#fff;text-align: center;text-decoration: none;">确定</a>
            </p>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
