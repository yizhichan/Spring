<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  list(列表)
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataList
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
	 * 列表名称
	 */
	public  $name       = 'list';

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
	 * 设置列表名称
	 *
	 * @access public
	 * @param  string	$name	列表名称
	 * @return object
	 */
	public function name($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 列表长度
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->llen($this->name);
	}
	
	/**
	 * 添加元素
	 *
	 * @access	public
	 * @param	mixed	$value		待添加元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @param	mixed	$direction	1左，2右
	 * @return	void
	 */
	public function push($value, $encoding = 1, $direction = 1)
	{
		if ( !in_array($direction, array(1,2)) )
		{
			return ;
		}

		$this->connect();
		$op = $direction == 1 ? 'lPush' : 'rPush';
		$encoding && $value = $this->encoding->encode($value, $encoding);
		$this->connectId->$op($this->name, $value);
	}

	/**
	 * 删除元素
	 *
	 * @access	public
	 * @param	int		$encoding	编码方式(0-3)
	 * @param	mixed	$direction	1左、2右
	 * @return	mixed
	 */
	public function pop($encoding = 1, $direction = 2)
	{
		if ( !in_array($direction, array(1,2)) )
		{
			return '';
		}

		$this->connect();
		$op    = $direction == 1 ? 'lPop' : 'rPop';
		$value = $this->connectId->$op($this->name);

		return $value ? $this->encoding->decode($value, $encoding) : '';
	}

	/**
	 * 按索引号取元素
	 *
	 * @access	public
	 * @param	int		$start		开始位置 
	 * @param	int		$end		结束位置
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function range($start, $end, $encoding = 1)
	{
		$this->connect();
		$list  = array();
		$items = $this->connectId->lRange($this->name, $start, $end);
		foreach ( $items as $item )
		{
			$list[] = $item ? $this->encoding->decode($item, $encoding) : '';
		}
		return $list;
	}

	/**
	 * 按索引号取单个元素
	 *
	 * @access	public
	 * @param	int		$index		位置索引
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	mixed
	 */
	public function get($index = 0, $encoding = 1)
	{
		$this->connect();
		$value = $this->connectId->lGet($this->name, $index);
		
		return $value ? $this->encoding->decode($value, $encoding) : '';
	}

	/**
	 * 保留索引号区间元素
	 *
	 * @access	public
	 * @param	int		$start	开始位置 
	 * @param	int		$end	结束位置 
	 * @return	void
	 */
	public function trim($start, $end)
	{
		$this->connect();
		$this->connectId->lTrim($this->name, $start, $end);
	}

	/**
	 * 清空列表
	 *
	 * @access	public
	 * @return	void
	 */
	public function clear()
	{
		$this->connect();
		while ( $this->size() )
		{
			$this->pop();
		}
	}
}
?>