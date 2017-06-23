<?php

/**
 * 小喇叭包协议格式客户端解析公共类
 *
 * @author JsonChen
 */
class ByPackClient {

	/**
	 * 创建SwooleClient并建立连接
	 * @param type $ip
	 * @param type $port
	 * @param type $timeOut
	 */
	public static function CreateClientAndConnect($ip, $port, $timeOut = 1) {
		$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
		$client->set(array(
			'open_length_check' => 1,
			'package_length_type' => 'n',
			'package_length_offset' => 0, //第N个字节是包长度的值
			'package_body_offset' => 2, //第几个字节开始计算长度
			'package_max_length' => 80000, //协议最大长度
		));
		$result = $client->connect($ip, $port, $timeOut);
		if ($result) {
			return $client;
		}
		return false;
	}

	/**
	 * 发送并接收数据
	 * @param type $swoole_client
	 * @param type $tcpData
	 * @return \ReadPackageExt 当===false时，表示出现协议问题
	 */
	public static function SendAndReciveByClient($swoole_client, $tcpData) {
		$swoole_client->send($tcpData);
		$responseData = $swoole_client->recv();
		$readPackage = new ReadPackageExt();
		$ret=$readPackage->ReadPackageBuffer($responseData);
		if($ret!=1){
			return false;
		}
		return $readPackage;
	}

	/**
	 * 创建连接并发送数据
	 * @param type $ip
	 * @param type $port
	 * @param type $tcpData
	 * @param type $timeOut
	 * @return ReadPackageExt  当===false时，表示出现协议问题
	 */
	public static function SendAndRecive($ip, $port, $tcpData, $timeOut = 1) {
		$client = self::CreateClientAndConnect($ip, $port, $timeOut);
		if ($client) {
			return self::SendAndReciveByClient($client, $tcpData);
		}
		return false;
	}
	/**
	 * 发送udp数据
	 * @param type $ip
	 * @param type $port
	 * @param type $package
	 */
	public static function SendByUdpSocket($ip, $port, $package) {
		$client = new swoole_client(SWOOLE_SOCK_UDP, SWOOLE_SOCK_SYNC);
		$client->connect($ip, $port);
		return $client->send($package);
	}

}
