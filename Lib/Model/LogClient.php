<?php

/**
 * 日志上传类
 */
class LogClient {

	private $udpIp, $udpPort;

	public function __construct($udpIp, $udpPort) {
		$this->udpIp = $udpIp;
		$this->udpPort = $udpPort;
	}

	public function debug($params, $fname = 'debug.txt', $fsize = 1) {
		is_scalar($params) or ( $params = var_export($params, true)); //是简单数据
		if (!$params) {
			return false;
		}
		if (defined('TSWOOLE_SID')) {
			$fname = TSWOOLE_SID . '_' . $fname;
		}
		$udp = array($fname, max(1, $fsize) * 1024 * 1024, $params);
		$content = implode('+_+', $udp);
		//超出大小，直接丢掉
		if (strlen($content) > 65500) {
			return false;
		}
		$sSocket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		@socket_sendto($sSocket, $content, strlen($content), 0, $this->udpIp, $this->udpPort);
		return true;
	}

}
