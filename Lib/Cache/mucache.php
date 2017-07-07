<?php

namespace library\cache;

class mucache {

    private $servers;
    private $connected = false;
    private $memcachedClient = false;

    const TRY_CNT = 3;

    public function __construct($servers) { //构造函数	
        $this->servers = $servers;
    }

    /**
     * 连接.
     * @return void
     */
    private function connect() {
        if (!$this->connected) {
            $this->connected = true;
            $this->memcachedClient = new \Memcached();
            $this->memcachedClient->setOption(Memcached::OPT_TCP_NODELAY, true); //启用tcp_nodelay
            $this->memcachedClient->setOption(Memcached::OPT_NO_BLOCK, true); //启用异步IO
            $this->memcachedClient->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT); //分布式策略
            $this->memcachedClient->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true); //分布式服务组分散.推荐开启 
            $this->memcachedClient->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC);  //Key分布
            $this->memcachedClient->addServers($this->servers);
        }
    }

    /**
     * 设置mem值 默认缓存24小时
     * @var key 键名,键值,过期时间,是否压缩. 后两个参数对Memcachedb透明
     * @return 成功为true,否则false
     */
    public function set($key, $value, $expire = 86400, $zip = true) {
        $this->connect();
        $this->memcachedClient->setOption(Memcached::OPT_COMPRESSION, $zip ? true : false);
        for ($try = 0; $try < self::TRY_CNT; $try++) { //确保每个组都保存成功,重试$this->try次
            $this->memcachedClient->set($key, $value, $expire);
            $resultCode = $this->memcachedClient->getResultCode();
            if ($resultCode == Memcached::RES_SUCCESS || $resultCode == Memcached::RES_END) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取单键 $zip 是否解压缩.对应set的压缩 
     * @var $inCas 是否进行cas验证 如果是则返回一个数组[结果集,cas字串] 注意不支持分组!!!
     * @return 成功返回结果,否则false
     */
    public function get($key, $zip = true) {
        $this->connect();
        $result = false; //连接不上为false
        for ($try = 0; $try < self::TRY_CNT; $try++) { //确保在没有系统错误的情况下执行
            $result = $this->memcachedClient->get($key);  //抑制错误,如不能正常解压
            $resultCode = $this->memcachedClient->getResultCode();
            if ((!$result) && ($resultCode != Memcached::RES_SUCCESS) && ($resultCode != Memcached::RES_NOTFOUND)) {
                continue;
            }
        }
        return $result;
    }

    /**
     * 获取多键.有缺陷,keys数组中的值必须都为字符串型.先要测试该方法是否可用,否则会引起致命错误
     * @return 成功返回结果,否则false
     */
    public function getMulti($keys, $zip = true) {
        foreach ((array) $keys as $i => $key) {
            $keys[$i] = (string) $key;
        }
        $this->connect();
        $this->memcachedClient->setOption(Memcached::OPT_COMPRESSION, $zip ? true : false); //对于set是压缩的则解压缩取出
        $result = $this->memcachedClient->getMulti($keys);  //抑制错误,如不能正常解压
        return $result;
    }

}
