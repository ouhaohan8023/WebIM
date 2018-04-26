<?php

/**
 * PHP进程可以创建一个websocket客户端与WebSocket服务器通信，发送消息转发的指令。
 */
require_once __DIR__ . '/WebSocketClient.php';

$client = new WebSocketClient('100.100.0.223', 9502);

if (!$client->connect())
{
    echo "connect failed \n";
    return false;
}

$send_data = "I am client.";
if (!$client->send($send_data))
{
    echo $send_data. " send failed \n";
    return false;
}

echo "send succ \n";
return true;