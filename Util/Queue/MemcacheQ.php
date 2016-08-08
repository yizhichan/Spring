<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 MemcacheQ队列
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class MemcacheQ implements IQueue
{
	/**
	 * 队列默认名称
	 */
	public  $name      = "queue";

	/**
	 * 配置文件
	 */
	public $configFile = null;

	/**
	 * 编码对象
	 */
	public $encoding   = null;

	/**
	 * Memcache对象
	 */
	private $mem       = null;

	/**
	 * 连接标识
	 */
	private $connected = false;
	
	
	/**
	 * 检查是否安装Memcache
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		if ( !class_exists('Memcache') )
		{
			throw new SpringException('Not Support : Memcache');
		}
		$this->mem = new Memcache();
	}
	
	/**
	 * 释放资源
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct()
	{
		$this->close();
		$this->mem        = null;
		$this->name	      = null;
		$this->encoding   = null;
		$this->connected  = null;
		$this->configFile = null;
	}
	
	/**
	 * 打开连接
	 *
	 * @access	private
	 * @return	void
	 */
	private function connect()
	{
		if ( !$this->connected )
		{
			if ( !file_exists($this->configFile) ) 
			{
				throw new SpringException("缓存配置文件：".$this->configFile."不存在!");
			}

			require($this->configFile);
			$this->connected = $this->mem->connect($host, $port);

			if ( !$this->connected ) 
			{
				throw new SpringException("连接MemcacheQ失败");
			}
			$host = $port = null;
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
		if ( $this->connected )
		{
			$this->mem->close();
			$this->connected = null;
		}
	}
	
	/**
	 * 数据入队
	 *
	 * @access public
	 * @param  mixed    $data		待入队数据
	 * @param  int		$encoding	编码方式(0-3)
	 * @return bool
	 */
	public function push($data, $encoding = 1)
	{
		$this->connect();
		$encoding && $data = $this->encoding->encode($data, $encoding);
		return $this->mem->set($this->name, $data);
	}

	/**
	 * 数据出队
	 *
	 * @access public
	 * @param  int		$encoding	编码方式(0-3)
	 * @return mixed
	 */
	public function pop($encoding = 1)
	{
		$this->connect();

		$data = $this->mem->get($this->name);

		if ( $encoding == 0 )
		{
			return $data;
		}

		return $data ? $this->encoding->decode($data, $encoding) : '';
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
		$data  = $this->mem->getStats($this->name);
		$value = isset($data[$this->name]) ? explode("/", $data[$this->name]) : array();
		$size  = 0;

		if ( isset($value[0]) && isset($value[1]) )
		{
			$size = $value[0] - $value[1];
		}
		return $size;
	}
}
?>