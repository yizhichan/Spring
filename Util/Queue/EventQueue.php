<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 事件队列（队列事件具备唯一性）
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class EventQueue
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
	 * 当前连接对象
	 */
	private $connectId  = null;


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
		$this->connectId  = null;
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
		if ( $this->connectId == null )
		{
			if( !file_exists($this->configFile) ) 
			{
				throw new SpringException("配置文件：".$this->configFile."不存在!");
			}
			require($this->configFile);
			$this->connectId = new Redis();
			$this->connectId->connect($host, $port);
			$this->connectId->select($this->db);
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
	 * 设置队列名称
	 *
	 * @access public
	 * @param  string	$name	队列名称
	 * @return EventQueue
	 */
	public function setName($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 选择数据库
	 *
	 * @access public
	 * @param  int	$db		数据库
	 * @return EventQueue
	 */
	public function setDb($db)
	{
		$this->db = $db;
		
		return $this;
	}

	/**
	 * 数据入队
	 *
	 * @access	public
	 * @param	string    $data		待入队数据
	 * @return	bool
	 */
	public function push($data)
	{
		$eventId = $data['id'];
		if ( !$eventId ) {
			return false;
		}

		$this->connect();
		$this->connectId->multi();
		$this->connectId->set($eventId, json_encode($data));
		$this->connectId->zAdd($this->name, time(), $eventId);
		$result = $this->connectId->exec();

		return $result[0] || $result[1];
	}

	/**
	 * 数据出队
	 *
	 * @access	public
	 * @return	array
	 */
	public function pop()
	{
		$this->connect();
		$members = $this->connectId->zRange($this->name, 0, 0);
		if ( !$members ) {
			return array();
		}

		$event = $this->connectId->get($members[0]);

		return $event ? json_decode($event, true) : array();
	}

	/**
	 * 删除队列成员
	 *
	 * @access	public
	 * @param	string	$eventId	事件id
	 * @return	bool
	 */
	public function remove($eventId)
	{
		$this->connect();
		$this->connectId->multi();
		$this->connectId->zDelete($this->name, $eventId);
		$this->connectId->delete($eventId);
		$result = $this->connectId->exec();

		return $result[0] || $result[1];
	}

	/**
	 * 队列长度(队列中成员个数)
	 *
	 * @access	public
	 * @return	int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->zSize($this->name);
	}
}
?>