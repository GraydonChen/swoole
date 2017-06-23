<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/5/10
 * Time: 12:26
 */

//处理work进程的业务
class work{
    private $res = array();
    public function task($packet_buff, $task_id){
        echo $task_id."first\n";
        $task_id = oo::main()->server->task($packet_buff);
        echo $task_id ;
    }

    /**
     * @param $data
     * 纯输出，调试专用
     */
    public function _echo($data){
        $this->res[] = $data;
        print_r($this->res);
    }

    public function _analysisCard($data){

    }

}