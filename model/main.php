<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/26
 * Time: 17:49
 */
class main{
    static $opt = array();
    public $server;

    public function onWorkerStart($serv, $work_id){
        include_once MOD.'tick.php';
        //读取定时配置
        $cfg = include_once CONFIG.'crontab.php';
        //开启定时任务
        if($work_id == 0){
            //tick::start($serv, 10000, $work_id, $cfg);
        }
    }

    //处理tcp请求
    public function onReceive($serv, $fd, $packet_buff){
        //分配给task 进程处理
        $this->server = $serv;
        $method = "tcp_" . $packet_buff;
        if(method_exists(oo::tcp(),$method)){
            oo::tcp()->$method($packet_buff,0);
        }
        //$serv->task($packet_buff, 0);
        //oo::tcp()->receive($fd, $packet_buff);

    }

    //处理udp请求
    public function onPacket($server, $data, $client_info){

    }

    public function onTask($serv, $task_id, $from_id, $data){
        if(method_exists(oo::work(), $data['method'])){
            $method = $data['method'];
            oo::work()->$method($data['data']);
        }
    }

    public function onClose($fd){

    }

    public function onMessage($server, $frame){
        $this->server = $server;
        var_dump($frame->data);
        oo::tcp()->sendAll($frame->data);
    }

    public function onOpen($server, $frame){
        $Connects = $server->connection_list($start_fd = 0, $pagesize = 100);
        $onLineNum = count($Connects);
        foreach ($Connects as $per){
            $server->push($per, $onLineNum);
        }
    }

}
