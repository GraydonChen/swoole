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
abstract class CServerSocketPackage {

	abstract function GetPacketBuffer();

	public function __construct() {
	}

	public function GetPacketSize() {
		return $this->m_packetSize;
	}

	public function GetCmdType() {
		return '0x' . dechex($this->CmdType);
	}

}
