<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/26
 * Time: 17:29
 */
include_once SERVER_ROOT . 'config/load.php';
include_once SERVER_ROOT . 'model/log.php';
class hooBehavior{

    /**
     * 处理TCP协议
     * @param type $server
     * @param type $fd
     * @param type $from_id
     * @param type $packet_buff
     * @throws Exception
     */
    public function onReceive($server, $fd, $from_id, $packet_buff){
        try {
            oo::main()->onReceive($server, $fd, $packet_buff);
        } catch (Throwable $ex){
            $aErr = array('onReceive' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    /**
     * 处理Task异步任务
     * @param type $serv
     * @param type $task_id
     * @param type $from_id
     * @param type $data
     */
    public function onTask($serv, $task_id, $from_id, $data){
        try {
            oo::main()->onTask($serv, $task_id, $from_id, $data);
        }catch (Throwable $ex){
            $aErr = array('onTask' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    /**
     * Work/Task进程启动
     * @global type $config
     * @param type $serv
     * @param type $worker_id
     */
    public function onWorkerStart($serv, $worker_id){
        try {
            oo::main()->onWorkerStart($serv, $worker_id);
        }catch (Throwable $ex){
            $aErr = array('onWorkerStart' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    public function onPipeMessage($serv, $from_worker_id, $message){
        try {
            Main::onPipeMessage($from_worker_id, $message);
        }catch (Throwable $ex){
            $aErr = array('onPipeMessage' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    /**
     * 断开连接
     * @param type $serv
     * @param type $fd
     * @param type $from_id
     */
    public function onClose($serv, $fd, $from_id){
        try {
            Main::onClose($fd);
        }catch (Throwable $ex){
            $aErr = array('onClose' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }
    /**
     * 停止了
     */
    public function onWorkerStop($server, $worker_id){
        try {
            Main::onWorkerStop();
        }catch (Throwable $ex){
            $aErr = array('onWorkerStop' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    public function onConnect($server, $fd, $from_fd){
        try {

        }catch (Throwable $ex){
            $aErr = array('onWorkerStop' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    public function onMessage($server, $frame){
        try {
            oo::main()->onMessage($server, $frame);
        }catch (Throwable $ex){
            $aErr = array('onWorkerStop' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

    public function onOpen($server, $frame){
        try {
            echo $frame->fd;
            //记录登陆的用户都
            global $peopleOnLine;
            $peopleOnLine->set($frame->fd, array('fd' => $frame->fd));
            oo::main()->onOpen($server, $frame);
            //var_dump($peopleOnLine);
        }catch (Throwable $ex){
            $aErr = array('onWorkerStop' ,$ex->getMessage(), $ex->getFile() . ' on line:' . $ex->getLine());
            log::setLog($aErr , 'exErr');
        }
    }

}
