<?php

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server('0.0.0.0', 9502);
$ws->redis = new Redis();
$ws->redis->connect('127.0.0.1');
$i = 0; //定时任务重复次数，无意义
$ws->redis->set('PeopleNum', 0); //当前聊天室人数
/*
 * 监听WebSocket开始事件
 */
$ws->on('start', function ($ws) {
    //定时任务
//    swoole_timer_tick(1000, function ($timeId, $params) use (&$i) {
//        $i ++;
//        echo "hello, {$params} --- {$i}\n";
//        if ($i >= 5) {
//            swoole_timer_clear($timeId);
//        }
//    }, 'world');
    //推送到前端
});

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
    $toFront['text'] = "欢迎来到基佬论坛！\n";
    $jsonToFront = json_encode($toFront);
    $ws->push($request->fd, $jsonToFront);

    $num = $ws->redis->get('PeopleNum');
    ++$num;
    $ws->redis->set('PeopleNum', $num);

    $msg = '【用户'.$request->fd."】进入\n";
    sendToAll($request, $ws, $msg, 1);
    sendToAllNumber($ws, $num);
    echo $msg;
});

/*
 * 监听WebSocket消息事件
 */
$ws->on('message', function ($ws, $frame) {
    $msg = '【用户'.$frame->fd."】说:{$frame->data}\n";
    sendToAll($frame, $ws, $msg, 1);
//    foreach ($Arr as $v) {
//        echo '【用户'.$frame->fd.'】广播给【用户'.$v.'】:'.$msg."\n";
//        $re = $ws->push(intval($v), $msg);
//    }
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
            }
        }
        $ws->redis->set('str', $string);
        $msg = '【用户'.$fd."】退出\n";
        sendToAll($fd, $ws, $msg, 2);

        $num = $ws->redis->get('PeopleNum');
        --$num;
        $ws->redis->set('PeopleNum', $num);

        sendToAllNumber($ws, $num);
        echo $msg;
    }
});

$ws->start();

/**
 * 将字符串处理成数组.
 *
 * @param $str
 *
 * @return bool|string
 */
function explodeStr($str)
{
    if (2 == strlen($str)) {
        //只有一个数据 3;
        $Arr[] = substr($str, 0, 1);
//        print_r($Arr);
        return $Arr;
    } elseif (empty($str)) {
        $Arr = [];

        return $Arr;
    } else {
        $str = rtrim($str, ';');
        $Arr = explode(';', $str);

        return $Arr;
    }
}

/**
 * 群发给$Arr.
 */
function sendToAll($frame, $ws, $msg, $status)
{
    $str = $ws->redis->get('str');
    $Arr = explodeStr($str);
    if (1 == $status) {
        $id = $frame->fd;
    } else {
        $id = $frame;
    }
    foreach ($Arr as $v) {
        if ($id == $v) {
            echo '【用户'.$id.'】广播给【用户'.$v.'】:'.$msg;
            $toFront['text'] = '<b style="color: crimson">【我】'.$msg.'</b>';
            $jsonToFront = json_encode($toFront);
            $ws->push(intval($v), $jsonToFront);
        } else {
            echo '【用户'.$id.'】广播给【用户'.$v.'】:'.$msg;
            $toFront['text'] = $msg;
            $jsonToFront = json_encode($toFront);
            $ws->push(intval($v), $jsonToFront);
        }
    }
}

/**
 * 更新在线人数.
 *
 * @param $ws
 * @param $msg
 */
function sendToAllNumber($ws, $msg)
{
    $str = $ws->redis->get('str');
    $Arr = explodeStr($str);
//    print_r($Arr);
    foreach ($Arr as $v) {
        $toFront['ppp'] = $msg;
        $jsonToFront = json_encode($toFront);
//        echo $jsonToFront."\n";
//        echo intval($v);
        $ws->push(intval($v), $jsonToFront);
    }
}
