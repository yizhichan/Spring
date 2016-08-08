<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层缓存接口(数据变化自动更新缓存)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class FileCacheApi extends Entity
{
	/**
	 * 缓存组件标识id
	 */
	public $cacheId = 'fileCache';

	/**
	 * 表行记录
	 */
	private $row    = array();

	/**
	 * 表数据是否发生改变(只针对更新、删除)
	 */
	private $change = false;


	/**
	 * 得到指定ID的数据(主键查询)
	 *
	 * @access	public
	 * @param	int		$id		主键id
	 * @param	string	$col	字段名称[只支持一个字段]
	 * @return	mixed(array|string|int|float)
	 */
	public function findOne($id, $col = '')
	{
		if ( isset($this->row[$id]) && !$this->change )
		{
			if ( $col )
			{
				return isset($this->row[$id][$col]) ? $this->row[$id][$col] : '';
			}
			else
			{
				return $this->row[$id];
			}
		}

		$cache       = $this->getCacheObject();
		$cache->path = EntityCacheDir.'/'.$this->tableKey;
		$key         = $this->tableKey.':'.$id;
		$data        = $cache->get($key);

		if ( !empty($data) )
		{
			$this->row[$id] = $data;
			if ( $col ) 
			{
				return isset($data[$col]) ? $data[$col] : '';
			}
			else
			{
				return $data;
			}
		}
		
		$data = parent::findOne($id);
		!empty($data) && $cache->set($key, $data);
		!empty($data) && $this->row[$id] = $data;
		
		if ( $col ) 
		{
			return isset($data[$col]) ? $data[$col] : '';
		}
		else
		{
			return $data;
		}
	}

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	array	$rule  数据查询规则
	 * @return	array
	 */
	public function find($rule)
	{
		if ( isset($rule['limit']) && $rule['limit'] == 1 )
		{
			return parent::find($rule);
		}

		$rule['col'] = array($this->pk);
		$list        = parent::find($rule);
		foreach ( $list as &$row )
		{
			$row = $this->findOne($row[$this->pk]);
		}
		$rule    = null;

		return $list;
	}

	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	array
	 */
	public function findAll($rule)
	{
		$rule['col'] = array($this->pk);
		$data        = parent::findAll($rule);
		$list        = array( 'rows' => array() );

		foreach ( $data['rows'] as $row )
		{
			$list['rows'][] = $this->findOne($row[$this->pk]);
		}		
		$list['total'] = $data['total'];
		$data = $rule  = null;

		return $list;
	}

	/**
	 * 创建一条数据
	 *
	 * @access	public
	 * @param	array	$data	数据信息[键值对]
	 * @return	int				0失败、大于0成功
	 */
	public function create($data)
	{
		$data['updated'] = time();
		$id              = parent::create($data);
		
		if ( $id )
		{
			$rule['scope'] = array('updated' => array(time()-4, time()+1));
			$rule['order'] = array('updated' => 'desc');
			$rule['limit'] = 20;
			$list          = parent::find($rule);
			$this->updateCache($list, 1);
			$list = $rule  = null;
		}
		
		return $id;
	}

	/**
	 * 修改数据
	 *
	 * @access	public
	 * @param	array	$data	被修改的数据[键值对]
	 * @param	array	$rule	数据修改规则
	 * @return	bool
	 */
	public function modify($data, $rule)
	{
		$data['updated'] = time();
		$bool            = parent::modify($data, $rule);
		$rule            = array();
		$rule['scope']   = array('updated' => array(time()-4, time()+1));
		$rule['order']   = array('updated' => 'desc');
		$rule['limit']   = 20;
		$list            = parent::find($rule);
		$this->updateCache($list, 1);
		$list = $rule    = $data = null;
		$this->change    = true;
		
		return $bool;
	}

	/**
	 * 删除数据 
	 *
	 * @access	public
	 * @param	array	$rule	数据删除规则
	 * @return	bool
	 */
	public function remove($rule)
	{
		$rule['col']   = array($this->pk);
		$rule['limit'] = 20;
		$list          = parent::find($rule);
		$this->updateCache($list, 2);
		$list          = null;
		$this->change  = true;
		
		return parent::remove($rule);
	}
	
	/**
	 * 更新缓存 
	 *
	 * @access	public
	 * @param	array	$list	数据列表
	 * @param	int		$op		1写入缓存、2删除缓存
	 * @return	void
	 */
	private function updateCache($list, $op = 1)
	{
		$cache       = $this->getCacheObject();
		$cache->path = EntityCacheDir.'/'.$this->tableKey;
		foreach ( $list as $data )
		{
			$key = $this->tableKey.':'.$data[$this->pk];
			if ( $op == 1 )
			{
				$cache->set($key, $data);
			}
			else
			{
				$cache->remove($key);
			}
		}
		$list = $cache = $key = $data = null;
	}

	/**
	 * 释放资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->cacheId = null;
		parent::__destruct();
	}
}
?>