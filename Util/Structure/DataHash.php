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
	 * 编码对象
	 */
	public  $encoding   = null;

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
		$this->encoding   = null;
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
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function exist($key, $encoding = 1)
	{
		if ( empty($key) )
		{
			return false;
		}

		$this->connect();
		$key = $this->encoding->encode($key, $encoding);
		return $this->connectId->hExists($this->name, $key);
	}
	
	/**
	 * 添加元素
	 *
	 * @access	public
	 * @param	mixed   $key		键
	 * @param	mixed   $value		值
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function set($key, $value = '', $encoding = 1)
	{
		if ( empty($key) ) 
		{
			return '';
		}
		$this->connect();
		$key   = $this->encoding->encode($key, $encoding);
		$value = empty($value) ? '' : $this->encoding->encode($value, $encoding);
		$this->connectId->hSet($this->name, $key, $value);
	}

	/**
	 * 批量添加元素
	 *
	 * @access	public
	 * @param	array   $kvs		多个键值对
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function mSet($kvs, $encoding = 1)
	{
		if ( empty($kvs) ) 
		{
			return '';
		}
		$this->connect();

		if ( $encoding ) 
		{
			$kv = array();
			foreach ( $kvs as $k => $v )
			{
				$k      = $this->encoding->encode($k, $encoding);
				$v      = $this->encoding->encode($v, $encoding);
				$kv[$k] = $v;
			}
			$this->connectId->hMset($this->name, $kv);
		} 
		else
		{
			$this->connectId->hMset($this->name, $kvs);
		}
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
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function remove($key, $encoding = 1)
	{
		if ( empty($key) )
		{
			return '';
		}
		$this->connect();
		$key = $this->encoding->encode($key, $encoding);
		$this->connectId->hDel($this->name, $key);
	}

	/**
	 * 获取所有键
	 *
	 * @access	public
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function keys($encoding = 1)
	{
		$this->connect();
		$keys = $this->connectId->hKeys($this->name);
		foreach ( $keys as &$key )
		{
			$key = $this->encoding->decode($key, $encoding);
		}

		return $keys;
	}

	/**
	 * 获取所有值
	 *
	 * @access	public
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function vals($encoding = 1)
	{
		$this->connect();
		$vals = $this->connectId->hVals($this->name);

		foreach ( $vals as &$val )
		{
			$val = $this->encoding->decode($val, $encoding);
		}

		return $vals;
	}

	/**
	 * 获取单个元素
	 *
	 * @access	public
	 * @param	mixed	$key	键
	 * @return	mixed
	 */
	public function get($key, $encoding = 1)
	{
		if ( empty($key) )
		{
			return '';
		}
		$this->connect();
		$data = $this->connectId->hGet($this->name, $key);
		if ( $encoding )
		{
			return $this->encoding->decode($data, $encoding);
		}
		return $data;
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
		if ( $this->encode )
		{
			$items = $this->connectId->hGetAll($this->name);
			$list  = array();
			foreach ( $items as $key => $val )
			{
				$list[$this->unformat($key)] = $this->unformat($val);
			}
			return $list;
		}
		else
		{
			return $this->connectId->hGetAll($this->name);
		}
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
		
		if ( $this->encode )
		{
			$items = $this->connectId->hmGet($this->name, $keys);
			$list  = array();
			foreach ( $items as $key => $val )
			{
				$list[$this->unformat($key)] = $this->unformat($val);
			}
			$items = null;
			return $list;
		}
		else
		{
			return $this->connectId->hmGet($this->name, $keys);
		}
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