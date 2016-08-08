<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  Redis管理工具
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class RedisManager
{
	/**
	 * 配置文件
	 */
	public  $configFile = null;

	/**
	 * 当前连接对象
	 */
	private $connectId  = null;


	/**
	 * 检查是否安装Redis
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		 if ( !class_exists('Redis') )
		 {
			 throw new SpringException('Not Support : Redis');
		 }
	}

	/**
	 * 释放资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->close();
		$this->configFile = null;
		$this->connectId  = null;
	}

	/**
	 * 打开连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function connect()
	{
		if ( $this->connectId == null )
		{
			if ( !file_exists($this->configFile) ) 
			{
				throw new SpringException("配置文件：".$this->configFile."不存在!");
			}
			require($this->configFile);
			$this->connectId = new Redis();
			$this->connectId->connect($host, $port);
			$this->connectId->select($db);
		}
	}

	/**
	 * 关闭连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function close()
	{
		if ( $this->connectId != null )
		{
			$this->connectId->close();
		}
	}

	/**
	 * 选择数据库
	 *
	 * @access public
	 * @param  int		$db		数据库
	 * @return RedisManager对象
	 */
	public function select($db = 1)
	{
		$db = intval($db);
		if ( $db )
		{
			$this->connect();
			$this->connectId->select($db);
		}
		
		return $this;
	}

	/**
	 * 数据库大小
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->dbSize();
	}

	/**
	 * 数据库信息
	 *
	 * @access public
	 * @return int
	 */
	public function info()
	{
		$this->connect();
		
		return $this->connectId->info();
	}

	/**
	 * 清空数据库
	 *
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->connect();
		$this->connectId->flushDb();
	}
}
?>