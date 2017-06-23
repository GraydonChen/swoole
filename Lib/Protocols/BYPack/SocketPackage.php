<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 适用于Tcp/UDP之间传递包读写
 *
 * @author JsonChen
 */
abstract class SocketPackage {

	const SERVER_PACEKTVER = 2;
	const SERVER_SUBPACKETVER = 1;
	const PACKET_BUFFER_SIZE = 8192;
	const PACKET_HEADER_SIZE = 7;

	/**
	 * @var swoole_buffer 
	 */
	protected $m_packetBuffer;
	protected $m_packetSize = 0;
	/**
	 *整型cmdtype，用于封包
	 * @var type 
	 */
	public $CmdType;
	protected $m_Encrypt;

	abstract function GetPacketBuffer();

	public function __construct($m_Encrypt = false) {
		$this->m_Encrypt = $m_Encrypt;
	}

	public function GetPacketSize() {
		return $this->m_packetSize;
	}
	/**
	 * 返回字符型16进制，如 '0x101'
	 * @return type
	 */
	public function GetCmdType() {
		return '0x' . dechex($this->CmdType);
	}

}
