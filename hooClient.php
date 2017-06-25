<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/5/3
 * Time: 10:03
 */
$client = new swoole_client(SWOOLE_SOCK_TCP );
if (!$client->connect('127.0.0.1', 9501, 1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}

$data = new swoole_buffer(1024);
//$data ->append();
$client->send("0x104");

//$client->recv();
//$client->close();
