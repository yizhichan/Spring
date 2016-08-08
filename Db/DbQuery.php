<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 mysql数据库查询接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DbQuery
{
	/**
	 * 连接数据库配置文件
	 */
	public  $configFile   = null;
	
	/**
	 * 异常信息
	 */
	private $errorMsg     = null;

	/**
	 * 当前连接ID
	 */
	private $connectId    = null;

	/**
	 * 查询结果对象
	 */
	private $PDOStatement = null;
	
	
	/**
	 * 检查是否安装pdo
	 *
	 * @access	public
	 * @param	string	$dbName	数据库名
	 * @return	void
	 */
	public function __construct($dbName = '')
	{
		if ( $dbName ) 
		{
			$this->configFile = ConfigDir."/Db/{$dbName}.master.config.php";
		}

		if ( !class_exists('PDO') )
		{ 
			throw new SpringException('Not Support : PDO');
		}
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
		$this->configFile   = null;
		$this->errorMsg     = null;
		$this->PDOStatement = null;
	}
	
	/**
	 * 打开数据库连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function connect()
	{
		require($this->configFile);
		try
		{
			$this->connectId = new PDO($dsn, $user, $password);
			$this->connectId->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connectId->exec("set names $encode");
			$dsn = $user = $password = $encode = null;
			
			if ( $this->connectId == null )
			{
				$this->errorMsg = "PDO CONNECT ERROR";
				ErrorHandle::record($this->errorMsg, 'error');
			}
		}
		catch ( PDOException $e )
		{
			$this->errorMsg = $e->getMessage();
			ErrorHandle::record($this->errorMsg, 'error');
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
		$this->connectId = null;
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
	 * 执行sql语句(数据库写)
	 *
	 * @access	public
	 * @param	string	$sql	sql指令
	 * @return	bool
	 */
	public function query($sql)
	{
		$this->connect();
		if ( $this->errorMsg ) 
		{
			return false;
		}
		
		if ( empty($sql) ) 
		{
			return false;
		}

		return $this->connectId->exec($sql);
	}
	
	/**
	 * 获得多条查询记录
	 *
	 * @access	public
	 * @param	string	$sql	sql指令
	 * @return	array
	 */
	public function fetchAll($sql)
	{
		$this->connect();
		if ( $this->errorMsg ) 
		{
			return array();
		}

		$result             = array();
		$this->PDOStatement = $this->connectId->prepare($sql);
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