<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 模型(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class Model extends ModelActionBase
{
	/**
	 * 实体类名
	 */
	public	  $entity   = '';

	/**
	 * 切片[0无切片、大于0的整数为切片标识]
	 */
	protected $slice    = 0;

	/**
	 * 实体对象数组
	 */
	protected $instance = array();

	/**
	 * 构造实体类对象
	 *
	 * @access	protected
	 * @param	string	$name	实体类名(不带后缀Api)
	 * @return	Entity
	 */
	protected function createEntity($name)
	{
		//实体对象缓存
		if ( !isset($this->instance[$name]) )
		{
			$this->instance[$name] = Orm::create($name);
			$this->instance[$name]->debug = $this->debug;
		}

		return $this->instance[$name];
	}

	/**
	 * 主从定位
	 *
	 * @access	protected
	 * @param	int		$master		1为主服务器、0为从服务器
	 * @return	Entity
	 */
	protected function locate($master = 1)
	{
		return $this->createEntity($this->entity)->locate($master);
	}

	/**
	 * 更换实体
	 *
	 * @access	protected
	 * @param	string		$name	实体类名
	 * @return	void
	 */
	protected function change($name)
	{
		$this->entity = $name;

		return $this;
	}

	/**
	 * 开始事务
	 *
	 * @access	protected
	 * @param	string		$name	实体类名
	 * @return	bool
	 */
	protected function begin($name)
	{
		return $this->createEntity($name)->begin();
	}

	/**
	 * 提交事务
	 *
	 * @access	protected
	 * @param	string		$name	实体类名
	 * @return	bool
	 */
	protected function commit($name)
	{
		return $this->createEntity($name)->commit();
	}

	/**
	 * 回滚事务
	 *
	 * @access	protected
	 * @param	string		$name	实体类名
	 * @return	bool
	 */
	protected function rollBack($name)
	{
		return $this->createEntity($name)->rollBack();
	}

	/**
	 * 得到结构信息
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function struct()
	{
		return $this->createEntity($this->entity)->struct();
	}

	/**
	 * 获取实体对应的数据表名
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function getTableName()
	{
		return $this->createEntity($this->entity)->getTableName();
	}
	
	/**
	 * 得到指定ID的数据(主键查询)
	 *
	 * @access	protected
	 * @param	int			$id		主键id
	 * @param	string		$col	字段名称[只支持一个字段]
	 * @return	mixed(array|string)
	 */
	protected function findOne($id, $col = '')
	{
		$this->createEntity($this->entity)->slice($this->slice);
		
		return $this->createEntity($this->entity)->findOne($id, $col);
	}

	/**
	 * 获取多条数据
	 *
	 * @access	protected
	 * @param	array		$rule  数据查询规则
	 * @return	array
	 */
	protected function find($rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->find($rule);
	}

	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	protected
	 * @param	array		$rule	数据查询规则
	 * @return	array
	 */
	protected function findAll($rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->findAll($rule);
	}

	/**
	 * 按条件统计数据
	 *
	 * @access	protected
	 * @param	array		$rule	数据查询规则
	 * @return	int
	 */
	protected function count($rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->count($rule);
	}

	/**
	 * 原始查询
	 *
	 * @access	protected
	 * @param	array		$rule	数据查询规则
	 * @return	int
	 */
	protected function query($rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->query($rule);
	}

	/**
	 * 创建一条数据
	 *
	 * @access	protected
	 * @param	array		$data	数据信息[键值对]
	 * @return	int			0失败、大于0成功
	 */
	protected function create($data)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->create($data);
	}
	
	/**
	 * 修改数据
	 *
	 * @access	protected
	 * @param	array		$data	被修改的数据[键值对]
	 * @param	array		$rule	数据修改规则
	 * @return	bool
	 */
	protected function modify($data, $rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->modify($data, $rule);
	}

	/**
	 * 删除数据 
	 *
	 * @access	protected
	 * @param	array		$rule	数据删除规则
	 * @return	bool
	 */
	protected function remove($rule)
	{
		$this->createEntity($this->entity)->slice($this->slice);

		return $this->createEntity($this->entity)->remove($rule);
	}

	/**
	 * 获取表主键
	 *
	 * @access	protected
	 * @return	string
	 */
	protected function getPk()
	{
		return $this->createEntity($this->entity)->pk;
	}

	/**
	 * 初始化(构造模型时执行)
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
	}

	/**
	 * 释放资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->entity   = null;
		$this->instance = null;
		$this->slice    = null;
		$this->object   = null;
		$this->debug    = null;
	}
}
?>