<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 通用数据库访问接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SpringMysql implements IDataSource
{
	/**
	 * 当前数据库id(数据库标识)
	 */
	public  $dbId         = null;

	/**
	 * 连接数据库配置文件
	 */
	public  $configFile   = null;

	/**
	 * 表路由配置文件
	 */
	public  $routeFile    = null;

	/**
	 * 默认返回的数据条数
	 */
	public  $limit        = 1;
	
	/**
	 * 日志对象
	 */
	public  $dbLog        = null;
	
	/**
	 * 是否开启事务
	 */
	public  $transaction  = false;

	/**
	 * 表路由配置信息
	 */
	private $tbl          = null;
	
	/**
	 * 当前操作的数据表
	 */
	private $table	      = null;

	/**
	 * 当前执行的sql序列
	 */
	private $sqls         = null;

	/**
	 * 连接池
	 */
	private $pool         = array();

	/**
	 * 操作所影响的行数
	 */
	private $affectedRows = 0;

	/**
	 * 查询结果对象
	 */
	private $PDOStatement = null;
	
	
	/**
	 * 检查是否安装pdo
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		if ( !class_exists('PDO') )
		{
			throw new SpringException("PDO扩展不存在!");
		}
	}

	/**
	 * 自动加载表路由配置文件
	 *
	 * @access public
	 * @return void
	 */
	public function load()
	{
		if ( !file_exists($this->routeFile) )
		{
			throw new SpringException("表路由配置文件{$this->routeFile}不存在!");
		}

		require($this->routeFile);
		$this->tbl = $tbl;
		unset($tbl);
	}

	/**
	 * 清理资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->close();
		$this->dbId         = null;
		$this->configFile   = null;
		$this->routerFile   = null;
		$this->limit        = null;
		$this->tbl          = null;
		$this->dbLog        = null;
		$this->table        = null;
		$this->sqls         = null;
		$this->affectedRows = null;
		$this->PDOStatement = null;
	}

	/**
	 * 获取结构信息
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	array
	 */
	public function struct($tableKey)
	{
		$table  = $this->getTable($tableKey, 0, 0);
		$sql    = "show full fields from $table";
		$list   = $this->getRows($sql);
		$struct = array();

		foreach ( $list as $item ) 
		{
			$struct[$item['Field']] = "$item[Type]、$item[Comment]";
		}

		return $struct;
	}
	
	/**
	 * 获取一条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function findOne($tableKey, $rule)
	{
		$table = $this->getTable($tableKey, $rule['slice'], $rule['from']);
		$where = $this->where($rule);
		$col   = isset($rule['col']) && is_array($rule['col']) && !empty($rule['col'])
			     ? implode(",", $this->parseKey($rule['col']) ) 
			     : '*'; 
		$sql   = "select $col from $table where $where limit 1";
		
		return $this->getRow($sql);
	}

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function find($tableKey, $rule)
	{
		$table  = $this->getTable($tableKey, $rule['slice'], $rule['from']);
		$where  = $this->where($rule);
		$limit  = isset($rule['limit']) ? intval($rule['limit']) : $this->limit;
		$offset = $limit <= 0 ? ' limit '.$this->limit : ' limit '.$limit; 
		
		if ( isset($rule['index']) && is_array($rule['index']) && count($rule['index']) == 2 ) {
			$offset = ' limit '. $rule['index'][0].','.$rule['index'][1];
		}

		$col = isset($rule['col']) && is_array($rule['col']) && !empty($rule['col']) 
			   ? implode(",", $this->parseKey($rule['col']) ) 
			   : '*'; 
		$sql = "select $col from $table where $where $offset";
		
		return $this->getRows($sql);
	}

	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function findAll($tableKey, $rule)
	{
		$table  = $this->getTable($tableKey, $rule['slice'], $rule['from']);
		$where  = $this->where($rule);
		$limit  = isset($rule['limit']) ? intval($rule['limit']) : $this->limit;
		$limit  = $limit <= 0 ? $this->limit : $limit; 
		$col    = isset($rule['col']) && is_array($rule['col']) && !empty($rule['col']) 
			      ? implode(",", $this->parseKey($rule['col']) ) 
				  : '*';
		$page   = isset($rule['page']) ? intval($rule['page']) : 1;
		$page   = $page <= 0 ? 1 : $page;
		$page   = $page > 10000 ? 1 : $page;
		$offset = ($page-1)* $limit;
		$offset = ($offset < 0) ? 0 : $offset; 
		$limit  = ' limit '.$offset.','. $limit;
		$sql    = "select $col from $table where $where $limit";
		
		return array(
			'rows'  => $this->getRows($sql),
			'total' => $this->count($tableKey, $rule),
			);
	}

	/**
	 * 统计数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	int
	 */
	public function count($tableKey, $rule)
	{
		if ( isset($rule['order']) )
		{
			unset($rule['order']);
		}

		$table = $this->getTable($tableKey, $rule['slice'], $rule['from']);
		$where = $this->where($rule);
		$sql   = "select count(*) as total from $table where $where";
		
		if ( preg_match('!(group[[:space:]]+by|having|select[[:space:]]+distinct)[[:space:]]+!is', $sql) )
		{
			$sqlCount = preg_replace("|select.*?from([\s])|i", "select count(*) as total from$1", $sql, 1);
			$rows     = $this->getRows($sqlCount);
			$total    = empty($rows) ? 0 : count($rows);
		}
		else 
		{
			$sqlCount = preg_replace("|select.*?from([\s])|i", "select count(*) as total from$1", $sql, 1);
			$row      = $this->getRow($sqlCount);
			$total    = isset($row['total']) ? $row['total'] : 0 ;
		}

		return $total;
	}

	/**
	 * 执行原始语句
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function raw($tableKey, $rule)
	{
		if ( !isset($rule['query']) || !$rule['query'] )
		{
			return array();
		}
		
		$this->getTable($tableKey, $rule['slice'], $rule['from']);
		$rawStatement = explode(" ", $rule['query']);
		$statement    = strtolower(trim($rawStatement[0]));

		if ( $statement === 'select' || $statement === 'show' ) {
			return $this->getRows($rule['query']);
		}

		return $this->query($rule['query']);
	}

	/**
	 * 创建一条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$data		数据信息[键值对]
	 * @param	array	$rule		数据创建规则
	 * @return	int     0失败、大于0成功
	 */
	public function create($tableKey, $data, $rule)
	{
		if ( !is_array($data) || empty($data) )
		{
			return 0;
		}

		$table  = $this->getTable($tableKey, $rule['slice'], 1);
		$keys   = $this->parseKey(array_keys($data));
		$values = array_values($data);
		$data   = array_combine($keys, $values);
		$bool   = $this->insert($table, $data);
		$id     = $bool ? $this->getLastInsId() : 0;
		
		if ( $bool )
		{
			$id = $this->getLastInsId();
			$id = $id > 0 ? $id : 1;
		}
		else
		{
			$id = 0;
		}

		return $id;
	}
	
	/**
	 * 修改数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$data		数据信息[键值对]
	 * @param	array	$rule		数据修改规则
	 * @return	bool
	 */
	public function modify($tableKey, $data, $rule)
	{
		if ( !is_array($data) || empty($data) || !is_array($rule) || empty($rule) )
		{
			return false;
		}

		$table  = $this->getTable($tableKey, $rule['slice'], 1);
		$where  = $this->where($rule);
		$keys   = $this->parseKey(array_keys($data));
		$values = array_values($data);
		$data   = array_combine($keys, $values);

		return $this->update($table, $data, $where);
	}

	/**
	 * 删除数据 
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据删除规则
	 * @return	bool
	 */
	public function remove($tableKey, $rule)
	{
		if ( !is_array($rule) || empty($rule) )
		{
			return false;
		}

		$table = $this->getTable($tableKey, $rule['slice'], 1);
		$where = $this->where($rule);
		
		return $this->delete($table, $where);
	}

	/**
	 * 开始事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function begin($tableKey)
	{
		$this->getTable($tableKey);
		$this->connect();
		
		if ( !isset($this->pool[$this->dbId]->transaction) )
		{
			$this->transaction = true;
			$this->pool[$this->dbId]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pool[$this->dbId]->beginTransaction();
			$this->pool[$this->dbId]->transaction = true;
		}

		return true;
	}

	/**
	 * 提交事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function commit($tableKey)
	{
		$this->getTable($tableKey);
		$this->connect();

		if ( !isset($this->pool[$this->dbId]->transaction) )
		{
			return false;
		}

		try
		{
			$this->pool[$this->dbId]->commit();
			$bool = true;
		}
		catch(PDOException $e)
		{
			$this->pool[$this->dbId]->handle = $this->pool[$this->dbId]->rollBack();
			ErrorHandle::record($e->getMessage(), 'error');
			$bool = false;
		}
		unset($this->pool[$this->dbId]->transaction);
		return $bool;
	}

	/**
	 * 回滚事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function rollBack($tableKey)
	{
		$this->getTable($tableKey);
		$this->connect();

		if ( isset($this->pool[$this->dbId]->transaction) )
		{
			$bool = $this->pool[$this->dbId]->rollBack();
			unset($this->pool[$this->dbId]->transaction);
			return $bool;
		}

		if ( isset($this->pool[$this->dbId]->handle) )
		{
			return $this->pool[$this->dbId]->handle;
		}

		return false;
	}

	/**
	 * 根据key获取表名
	 *
	 * @access	public
	 * @param	string		$key	数据表标识
	 * @param	int			$slice	切片[0无切片、大于0的整数为切片标识]
	 * @param	int			$master	选择主从服务器[0从、1主]
	 * @return	string
	 */
	public function getTable($key, $slice = 0, $master = 1)
	{
		if ( !isset($this->tbl[$key]) ) 
		{
			throw new SpringException("key: $key 对应的数据表不存在!");
		}

		if ( !isset($this->tbl[$key]['dbId']) 
			|| !isset($this->tbl[$key]['name']) 
			|| !isset($this->tbl[$key]['configFile'])
			)
		{
			throw new SpringException("key: $key 对应的数据表配置节点错误!");
		}
		
		$config      = $this->tbl[$key]['configFile'];
		$table       = $this->tbl[$key]['name'];
		$this->table = $slice ? $table.intval($slice) : $table;

		if ( $master )
		{
			$this->configFile = $config[0];
			$this->dbId       = $this->tbl[$key]['dbId'];
		}
		else
		{
			$this->configFile = isset($config[1]) ? $config[1] : $config[0];
			$this->dbId       = 'slave_'.$this->tbl[$key]['dbId'];
		}
		
		return $this->table;
	}
	
	/**
	 * 构造sql查询条件
	 *
	 * @access	protected
	 * @param	array	$rule	数据查询规则
	 * @return	string
	 */
	protected function where($rule)
	{
		$where = '';
		$order = $group = array();

		if ( isset($rule['eq']) && is_array($rule['eq']) && !empty($rule['eq']) ) {
			foreach ($rule['eq'] as $key => $value) {
				$value = addslashes($value);
				$kv[]  = $this->parseKey($key).'='."'$value'";
			}
			$where = "( " . implode(' and ', $kv) . " )";
		}

		if ( isset($rule['in']) && is_array($rule['in']) && !empty($rule['in']) ) {
			$kv = array();
			foreach ($rule['in'] as $key => $value) {
				if ( is_array($value) ) {
					$items = array();
					foreach ( $value as $val ) {
						$items[] = "'".$val."'";
					}
					$kv[] = $this->parseKey($key). " in ( ". implode(',', $items) .")";
				}
			}
			$in = "( " . implode(' and ', $kv) . " )";
			$where .= $where ? " and $in " : $in;
		}

		if ( isset($rule['notIn']) && is_array($rule['notIn']) && !empty($rule['notIn']) ) {
			$kv = array();
			foreach ($rule['notIn'] as $key => $value) {
				if ( is_array($value) ) {
					$items = array();
					foreach ( $value as $val ) {
						$items[] = "'".$val."'";
					}
					$kv[] = $this->parseKey($key). " not in ( ". implode(',', $items) .")";
				}
			}
			$notIn = "( " . implode(' and ', $kv) . " )";
			$where .= $where ? " and $notIn " : $notIn;
		}

		if ( isset($rule['scope']) && is_array($rule['scope']) && !empty($rule['scope']) ) {
			$kv = array();
			foreach ($rule['scope'] as $key => $value) {
				if ( is_array($value) && count($value) == 2  && $value[0] < $value[1]) {
					$kv[] = "( " . $this->parseKey($key) ." >= ". $value[0] . " and ". $this->parseKey($key) ." <= ". $value[1]. " )";
				}
			}
			$scope = implode(' and ', $kv);
			$where .= $where ? ($kv ? " and $scope " : "") : $scope;
		}

		//原始条件（后续版本会废弃，建议使用 raw）
		if ( isset($rule['other']) && is_string($rule['other']) && !empty($rule['other']) ) {
			$where .= $where ? " and " . $rule['other'] : $rule['other'];
		}

		//原始条件
		if ( isset($rule['raw']) && is_string($rule['raw']) && !empty($rule['raw']) ) {
			$where .= $where ? " and " . $rule['raw'] : $rule['raw'];
		}

		if ( isset($rule['ft']) && is_array($rule['ft']) && !empty($rule['ft']) ) {
			$kv = array();
			foreach ( $rule['ft'] as $key => $value ) {
				if ( is_array($value) ) {
					foreach ( $value as $v ) {
						$v && $kv[] = "MATCH({$this->parseKey($key)}) AGAINST ('$v')";
					}
				} else {
					$value && $kv[] = "MATCH({$this->parseKey($key)}) AGAINST ('$value')";
				}
			}

			if ( $kv ) {
				$ft = "( " . implode(' and ', $kv) . " )";
				$where .= $where ? " and ".$ft : $ft;
			}
		}

		if ( isset($rule['lLike']) && is_array($rule['lLike']) && !empty($rule['lLike']) ) {
			$kv = array();
			foreach ($rule['lLike'] as $key => $value) {
				if ( is_array($value) ) {
					foreach ($value as $v) {
						if ( trim($v) != '' ) $kv[] = $this->parseKey($key).' like \''. $v . '%\'';
					}
				} else {
					if ( trim($value) != '' ) {
						$kv[] = $this->parseKey($key).' like \''. $value . '%\'';
					}
				}
			}
			if ( !empty($kv) ) {
				$like = '( ' . implode(' and ', $kv) . ')';
				$where .= $where ? " and $like " : $like;
			}
		}

		if ( isset($rule['like']) && is_array($rule['like']) && !empty($rule['like']) ) {
			$kv = array();
			foreach ($rule['like'] as $key => $value) {
				if ( is_array($value) ) {
					foreach ($value as $v) {
						if ( trim($v) != '' ) $kv[] = $this->parseKey($key).' like \'%'. $v . '%\'';
					}
				} else {
					if ( trim($value) != '' ) {
						$kv[] = $this->parseKey($key).' like \'%'. $value . '%\'';
					}
				}
			}
			if ( !empty($kv) ) {
				$like = '( ' . implode(' and ', $kv) . ')';
				$where .= $where ? " and $like " : $like;
			}
		}

		if ( isset($rule['rLike']) && is_array($rule['rLike']) && !empty($rule['rLike']) ) {
			$kv = array();
			foreach ($rule['rLike'] as $key => $value) {
				if ( is_array($value) ) {
					foreach ($value as $v) {
						if ( trim($v) != '' ) $kv[] = $this->parseKey($key).' like \'%'. $v . '\'';
					}
				} else {
					if ( trim($value) != '' ) {
						$kv[] = $this->parseKey($key).' like \'%'. $value . '\'';
					}
				}
			}
			if ( !empty($kv) ) {
				$like = '( ' . implode(' and ', $kv) . ')';
				$where .= $where ? " and $like " : $like;
			}
		}

		$where = $where ? $where : ' 1 ';

		if ( isset($rule['group']) && is_array($rule['group']) )
		{
			foreach($rule['group'] as $key => $value) {
				$group[] = $this->parseKey($key). ' '.$value;
			}

			if ( !empty($group) ) {
				$where .= ' group by '. implode(',', $group);
			}
		}

		if ( isset($rule['order']) && is_array($rule['order']) )
		{
			foreach($rule['order'] as $key => $value) {
				$order[] = $this->parseKey($key). ' '.$value;
			}
			if ( !empty($order) ) {
				$where .= ' order by '. implode(',', $order);
			}
		}

		return $where;
	}

	/**
	 * 字段和表名处理添加`
	 *
	 * @access	protected
	 * @param	mixed		$key	字段、表名	
	 * @return	string
	 */
	protected function parseKey($key)
	{
		if ( is_array($key) )
		{
			$itmes = array();
			foreach ( $key as $k )
			{
				$k       = trim($k);
				if ( !preg_match('/[,\'\"\*\(\)`.\s]/',$k) )
				{
					$k = '`'.$k.'`';
				}
				$items[] = $k;
			}
			return $items;
		} 
		else
		{
			$key    =  trim($key);
			if ( !preg_match('/[,\'\"\*\(\)`.\s]/',$key) )
			{
				$key = '`'.$key.'`';
			}
			return $key;
		}
	}

	/**
	 * 打开数据库连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function connect()
	{
		if ( isset($this->pool[$this->dbId]) && $this->pool[$this->dbId] ) 
		{
			return ;
		}
		
		if ( !file_exists($this->configFile) ) 
		{
			throw new SpringException("数据库配置文件：".$this->configFile."不存在!");
		}
		
		require($this->configFile);
		try
		{
			$this->pool[$this->dbId] = new PDO($dsn, $user, $password);
			$this->pool[$this->dbId]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pool[$this->dbId]->exec("set names $encode");
			$dsn = $user = $password = $encode = null;
			if ( !$this->pool[$this->dbId] )
			{
				throw new SpringException("PDO CONNECT ERROR!");
			}
		}
		catch ( PDOException $e )
		{
			throw new SpringException($e);
			return ;
		}
	}
	
	/**
	 * 关闭数据库连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function close()
	{
		foreach ( $this->pool as $key => $linkId )
		{
			$this->pool[$key] = null;
		}
		$this->pool = null;
	}
	 
	/**
	 * 释放查询结果
	 *
	 * @access	private
	 * @return	void
	 */
	private function free()
	{
		$this->PDOStatement = null;
	}

	/**
	 * 执行sql语句(针对 INSERT, UPDATE 以及DELET)
	 *
	 * @access	public
	 * @param	string	$sql	sql指令
	 * @return	bool
	 */
	public function query($sql)
	{
		$this->connect();
		
		if ( empty($sql) ) 
		{
			return false;
		}
		$this->record($sql);
		$this->affectedRows = $this->pool[$this->dbId]->exec($sql);

		return $this->affectedRows >= 0 ? true : false; 
	}

	/**
	 * 返回操作所影响的行数(INSERT、UPDATE 或 DELETE)
	 *
	 * @access	public
	 * @return	int
	 */
	public function getAffected()
	{
		if ( !isset($this->pool[$this->dbId]) ) 
		{
			return 0;
		}
		return $this->affectedRows;
	}
	
	/**
	 * 获得最后一次插入的id
	 *
	 * @access	public
	 * @return	int
	 */
	public function getLastInsId()
	{
		if ( isset($this->pool[$this->dbId]) )
		{
			return $this->pool[$this->dbId]->lastInsertId(); 
		}
		return 0;		
	}
	
	/**
	 * 获得一条查询记录
	 *
	 * @access	public
	 * @param	string	$sql	sql指令
	 * @return	array
	 */
	public function getRow($sql)
	{
		$this->connect();

		$this->record($sql);
		$result             = array();
		$this->PDOStatement = $this->pool[$this->dbId]->prepare($sql);
		$this->PDOStatement->execute();
		
		if ( empty($this->PDOStatement) )
		{
			$this->error($sql);
			return $result;
		}

		$result = $this->PDOStatement->fetch(constant('PDO::FETCH_ASSOC'));
		$this->free();

		return $result;
	}
	
	/**
	 * 获得多条查询记录
	 *
	 * @access	public
	 * @param	string	$sql	sql指令
	 * @return	array
	 */
	public function getRows($sql)
	{
		$this->connect();

		$this->record($sql);
		$result             = array();
		$this->PDOStatement = $this->pool[$this->dbId]->prepare($sql);
		$this->PDOStatement->execute();
		
		if ( empty($this->PDOStatement) )
		{
			$this->error($sql);
			return $result;
		}

		$result = $this->PDOStatement->fetchAll(constant('PDO::FETCH_ASSOC'));
		$this->free();

		return $result;
	}

	/**
	 * 添加数据
	 *
	 * @access	public
	 * @param	string	$table	表名
	 * @param	array   $data	插入的数据(键值对)
	 * @return	bool
	 */
	public function insert($table, $data = array() )
	{
		if ( !empty($data) && is_array($data) )
		{
			foreach ( $data as $key => $val )
			{
				$val      = addslashes($val);
				$fields[] = $key;
				$values[] = "'$val'";
			}
			$field  = implode(',', $fields);
			$value  = implode(',', $values);
			$sql    = "insert into $table($field) values($value)";
			
			return $this->query($sql);
		}

		return false;
	}
	
	/**
	 * 更新数据
	 *
	 * @access	public
	 * @param	string	$table	表名
	 * @param	array   $data	更新的数据(键值对)
	 * @param	string  $where  条件
	 * @return	bool
	 */
	public function update($table, $data = array(), $where = '' )
	{
		if ( !empty($data) && is_array($data) )
		{
			foreach ( $data as $key => $val )
			{
				if ( is_array($val) )
				{
					$val      = $val[0] . '+' .$val[1];
					$fields[] = "$key=$val";
				}
				else
				{
					$val      = addslashes($val);
					$fields[] = "$key='$val'";
				}
			}
			$field = implode(',', $fields);
			$sql   = "update $table set $field";
		}

		//禁止无条件进行数据更新
		if ( !empty($where) )
		{
			$sql .= " where $where";
			$this->query($sql);
			return $this->getAffected();
		}
		
		return false;
	}
	
	/**
	 * 删除数据
	 *
	 * @access	public
	 * @param	string	$table	表名
	 * @param	string  $where  条件
	 * @return	bool
	 */
	public function delete($table, $where = '')
	{
		$sql = "delete from $table";

		//禁止无条件进行数据删除
		if ( !empty($where) )
		{
			$sql .= " where $where";

			return $this->query($sql);
		}

		return false;
	}

	/**
	 * 日志记录
	 *
	 * @access	private
	 * @return	void
	 */
	private function record($sql)
	{
		if ( $this->dbLog && is_object($this->dbLog) )
		{
			$this->dbLog->record($this->table, $sql);
		}
	}
	
	/**
	 * 数据库错误信息
	 *
	 * @access	private
	 * @param	string	$sql	sql指令
	 * @return	void
	 */
	private function error($sql)
	{
		$error = $this->PDOStatement->errorInfo();
		$str   = $error[2];
		$str  .= "\n [ SQL语句 ] : ".$sql;
		ErrorHandle::record($str, 'error');
	}
}
?>