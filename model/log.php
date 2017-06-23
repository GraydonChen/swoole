<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/6/23
 * Time: 11:53
 */
class log{
    public static function setLog($log, $name){
        clearstatcache();
        $file = SERVER_ROOT."log/{$name}.txt";
        $dir = dirname($file);
        if(!is_dir($dir)) mkdir( $dir, 0775, true );
        $content = file_exists($file) ? "" : "";
        $content = date("Y:m:d H:i:s") . $content . json_encode($log)."\n";
        ile_put_contents($file, $content, FILE_APPEND);
    }

    public static function getLog(){

    }
}
