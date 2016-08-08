<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  Memcache协议接口
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class MmCache implements ICache
{
	/**
	 * Memcache对象
	 */
	public $mem        = null;

	/**
	 * 过期时间(5分钟)
	 */
	public $expire     = 300;  
	
	/**
	 * 连接标识
	 */
	public $connected  = false;

	/**
	 * 编码对象
	 */
	public $encoding   = null;


	/**
	 * 检查是否安装Memcache
	 *
	 * @access	public
	 * @return	void
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
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		 $this->close();
		 $this->mem        = null;
		 $this->expire     = null;
		 $this->connected  = null;
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
		if ( !$this->connected )
		{
			if ( !file_exists($this->configFile) )
			{
				throw new SpringException("配置文件：".$this->configFile."不存在!");
			}

			require($this->configFile);
			foreach ( $hosts as $host )
			{
				$this->mem->addServer($host['ip'], $host['port'], true, 1, 1, $interval, true);
			}
			$this->connected = true;
			$hosts = null;
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
			$this->connected = false;
		}
	}

	/**
	 * 写入数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	mixed   $value		值
	 * @param	mixed   $expire		缓存时间
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function set($key, $value, $expire = 0, $encoding = 1)
	{
		if ( empty($key) ) 
		{
			return false;
		}

		$key    = $this->encoding->encode($key, $encoding);
		$data   = empty($value) ? '' : $this->encoding->encode($value, $encoding);
		$expire = $expire > 0 ? $expire : $this->expire;
		$this->connect();
		
		return $this->mem->set($key, $data, 0, $expire);
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
		$data = $this->mem->get($key);

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
	 * @return	bool
	 */
	public function remove($key, $encoding = 1)
	{
		$this->connect();
		$key = $this->encoding->encode($key, $encoding);

		return $this->mem->delete($key);
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

		return $this->mem->flush();
	}
}
?>