<?php
/**
 * 小喇叭客户端通信类
 */
class EServerClient {

	private $svip, $svport;
	private $eserverClient, $connectedTime;

	public function __construct($ip,$port) {
		$this->connectedTime =0;
		$this->svip = $ip;
		$this->svport = $port;
	}

	private function connect() {
		//小喇叭服务30秒内会切断连接
		if((Time()-$this->connectedTime)>30){
			$this->connected = time();
			$this->eserverClient = ByPackClient::CreateClientAndConnect($this->svip, $this->svport);
		}
		return $this->eserverClient;		
	}

	/**
	 * Udp方式发送
	 * @param WritePackage
	 */
	private function sendUdp($package) {
		$package->WriteEnd();
		return ByPackClient::SendByUdpSocket($this->svip, $this->svport, $package->GetPacketBuffer());
		
	}

	/**
	 * 向客户端发消息
	 * @param type $msg
	 * @param type $target 2:全部，0：针对移动 1:pc
	 * @return type
	 */
	public function sendMsg($msg,$target = 2) {		
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x104);
		$wr->WriteShort(intval($target));
		$wr->WriteString($msg);
		return $this->sendUdp($wr);
	}

	//指定用户发JS广播
	public function sendJsMsg($mid, $msg) {
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x10E);
		$wr->WriteInt($mid);
		$wr->WriteString('cade073b2c1b6612db735a41c11853f4');
		$wr->WriteString(rawurlencode($msg));
		return $this->sendUdp($wr);
	}
	
	//全服发JS广播
	public function sendJsMsgAll($msg) {
		$data = array(
			'type' => 100,
			'test' => PRODUCTION_SERVER ? 0 : 1,
			'js' => rawurlencode($msg),
		);
		$this->sendMsg(json_encode($data),1);
		
	}

	/**
	 * 对全桌玩家发送JS推送
	 *
	 * @param Int $tid 桌子ID
	 * @param Json $msg 消息，js代码
	 * @param Int $stat 过滤状态，0x888
	 * @return int
	 */
	public function sendJsMsgByTid($tid, $msg, $stat = -1) {
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x10F);
		$wr->WriteInt($tid);
		$wr->WriteString('c801792bc8959b4842f526e8dc11b322');
		$wr->WriteShort($stat);
		$wr->WriteString(rawurlencode($msg));
		return $this->sendUdp($wr);
	}

	/**
	 * 批量获取指定mid的在线状态(每次不超过1000个ID)
	 *
	 * @param array $aMids
	 * @return array 格式 array(mid=>stat),stat:0=离线，1=大厅，2=旁观，3=在玩
	 */
	public function getUsersStat(array $aMids = array()) {
		if (empty($aMids)) {
			return array();
		}
		$this->connect();
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x110);
		$iCnt = 0;
		foreach ($aMids as $mid) {
			if (($mid = intval($mid)) > 0) {
				$wr->writeInt($mid);
				$iCnt++;
			}
			if ($iCnt > 999) {
				break;
			}
		}
		$wr->WriteEnd();
		$readPackage = ByPackClient::SendAndReciveByClient($this->eserverClient, $wr->GetPacketBuffer());
		$aStats = array();
		if (!$readPackage) {
			return $aStats;
		}
		$iCnt=0;
		while ($readPackage->GetLen() >= 5) {
			$mid = intval($readPackage->ReadInt());
			if ($iCnt > 999) {
				break;
			}
			if(!in_array($mid, $aMids)){
				continue;
			}
			$aStats[$mid] = intval($readPackage->ReadByte());
			$iCnt++;
		}
		return $aStats;
	}
	/**
	 * 添加好友
	 * @param type $fmid
	 * @param type $tmid
	 * @param type $msg
	 * @param type $large
	 * @param type $isUdp
	 * @return type
	 */
	public function sendSingleMsg($fmid, $tmid, $msg) {
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x103);

		$wr->WriteInt($fmid);
		$wr->WriteInt($tmid);

		$wr->WriteString($msg);
		return $this->sendUdp($wr);
	}

	/**
	 * 
	 * @param type $tid 对应的桌子id
	 * @param type $type 1获取坐下在玩的玩家 2旁观 3所有
	 */
	public function getUserStatByTid($tid, $type = 3) {
		$tid = functions::uint($tid);
		$type = functions::uint($type);
		$ret = array();

		if (!$tid || !in_array($type, array(1, 2, 3))) {
			return $ret;
		}
		if(!$this->connect()){			
			return $ret;
		}
		$wr = new WritePackage(true);
		$wr->WriteBegin(0x887); //
		$wr->WriteShort($type);
		$wr->WriteInt($tid);
		$wr->WriteEnd();
		$readPackage = ByPackClient::SendAndReciveByClient($this->eserverClient, $wr->GetPacketBuffer());
		if ($readPackage) {
			$data = $readPackage->ReadString();
			$ret = json_decode($data, true);
		}
		return $ret;
	}

	/**
	 * 
	 * @param type $tid 对应的桌子id
	 * @param type $type 1获取坐下在玩的玩家 2旁观 3所有
	 */
	public function getUserStatByTidMulit($tids, $type = 3) {
		$tids = is_array($tid) ? $tids : (array) $tids;
		$type = functions::uint($type);
		$ret = array();
		if (empty($tids) || !in_array($type, array(1, 2, 3))) {
			return $ret;
		}		
		if(!$this->connect()){			
			return $ret;
		}

		$wr = new WritePackage(true);
		$wr->WriteBegin(0x886); 
		$wr->WriteShort($type);
		foreach ($tids as $tid){
			$wr->WriteInt($tid);
		}
		$wr->WriteEnd();
		$readPackage = ByPackClient::SendAndReciveByClient($this->eserverClient, $wr->GetPacketBuffer());
		if ($readPackage) {
			$data = $readPackage->ReadString();
			$ret = json_decode($data, true);
		}
		return $ret;
	}

	/**
	 * 请求在线用户数
	 */
	public function getCount($onlyNumber = true) {
		$ret = 0;
		
		if(!$this->connect()){			
			return $ret;
		}
		$wr=new WritePackage(true);
		$wr->WriteBegin(0x109);
		$wr->WriteEnd();
		$readPackage = ByPackClient::SendAndReciveByClient($this->eserverClient, $wr->GetPacketBuffer());
		if($readPackage){
			$ret = $readPackage->ReadInt();
		}
		return $ret;		
	}

	/**
	 * 获取server运行时系统信息
	 */
	public function getSysInfo() {
		$ret = '';
		
		if(!$this->connect()){			
			return $ret;
		}
		$wr=new WritePackage(true);
		$wr->WriteBegin(0x888);
		$wr->WriteString("f35537b335a767c5b60d76863daff7af");
		$wr->WriteEnd();
		$readPackage = ByPackClient::SendAndReciveByClient($this->eserverClient, $wr->GetPacketBuffer());
		if($readPackage){
			$ret = $readPackage->ReadString();
		}
		return $ret;
	}

}
