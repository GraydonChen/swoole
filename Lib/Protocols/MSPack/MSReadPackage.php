<?php

/**
 * Description of ReadPackage
 *
 * @author JsonChen
 */
class MSReadPackage extends MSSocketPackage {

	private $m_Offset=0;
	public function ReadPackageBuffer($packet_buff) {
		$this->realpacket_buff = $packet_buff;
		if (!$this->m_packetBuffer) {
			$this->m_packetBuffer = new swoole_buffer(65537);
		} else {
			$this->m_packetBuffer->clear();
		}		
		$this->package_realsize = $this->m_packetBuffer->append($packet_buff);
		if ($this->package_realsize < self::PACKET_HEADER_SIZE) {
			//包头为9个字节
			//throw new Exception("非法包,包过小");
			return -1;
		}
		if ($this->package_realsize > self::PACKET_BUFFER_SIZE) {
			//包长度为2个字节，包内容最多65535个字节
			//throw new Exception("非法包,包过大");
			return -2;
		}
		$headerInfo = unpack("c2Iden/sCmdType/cVer/cSubVer/sLen/cCode", $this->m_packetBuffer->read(0, 9));	
		if ($headerInfo['Len']>=0 && $headerInfo['Len'] !=$this->package_realsize-self::PACKET_HEADER_SIZE ) {
			//throw new Exception("非法包头,-1");
			return -3;
		}
		if ($headerInfo['Iden1'] != ord('I') || $headerInfo['Iden2'] != ord('C')) {
			//throw new Exception("非法包头,-1");
			return -4;
		}
		if ($headerInfo['Ver'] != self::SERVER_PACEKTVER) {
			//throw new Exception("非法包头,-2");
			//return -5;
		}
		if ($headerInfo['CmdType'] <= 0 || $headerInfo['CmdType'] >= 32000) {
			//throw new Exception("非法包头,-3");
			return -6;
		}
		$this->CmdType = $headerInfo['CmdType'];
		$this->m_packetSize = $headerInfo['Len'];
		if ($this->m_packetSize) {
			$packetBuffer = $this->m_packetBuffer->read(self::PACKET_HEADER_SIZE, $this->m_packetSize);
			$DecryptObj = new CServerEncryptDecrypt();
			$DecryptObj->DecryptBuffer($packetBuffer, $this->m_packetSize, $headerInfo['Code']);
			$this->m_packetBuffer->write(self::PACKET_HEADER_SIZE, $packetBuffer);
		}
		$this->m_Offset = self::PACKET_HEADER_SIZE;
		return 1;
	}

	public function GetPacketBuffer() {
		return $this->realpacket_buff;
	}

	public function GetLen() {
		return $this->package_realsize - $this->m_Offset;
	}

	public function ReadByte() {
		if($this->package_realsize<=$this->m_Offset){
			return false;
		}
		$temp = $this->m_packetBuffer->read($this->m_Offset, 1);
		if ($temp === false) {
			return false;
		}
		$value = unpack("C", $temp);
		$this->m_Offset+=1;
		return $value[1];
	}

	public function ReadShort() {
		if($this->package_realsize<=$this->m_Offset){
			return false;
		}
		$temp = $this->m_packetBuffer->read($this->m_Offset, 2);
		if ($temp === false) {
			return false;
		}
		$value = unpack("s", $temp);
		$this->m_Offset+=2;
		return $value[1];
	}

	public function ReadInt(){
		if($this->package_realsize<=$this->m_Offset){
			return false;
		}
		$temp = $this->m_packetBuffer->read($this->m_Offset, 4);
		if ($temp === false) {
			return false;
		}
		$value = unpack("i", $temp);
		$this->m_Offset+=4;
		return $value[1];
	}
	
	public function ReadUInt(){
		if($this->package_realsize<=$this->m_Offset){
			return false;
		}
		$temp = $this->m_packetBuffer->read($this->m_Offset, 4);
		if ($temp === false) {
			return false;
		}
		list(,$var_unsigned)= unpack("L", $temp);
		$this->m_Offset+=4;
		return floatval(sprintf("%u",$var_unsigned));
	}

	public function ReadString() {
		if($this->package_realsize<=$this->m_Offset){
			return false;
		}
		$len = $this->ReadInt();
		if ($len === false) {
			return false;
		}
		$realLen = $this->m_packetBuffer->length - $this->m_Offset;
		if ($realLen < $len - 1) {
			return false;
		}
		$value = $this->m_packetBuffer->read($this->m_Offset, $len - 1);
		$this->m_Offset+=$len;
		return $value;
	}

}
