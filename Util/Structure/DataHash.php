<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  hash
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataHash 
{
	/**
	 * 默认数据库
	 */
	public  $db         = 1;

	/**
	 * 配置文件
	 */
	public  $configFile = null;

	/**
	 * hash名称
	 */
	public  $name       = 'hash';

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
		$this->db         = null;
		$this->configFile = null;
		$this->name	      = null;
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
	 * 设置哈希表名称
	 *
	 * @access public
	 * @param  string	$name	hash名称
	 * @return object
	 */
	public function name($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 哈希表长度
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->hLen($this->name);
	}

	/**
	 * 元素是否存在
	 *
	 * @access	public
	 * @param	string	$key	键
	 * @return	bool
	 */
	public function exist($key)
	{
		if ( empty($key) )
		{
			return false;
		}

		$this->connect();
		return $this->connectId->hExists($this->name, $key);
	}
	
	/**
	 * 添加元素
	 *
	 * @access	public
	 * @param	string	$key		键
	 * @param	mixed	$value		值
	 * @return	void
	 */
	public function set($key, $value = '')
	{
		if ( empty($key) ) 
		{
			return '';
		}

		$this->connect();
		$this->connectId->hSet($this->name, $key, $value);
	}

	/**
	 * 批量添加元素
	 *
	 * @access	public
	 * @param	array   $kvs	多个键值对
	 * @return	void
	 */
	public function mSet($kvs)
	{
		if ( empty($kvs) ) 
		{
			return '';
		}

		$this->connect();

		$this->connectId->hMset($this->name, $kvs);
	}

	/**
	 * 计数
	 *
	 * @access	public
	 * @param	mixed	$key	键
	 * @param	mixed   $value  值(int|float)
	 * @return	void
	 */
	public function increment($key, $n)
	{
		if ( empty($key) )
		{
			return '';
		}

		$this->connect();
		$this->connectId->hIncrBy($this->name, $key, $n);
	}

	/**
	 * 删除元素
	 *
	 * @access	public
	 * @param	string	$key	键
	 * @return	void
	 */
	public function remove($key)
	{
		if ( empty($key) )
		{
			return '';
		}

		$this->connect();
		$this->connectId->hDel($this->name, $key);
	}

	/**
	 * 获取所有键
	 *
	 * @access	public
	 * @return	array
	 */
	public function keys()
	{
		$this->connect();

		return $this->connectId->hKeys($this->name);
	}

	/**
	 * 获取所有值
	 *
	 * @access	public
	 * @return	array
	 */
	public function vals()
	{
		$this->connect();
		
		return $this->connectId->hVals($this->name);
	}

	/**
	 * 获取单个元素
	 *
	 * @access	public
	 * @param	string	$key	键
	 * @return	mixed
	 */
	public function get($key)
	{
		if ( empty($key) )
		{
			return '';
		}

		$this->connect();
		
		return $this->connectId->hGet($this->name, $key);
	}

	/**
	 * 获取全部元素
	 *
	 * @access	public
	 * @return	array
	 */
	public function getAll()
	{
		$this->connect();

		return $this->connectId->hGetAll($this->name);
	}

	/**
	 * 获取多个元素
	 *
	 * @access	public
	 * @param	array	$keys	多个键
	 * @return	mixed
	 */
	public function mGet($keys)
	{
		if ( empty($keys) )
		{
			return '';
		}

		$this->connect();
		
		return $this->connectId->hmGet($this->name, $keys);
	}

	/**
	 * 清空哈希表
	 *
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->connect();
		$this->connectId->delete($this->name);
	}
}
?>