<?php

class WritePackage extends SocketPackage {

	public function WriteBegin($CmdType) {
		$this->CmdType = $CmdType;
		if(!$this->m_packetBuffer){
			$this->m_packetBuffer = new swoole_buffer(1024);
		}else{
			$this->m_packetBuffer->clear();
		}
		$this->m_packetSize =0;
	}

	public function GetPacketBuffer() {
		return $this->m_packetBuffer->read(0, $this->m_packetSize);
	}

	public function WriteEnd() {
		$tmp = "";
		if ($this->m_packetSize) {
			$tmp = $this->m_packetBuffer->read(0, $this->m_packetSize);
			$this->m_packetBuffer->clear();
			if ($this->m_Encrypt) {
				$EncryptObj = new SwooleEncryptDecrypt();
				$EncryptObj->EncryptBuffer($tmp, 0, $this->m_packetSize);
			}
		}
		$head = pack("n", $this->m_packetSize + 5);
		$this->m_packetBuffer->append($head);
		$this->m_packetBuffer->append("BY");
		$this->m_packetBuffer->append(pack("c", self::SERVER_PACEKTVER));  //ver
		$this->m_packetSize = $this->m_packetBuffer->append(pack("n", $this->CmdType));   //cmd
		if ($tmp) {
			$this->m_packetSize = $this->m_packetBuffer->append($tmp);
		}
	}

	public function WriteInt($value) {
		$this->m_packetSize = $this->m_packetBuffer->append(pack("N", $value));
	}

	public function WriteByte($value) {
		$this->m_packetSize = $this->m_packetBuffer->append(pack("C", $value));
	}

	public function WriteShort($value) {
		$this->m_packetSize = $this->m_packetBuffer->append(pack("n", $value));
	}

	public function WriteString($value) {
		$len = strlen($value) + 1;
		$this->m_packetBuffer->append(pack("N", $len));
		$this->m_packetBuffer->append($value);
		$this->m_packetSize = $this->m_packetBuffer->append(pack("C", 0));
	}

}
