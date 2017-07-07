<?php

namespace library\cache;

use library\module\logClient;
/**
 * Redis操作类
 *
 * @author JsonChen
 */
class redis {

    private $ip, $port, $timeout, $oRedis, $seria;

    public function __construct($ip, $port, $timeout, $seria = true) {
        $this->ip = $ip;
        $this->port = intval($port);
        $this->timeout = $timeout;
        $this->seria = $seria;
    }

    private $connected = false;

    private function log($ex) {
        $content = "ip:{$this->ip},port:{$this->port}-";
        $content .= $ex->getTraceAsString() . ',msg:' . $ex->getMessage();
        logClient::log($ex, "redis_exception");
    }

    private function connect() {
        if ($this->connected) {
            return true;
        }
        if (!$this->oRedis) {
            $this->oRedis = new Redis();
        }
        try {
            $ret = $this->oRedis->connect($this->ip, $this->port, $this->timeout);
            if (!$ret) {
                return false;
            }
            $this->oRedis->setOption(Redis::OPT_SERIALIZER, $this->seria ? Redis::SERIALIZER_PHP : Redis::SERIALIZER_NONE);
            $this->connected = true;
            return true;
        } catch (\Exception $e) {
            //var_dump($e);
        }
        return false;
    }

    /**
     * List章节 无索引序列 把元素加入到队列左边(头部).如果不存在则创建一个队列.返回该队列当前元素个数/false
     * 注意对值的匹配要考虑到serialize.array(1,2)和array(2,1)是不同的值
     * @param String $key
     * @param Mixed $value
     * @return false/Int. 如果连接不上或者该key已经存在且不是一个队列
     */
    public function lPush($key, $value) {
        try {
            if (!$this->connect()) {
                return false;
            }
            return $this->oRedis->lPush($key, $value);
        } catch (\Exception $ex) {
            $this->connected = false;
            $this->log($ex);
            return false;
        }
    }

    public function lPop($key) {
        try {
            if (!$this->connect()) {
                return false;
            }
            return $this->oRedis->lPop($key);
        } catch (\Exception $ex) {
            $this->log($ex);
            $this->connected = false;
            return false;
        }
    }

    /**
     * 有序集合添加
     * @param type $key
     * @param type $index
     * @param type $value
     * @return type
     */
    public function zAdd($key, $score, $value, $try_cnt = 1) {
        for ($try = 0; $try < $try_cnt; $try++) {
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->zAdd($key, $score, $value);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return false;
    }

    /**
     * 获取有序集合数据
     * @param type $key
     * @param type $start
     * @param type $end
     * @return boolean
     */
    public function zRange($key, $start, $end) {
        try {
            if (!$this->connect()) {
                return false;
            }
            return $this->oRedis->zRange($key, $start, $end, true);
        } catch (\Exception $ex) {
            $this->log($ex);
            $this->connected = false;
            return false;
        }
    }

    /**
     * 获取有序集合数量
     * @param type $key
     * @param type $start
     * @param type $end
     * @return boolean
     */
    public function zCount($key, $start, $end) {
        try {
            if (!$this->connect()) {
                return false;
            }
            return $this->oRedis->zCount($key, $start, $end);
        } catch (\Exception $ex) {
            $this->log($ex);
            $this->connected = false;
            return false;
        }
    }

    /**
     * 设置.有则覆盖.true成功false失败
     * @param String $key
     * @param Mixed $value
     * @param int $Timeout 过期时间(秒).最好用setex
     * @return Boolean
     */
    public function set($key, $value, $Timeout, $try_cnt = 1) {
        for ($try = 0; $try < $try_cnt; $try++) {
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->set($key, $value, (int) $Timeout);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return false;
    }

    /**
     * 获取.不存在则返回false
     * @param String $key
     * @return false/Mixed
     */
    public function get($key, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->get($key);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return false;
    }

    /**
     * 先获取该key的值,然后以新值替换掉该key.该key不存在则添加同时返回false
     * @param String $key
     * @param Mixed $value
     * @return Mixed/false
     */
    public function getSet($key, $value, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->getSet($key, $value);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 给该key添加一个唯一值.相当于制作一个没有重复值的数组
     * @param String $key
     * @param Mixed $value
     * @return false/int 该值存在或者该键不是一个集合返回0,连接失败为false,否则为添加成功的个数1
     */
    public function sAdd($key, $value, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) {
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->sAdd($key, $value);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 删除该集合中对应的值 
     * @param String $key
     * @param String $value
     * @return Boolean 没有该值返回false
     */
    public function sRemove($key, $value, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->sRemove($key, $value);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 把某个值从一个key转移到另一个key
     * @param String $srcKey
     * @param String $dstKey
     * @param Mixed $value
     * @return Boolean 源key不存在/目的key不存在/源值不存在->false
     */
    public function sMove($srcKey, $dstKey, $value) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->sMove($srcKey, $dstKey, $value);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 返回所给key列表所有的值,相当于求并集
     * @param Array $keys
     * @return Array
     */
    public function sUnion($keys, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->sUnion($keys);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 返回所给key列表所有的值,
     * @param Array $keys
     * @return Array
     */
    public function sMembers($key, $try_cnt = 1) {
        for ($retry = 0; $retry < $try_cnt; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->sMembers($key);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return $res;
    }

    /**
     * 删除某key/某些key
     * @param String/Array $keys
     * @return int 被真实删除的个数
     */
    public function delete($keys, $try_cnt = 1) {
        for ($retry = 0; $retry < 2; $retry++) { //重试两次
            try {
                if (!$this->connect()) {
                    continue;
                }
                return $this->oRedis->delete($keys);
            } catch (\Exception $ex) {
                $this->log($ex);
                $this->connected = false;
            }
        }
        return false;
    }

}
