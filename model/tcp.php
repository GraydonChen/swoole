<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/27
 * Time: 12:11
 */
class tcp{
    private $aUser = array();
    public function receive($fd, $packet_buff){
        $this->aUser[] = $packet_buff;
        var_dump($this->aUser);
        echo 'aaaaaaaaaaaaaaaaa';
        echo $fd."\n";
    }

    /**
     * 输出
     */
    public function tcp_0x104($data){
        $data = array('method' => '_echo', 'data' => $data);
        oo::work()->task($data,0);
    }

    /**
     * 重启swoole
     */
    public function tcp_0x105(){
       $res = oo::main()->server->reload();
       var_dump($res);
    }

    /**
     * 广播所有人
     */
    public function tcp_0x106($data){
        $Connects = oo::main()->server->connection_list($start_fd = 0, $pagesize = 100);
        foreach ($Connects as $per){
            oo::main()->server->push($per, $data);
        }
    }

    /**
     * @param $data
     * 分析牌型功能
     */
    public function tcp_0x201($data){
        oo::work()->task($data,1);
    }

}