<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Redis队列
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class RedisQe implements IQueue
{	
	/**
	 * 配置文件
	 */
	public  $configFile = null;

	/**
	 * 数据库
	 */
	public  $db         = 0;

	/**
	 * 队列默认名称
	 */
	private $name       = "queue";

	/**
	 * 队列连接对象
	 */
	private $q	        = null;


	/**
	 * 检查是否安装Redis
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
	 */
	public function __destruct()
	{
		$this->close();
		$this->q          = null;
		$this->configFile = null;
		$this->name	      = null;
	}
	
	/**
	 * 打开连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function connect()
	{
		if ( $this->q == null )
		{
			if( !file_exists($this->configFile) ) 
			{
				throw new SpringException("配置文件：".$this->configFile."不存在!");
			}
			require($this->configFile);
			$this->db 	= $db;
			$this->q 	= new Redis();
			$this->q->connect($host, $port);
			$this->q->select($this->db);
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
		if ( $this->q != null )
		{
			$this->q->close();
		}
	}

	/**
	 * 设置队列名称
	 *
	 * @access public
	 * @param  string	$name	队列名称
	 * @return object
	 */
	public function name($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 数据入队
	 *
	 * @access public
	 * @param  string    $data	待入队数据
	 * @return bool
	 */
	public function push($data)
	{
		$this->connect();

		return $this->q->lPush($this->name, $data);
	}

	/**
	 * 数据出队
	 *
	 * @access	public
	 * @return	string
	 */
	public function pop()
	{
		$this->connect();

		return $this->q->rPop($this->name);
	}

	/**
	 * 队列长度(队列中元素个数)
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->q->llen($this->name);
	}
}
?>