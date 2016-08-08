<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层基类(ORM核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class BaseCacheApi extends Entity
{
	/**
	 * 是否开启缓存
	 */
	private $isCached = true;


	/**
	 * 缓存开关
	 *
	 * @access	public
	 * @param	bool	$open	缓存标识(true开启缓存、false关闭缓存)
	 * @return	void
	 */
	public function setCache($open = true)
	{
		$this->isCached = $open;
	}
	
	/**
	 * 得到指定ID的数据(主键查询)
	 *
	 * @access	public
	 * @param	int		$id		主键id
	 * @param	string	$col	字段名称[只支持一个字段]
	 * @return	mixed(array|string)
	 */
	public function findOne($id, $col = '')
	{
		if ( !$this->isCached )
		{
			return parent::findOne($id, $col);
		}

		if ( empty($id) )
		{
			return array();
		}
		
		$rule['eq']  = array( $this->pk => $id );
		$rule['key'] = $this->tableKey;
		$rule['act'] = 'findOne';
		$cache       = $this->getCacheObject();
		$data        = $cache->get($rule);
		
		if ( empty($data) )
		{
			$data = parent::findOne($id);
			!empty($data) && $cache->set($rule, $data, $this->expire);
		}
		
		if ( $col )
		{
			return isset($data[$col]) ? $data[$col] : '';
		}
		
		return $data;
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
		if ( !$this->isCached )
		{
			return parent::find($rule);
		}

		$rule['key'] = $this->tableKey;
		$rule['act'] = 'find';
		!isset($rule['limit']) && $rule['limit'] = 1;
		$cache       = $this->getCacheObject();
		$data        = $cache->get($rule);
		
		if ( !empty($data) )
		{
			return $data;
		}
		
		$method = $rule['limit'] == 1 ? 'findOne' : 'find';
		$data   = parent::find($rule);
		!empty($data) && $cache->set($rule, $data, $this->expire);
		
		return $data;
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
		if ( !$this->isCached )
		{
			return parent::findAll($rule);
		}

		$rule['key']  = $this->tableKey;
		$rule['act']  = 'findAll';
		$page         = isset($rule['page']) ? intval($rule['page']) : 1;
		$rule['page'] = $page >= 1 ? $page : 1;
		$cache        = $this->getCacheObject();
		$data         = $cache->get($rule);

		if ( !empty($data) )
		{
			return $data;
		}
		
		$data = parent::findAll($rule);
		!empty($data) && $cache->set($rule, $data, $this->expire);
		
		return $data;
	}

	/**
	 * 按条件统计数据
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	int
	 */
	public function count($rule)
	{
		if ( !$this->isCached )
		{
			return parent::count($rule);
		}

		$rule['key'] = $this->tableKey;
		$rule['act'] = 'count';
		$cache       = $this->getCacheObject();
		$data        = $cache->get($rule);

		if ( !empty($data) )
		{
			return $data;
		}

		$data = parent::count($rule);
		!empty($data) && $cache->set($rule, $data, $this->expire);
		
		return $data;
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
		return parent::create($data);
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
		return parent::modify($data, $rule);
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
		return parent::remove($rule);
	}
}
?>