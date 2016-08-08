<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  set(集合)
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataSet 
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
	 * 集合名称
	 */
	public  $name       = 'set';

	/**
	 * 编码对象
	 */
	public $encoding    = null;

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
	 * 设置集合名称
	 *
	 * @access public
	 * @param  string	$name	集合名称
	 * @return object
	 */
	public function channel($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 集合长度(集合中元素个数)
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->sSize($this->name);
	}
	
	/**
	 * 向集合中添加元素
	 *
	 * @access	public
	 * @param	mixed   $value		元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function add($value, $encoding = 0)
	{
		if ( empty($value) ) 
		{
			return '';
		}
		$this->connect();
		$encoding && $value = $this->encoding->encode($value, $encoding);
		$this->connectId->sAdd($this->name, $value);
	}

	/**
	 * 删除集合中的元素
	 *
	 * @access	public
	 * @param	mixed	$value		元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function remove($value, $encoding = 0)
	{
		if ( empty($value) )
		{
			return '';
		}
		$this->connect();
		$encoding && $value = $this->encoding->encode($value, $encoding);
		$this->connectId->sRem($this->name, $value);
	}

	/**
	 * 集合中是否存在$value元素
	 *
	 * @access	public
	 * @param	mixed	$value		元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function exist($value, $encoding = 0)
	{
		if ( empty($value) )
		{
			return false;
		}
		$this->connect();
		$encoding && $value = $this->encoding->encode($value, $encoding);
		
		return $this->connectId->sIsMember($this->name, $value);
	}

	/**
	 * 清空集合中的元素
	 *
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->connect();
		$this->connectId->delete($this->name);
	}

	/**
	 * 排序、分页
	 *
	 * @access	public
	 * @param	array	$rule		获取规则
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function sort($rule, $encoding = 0)
	{
		$this->connect();
		$list  = array();
		$items = $this->connectId->sort($this->name, $rule);
		foreach ( $items as $item )
		{
			$list[] = $item ? $this->encoding->decode($item, $encoding) : '';
		}
		return $list;
	}
}
?>