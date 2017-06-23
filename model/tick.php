
<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/27
 * Time: 11:12
 */

class tick{

    public static function start($serv, $time, $work_id, $cfg){
        $serv->tick($time, function() use ($serv, $work_id, $cfg) {
            //$res = oo::omysql()->getALL('sprider');
            //处理过期数据
            $res = oo::omysql()->delete('sprider');
            var_dump($res);
            echo $work_id."start".time()."\n";
        });
    }

    private function tick($serv, $time){
        $serv->tick($time, function() use ($serv) {
            echo "start\n";
        });
    }
}