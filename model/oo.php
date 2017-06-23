<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/5/2
 * Time: 18:31
 */
class oo{
    static $opt = array();

    /**
     * @return db
     */
    static function omysql(){
        if(!is_object(self::$opt['db'])){
            include_once MOD.'db.php';
            self::$opt['db'] = new db();
        }
        return self::$opt['db'];
    }

    /**
     * @return tcp
     */
    static function tcp(){
        if(!is_object(self::$opt['tcp'])){
            include_once MOD.'tcp.php';
            self::$opt['tcp'] = new tcp();
        }
        return self::$opt['tcp'];
    }

    /**
     * @return main
     */
    static function main(){
        if(!is_object(self::$opt['main'])){
            include_once MOD.'main.php';
            self::$opt['main'] = new main();
        }
        return self::$opt['main'];
    }

    /**
     * @return work
     */
    static function work(){
        if(!is_object(self::$opt['work'])){
            include_once MOD.'work.php';
            self::$opt['work'] = new work();
        }
        return self::$opt['work'];
    }

}