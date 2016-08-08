<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  zset(有序集合)
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataZSet
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
	public  $name       = 'zset';

	/**
	 * 编码对象
	 */
	public  $encoding    = null;

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
	 * 设置集合名称
	 *
	 * @access public
	 * @param  string	$name	集合名称
	 * @return object
	 */
	public function name($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * 集合长度(集合中所有元素个数)
	 *
	 * @access public
	 * @return int
	 */
	public function size()
	{
		$this->connect();
		
		return $this->connectId->zSize($this->name);
	}
	
	/**
	 * 向集合中添加元素
	 *
	 * @access	public
	 * @param	int		$score		序号
	 * @param	mixed   $value		元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	void
	 */
	public function add($score, $value, $encoding = 0)
	{
		if ( empty($value) ) 
		{
			return '';
		}
		$this->connect();
		$encoding && $value = $this->encoding->encode($value, $encoding);
		$this->connectId->zAdd($this->name, $score, $value);
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
		$this->connectId->zDelete($this->name, $value);
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
		$score = $this->connectId->zScore($this->name, $value);

		return $score ? true : false;
	}

	/**
	 * 元素排名
	 *
	 * @access	public
	 * @param	mixed	$value		元素
	 * @param	int		$order		0升序、1降序
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	int
	 */
	public function rank($value, $order = 0, $encoding = 0)
	{
		if ( empty($value) ) 
		{
			return 0;
		}
		$this->connect();
		$op = in_array($order, array(0,1)) && $order ? 'zRevRank' : 'zRank';
		$encoding && $value = $this->encoding->encode($value, $encoding);
		
		return $this->connectId->$op($this->name, $value);
	}

	/**
	 * 取元素的score
	 *
	 * @access	public
	 * @param	mixed	$value		元素
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	int
	 */
	public function score($value, $encoding = 0)
	{
		if ( empty($value) ) 
		{
			return -1;
		}
		$this->connect();
		$encoding && $value = $this->encoding->encode($value, $encoding);
		return $this->connectId->zScore($this->name, $value);
	}

	/**
	 * 按索引号取元素
	 *
	 * @access	public
	 * @param	int		$start		开始位置 
	 * @param	int		$end		结束位置
	 * @param	int		$order		0升序、1降序(按score排序)
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function range($start, $end, $order = 0, $encoding = 0)
	{
		$this->connect();
		$list  = array();
		$op    = in_array($order, array(0,1)) && $order ? 'zRevRange' : 'zRange';
		$items = $this->connectId->$op($this->name, $start, $end);
		
		if ( $this->encoding == 0 )
		{
			return $items;
		}

		foreach ( $items as $item )
		{
			$list[] = $item ? $this->encoding->decode($item, $encoding) : '';
		}
		return $list;
	}

	/**
	 * 按范围取元素(score)
	 *
	 * @access	public
	 * @param	int		$min		score最小值
	 * @param	int		$max		score最大值
	 * @param	int		$order		0升序、1降序
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	array
	 */
	public function scope($min, $max, $order = 0, $encoding = 0)
	{
		$this->connect();
		$list  = array();
		$op    = in_array($order, array(0,1)) && $order ? 'zRevRangeByScore' : 'zRangeByScore';
		$items = $this->connectId->$op($this->name, $min, $max);
		
		if ( $encoding == 0 )
		{
			return $items;
		}

		foreach ( $items as $item )
		{
			$list[] = $item ? $this->encoding->decode($item, $encoding) : '';
		}
		return $list;
	}

	/**
	 * 按范围统计元素(score范围)
	 *
	 * @access	public
	 * @param	int		$min	score最小值
	 * @param	int		$max	score最大值
	 * @return	int
	 */
	public function count($min, $max)
	{
		$this->connect();

		return $this->connectId->zCount($this->name, $min, $max);
	}

	/**
	 * 按范围删除元素(score范围)
	 *
	 * @access	public
	 * @param	int		$min	score最小值
	 * @param	int		$max	score最大值
	 * @return	int
	 */
	public function delete($min, $max)
	{
		$this->connect();

		return $this->connectId->zRemRangeByScore($this->name, $min, $max);
	}

	/**
	 * 清空集合
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