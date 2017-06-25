<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/26
 * Time: 17:26 
 */
define('SERVER_ROOT', dirname(__FILE__).'/');
define('CONFIG',SERVER_ROOT.'config/');
define('MOD',SERVER_ROOT.'model/');
define('LIB',SERVER_ROOT.'Lib/');

$prot = $argv[1];
include_once LIB.'SwooleService.php';

$SwooleConfig = include_once CONFIG.'swoole.php';
$SwooleConfig['Port'] = $prot;
$SwooleConfig['SocketType'] = SWOOLE_SOCK_TCP; //创建tcp连接
$SwooleConfig['Behavior'] = array('hooBehavior',SERVER_ROOT. 'hooBehavior.php');
$hooService = new SwooleService($SwooleConfig);

/**
 * 在线用户
 */
global $peopleOnLine;
$peopleOnLine = new swoole_table(1024);
$peopleOnLine->column('fd', swoole_table::TYPE_INT, 4);
$peopleOnLine->create();

$hooService->Start();
