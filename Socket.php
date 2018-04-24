<?php

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server('0.0.0.0', 9502);
$ws->redis = new Redis();
$ws->redis->connect('127.0.0.1');

/*
 * 监听WebSocket连接打开事件
 */
$ws->on('open', function ($ws, $request) {
    if (1 == $request->fd) {
        $ws->redis->set('str', '');
    }
    $str = $ws->redis->get('str');
    if (empty($str)) {
        $ws->redis->set('str', $request->fd.';');
    } else {
        $ws->redis->set('str', $str.$request->fd.';');
    }
    $ws->push($request->fd, "hello, welcome\n");
    echo '用户'.$request->fd.",进入\n";
});

/*
 * 监听WebSocket消息事件
 */
$ws->on('message', function ($ws, $frame) {
    $str = $ws->redis->get('str');
    $Arr = explodeStr($str);
    $msg = '【用户'.$frame->fd."】说:{$frame->data}\n";
    foreach ($Arr as $v) {
        echo '【用户'.$frame->fd.'】广播给【用户'.$v.'】:'.$msg."\n";
        $re = $ws->push(intval($v), $msg);
    }
});

/*
 * 监听WebSocket连接关闭事件
 * 删除已断开的客户端
 */
$ws->on('close', function ($ws, $fd) {
    $str = $ws->redis->get('str');
    $Arr = explodeStr($str);
    if (empty($Arr)) {
        echo '用户'.$fd."退出\n无用户\n";
    } else {
        $string = '';
        foreach ($Arr as $k => $v) {
            if ($v == $fd) {
                unset($Arr[$k]);
            } else {
                $string = $string.$v.';';
                $ws->redis->set('str', $string);
            }
        }
        echo '用户'.$fd."退出\n";
    }
});

$ws->start();

/**
 * 将字符串处理成数组.
 *
 * @param $str
 *
 * @return array
 */
function explodeStr($str)
{
    $str = rtrim($str, ';');
    $Arr = explode(';', $str);

    return $Arr;
}
