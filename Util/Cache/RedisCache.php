<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  Redis缓存(数据可持久化)
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class RedisCache implements ICache
{
	/**
	 * 默认数据库名
	 */
	public $db       = 0;

	/**
	 * 当前连接对象
	 */
	public $connectId  = null;

	/**
	 * 配置文件
	 */
	public $configFile = null;

	/**
	 * 编码对象
	 */
	public $encoding   = null;


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
		$this->db	      = null;
		$this->connectId  = null;
		$this->configFile = null;
		$this->encoding   = null;		
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
			$this->db = $db;
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
	 * 写入数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	mixed   $value		值
	 * @param	int		$expire		缓存时间(0持久存储)
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function set($key, $value, $expire = 0, $encoding = 1)
	{
		if ( empty($key) )
		{
			return false;
		}

		$key  = $this->encoding->encode($key, $encoding);
		$data = empty($value) ? '' : $this->encoding->encode($value, $encoding);
		$this->connect();

		if ( $expire )
		{
			return $this->connectId->setex($key, $expire, $data);
		}
		else
		{
			return $this->connectId->set($key, $data);
		}
	}

	/**
	 * 写入数据(多条数据)
	 *
	 * @access	public
	 * @param	array   $list		键值对数据列表
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function mset($list, $encoding = 1)
	{
		if ( empty($list) || !is_array($list) )
		{
			return '';
		}

		$this->connect();
		
		foreach ( $list as &$data ) 
		{
			$data = $this->encoding->encode($data, $encoding);
		}
		$this->connectId->mset($list);
	}

	/**
	 * 获取数据(多条)
	 *
	 * @access	public
	 * @param	array	$keys		键 
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function mget($keys, $encoding = 1)
	{
		$this->connect();
		$items = empty($keys) || !is_array($keys) ? array() : $this->connectId->getMultiple($keys);
		$list  = array();
		
		foreach ( $items as $data ) 
		{
			if ( !empty($data) )
			{
				$list[] = $this->encoding->decode($data, $encoding);
			}
		}
		$items = null;
		
		return $list;
	}

	/**
	 * 获取数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	mixed
	 */
	public function get($key, $encoding = 1)
	{
		if ( empty($key) )
		{
			return '';
		}

		$this->connect();
		$key  = $this->encoding->encode($key, $encoding);
		$data = $this->connectId->get($key);

		if ( empty($data) )  
		{
			return '';
		}

		return $this->encoding->decode($data, $encoding);
	}

	/**
	 * 删除数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	int
	 */
	public function remove($key, $encoding = 1)
	{
		$this->connect();
		$key = $this->encoding->encode($key, $encoding);

		return $this->connectId->delete($key);
	}

	/**
	 * 清空数据
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function clear()
	{
		$this->connect();
		
		return $this->connectId->flushdb();
	}
}
?>