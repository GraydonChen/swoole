<?php

namespace library\cache;

/**
 * Mongo操作类
 */
class mongo {

	private $manager = null; //数据库连接对象
	private $writeConcern;
	private $servers, $opt;

	public function __construct($mongocfg) {
		$this->opt = array(
			'connect' => true,
			'connectTimeoutMS' => 10000,
			'socketTimeoutMS' => 60000
		);
		$connect_str = "mongodb://";
		if (isset($mongocfg['auth'])&&$mongocfg['auth']) {
			list($username, $pwd) = explode(':', trim($mongocfg['auth']));
			if($username&&$pwd){
				$this->opt['username']=$username;
				$this->opt['password']=$pwd;
				$this->opt['authSource']='admin';
			}
		}
		foreach ($mongocfg['nodes'] as $server) {
			$connect_str .= $server[0] . ':' . $server[1] . ',';
			
			
		}
		$this->servers = substr($connect_str, 0, -1);
		if (isset($mongocfg['replicaSet']) && $mongocfg['replicaSet']) {
			$this->opt['replicaSet'] = $mongocfg['replicaSet'];
		}
		$this->manager = new \MongoDB\Driver\Manager($this->servers, $this->opt);
		$this->writeConcern = new \MongoDB\Driver\WriteConcern(1, 3000);
	}

	public function resetConnection($ex, $try_cnt = 0) {
		$this->manager = new \MongoDB\Driver\Manager($this->servers, $this->opt);
	}

	/**
	 * 安全插入数据
	 * @param type $tbl
	 * @param type $data
	 * @return int 插入的行数,当为-11000时为主键冲突,>0则为成功
	 */
	public function insert($tbl, $data, $try_cnt = 1) {
		$exception = false;
		for ($try = 0; $try < $try_cnt; $try++) {
			try {
				$bulk = new \MongoDB\Driver\BulkWrite(['ordered' => true]);
				$bulk->insert($data);
				$result = $this->manager->executeBulkWrite($tbl, $bulk, $this->writeConcern);
				return $result->getInsertedCount();
			} catch (\MongoDB\Driver\Exception\BulkWriteException $ex) {
				$exception = $ex;
				break;
			} catch (\Exception $ex) {
				$exception = $ex;
				$this->resetConnection($ex, $try);
			}
		}
		if ($exception) {
			throw $exception;
		}
	}

	/**
	 * 查询并返回一条记录
	 * @param type $tbl
	 * @param type $criteria
	 * @param type $fields
	 * @return object|boolean false时表示未取到结果，否则为object
	 */
	public function findOne($tbl, $criteria, $fields = array(), $sort = array(), $try_cnt = 1) {
		$needs = array();
		if (!empty($fields)) {
			foreach ($fields as $v) {
				$needs[$v] = 1;
			}
		}
		$options['projection'] = $needs;
		$options['limit'] = 1;
		if ($sort) {
			$options['sort'] = $sort;
		}
		$query = new \MongoDB\Driver\Query($criteria, $options);
		$rp = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY);
		$exception = false;
		for ($try = 0; $try < $try_cnt; $try++) {
			try {
				$result = $this->manager->executeQuery($tbl, $query, $rp);
				$exception = false;
				break;
			} catch (\Exception $ex) {
				$this->resetConnection($ex, $try);
				$exception = $ex;
			}
		}
		if ($exception) {
			throw $exception;
		}
		$result->setTypeMap(['root' => 'array']);
		return current($result->toArray());
	}

	/**
	 * 查询记录
	 * @param type $tbl
	 * @param type $criteria
	 * @param type $fields
	 * @return object|boolean false时表示未取到结果，否则为object
	 */
	public function find($tbl, $criteria, $fields = array(), $limit = 1000, $sort = array()) {
		$needs = array();
		if (!empty($fields)) {
			foreach ($fields as $v) {
				$needs[$v] = 1;
			}
		}
		$options['projection'] = $needs;
		$options['limit'] = $limit;
		if ($sort) {
			$options['sort'] = $sort;
		}
		$query = new \MongoDB\Driver\Query($criteria, $options);
		$result = $this->manager->executeQuery($tbl, $query);
		$result->setTypeMap(['document' => 'array', 'root' => 'array']);
		$documents = array();
		foreach ($result as $ret) {
			$documents[] = $ret;
		}
		return $documents;
	}

	public function count($tbl, $filter) {
		list($dbName, $coll) = explode('.', $tbl);
		$options = ['count' => $coll];
		$options['query'] = $filter;
		$cmd = new \MongoDB\Driver\Command($options);
		$cursor = $this->manager->executeCommand($dbName, $cmd);
		$ret = current($cursor->toArray());
		if (is_object($ret) && $ret->ok == 1) {
			return $ret->n;
		}
		return false;
	}

	/**
	 * 原子性更新
	 * @param type $tbl
	 * @param type $criteria
	 * @param type $update
	 * @param type $fields
	 * @param array $options
	 * @return array 返回的新值，为null时表示不存在记录
	 */
	public function findAndModify($tbl, $criteria, $update, $fields = array(), $options = array()) {
		$needs = array();
		if (!empty($fields)) {
			foreach ($fields as $v) {
				$needs[$v] = 1;
			}
		}
		list($dbName, $coll) = explode('.', $tbl);
		$cmd['findAndModify'] = $coll;
		$cmd['query'] = $criteria;
		$cmd['update'] = $update;
		$cmd['fields'] = $needs;
		$cmd['writeConcern'] = $this->writeConcern;
		$cmd['new'] = true;
		if (!empty($options)) {
			foreach (['writeConcern', 'new', 'sort'] as $key) {
				if (isset($options[$key])) {
					$cmd[$key] = $options[$key];
				}
			}
		}
		$commnd = new \MongoDB\Driver\Command($cmd);
		$cursor = $this->manager->executeCommand($dbName, $commnd);
		$cursor->setTypeMap(['document' => 'array']);
		return current($cursor->toArray())->value;
	}

	/**
	 * 删除
	 * @param type $tbl
	 * @param type $id
	 * @return int 返回删除的行数
	 */
	public function delete($tbl, $filter, $limit = 1) {
		$bulk = new \MongoDB\Driver\BulkWrite(['ordered' => true]);
		$bulk->delete($filter, ['limit' => $limit]);
		$result = $this->manager->executeBulkWrite($tbl, $bulk, $this->writeConcern);
		return $result->getDeletedCount();
	}

	/**
	 * 更新数据
	 * @param type $tbl
	 * @param type $filter
	 * @param type $update
	 * @param type $limit
	 * @return type
	 */
	public function update($tbl, $filter, $update, $limit = 1,$upsert=false) {
		$bulk = new \MongoDB\Driver\BulkWrite(['ordered' => true]);
		$bulk->update($filter, $update, ['limit' => $limit, 'upsert' => $upsert]);
		$result = $this->manager->executeBulkWrite($tbl, $bulk, $this->writeConcern);
		return $result->getModifiedCount();
	}

	/**
	 * 用于测试
	 * @return type
	 */
	public function ping() {
		$command = new \MongoDB\Driver\Command(array('ping' => 1));
		$cursor = $this->manager->executeCommand('admin', $command);
		$response = $cursor->toArray();
		return $response;
	}

}
