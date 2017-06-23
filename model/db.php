<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/5/2
 * Time: 12:19
 */
class db
{
    private $dbCon;

    public function __construct()
    {
        $this->connect();
    }

    public function connect()
    {
        if (is_object($this->dbCon)) {
            var_dump($this->dbCon);
            return $this->dbCon;
        }
        $dsn = "mysql:dbname=sprider;host=172.20.15.12";
        $db_user = 'root';
        $db_pass = '123456';
        try {
            $this->dbCon = new PDO($dsn, $db_user, $db_pass);
        } catch (PDOException $e) {
            echo '数据库连接失败' . $e->getMessage();
        }
        var_dump($this->dbCon);
    }

    //查询数据
    public function getALL($table, $condition = '', $sort = '', $page = '', $field = '*', $debug = false)
    {
        $rs = $this->dbCon->query("select * from {$table}");
        $result_arr = $rs->fetchAll();
        return $result_arr;
    }

    public function delete($table){
        $sql="delete from {$table} where id > 250";
        $res=$this->dbCon->exec($sql);
        return $res;
    }

}