<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层接口(ORM核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
interface IEntity
{
	/**
	 * 设置表切片
	 *
	 * @access	public
	 * @param	int		$slice	设置表切片(分表标识)
	 */
	public function slice($slice);

	/**
	 * 主从定位
	 *
	 * @access	public
	 * @param	int		$master		1为主服务器、0为从服务器
	 * @return	Entity
	 */
	public function locate($master);

	/**
	 * 得到结构信息
	 *
	 * @access	public
	 * @return	array
	 */
	public function struct();

	/**
	 * 得到指定ID的数据(主键查询)
	 *
	 * @access	public
	 * @param	int		$id		主键id
	 * @param	string	$col	字段名称[只支持一个字段]
	 * @return	mixed(array|string|int|float)
	 */
	public function findOne($id, $col = '');

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	array	$rule  数据查询规则
	 * @return	array
	 */
	public function find($rule);

	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	array
	 */
	public function findAll($rule);

	/**
	 * 按条件统计数据
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	int
	 */
	public function count($rule);

	/**
	 * 原始查询
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	array
	 */
	public function query($rule);

	/**
	 * 创建一条数据
	 *
	 * @access	public
	 * @param	array	$data  数据信息[键值对]
	 * @return	int     0失败、大于0成功
	 */
	public function create($data);
	
	/**
	 * 修改数据
	 *
	 * @access	public
	 * @param	array	$data	被修改的数据[键值对]
	 * @param	array	$rule	数据修改规则
	 * @return	bool
	 */
	public function modify($data, $rule);

	/**
	 * 删除数据 
	 *
	 * @param	array	$rule	数据删除规则
	 * @access	public
	 * @return	bool
	 */
	public function remove($rule);

	/**
	 * 开始事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function begin();

	/**
	 * 提交事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function commit();

	/**
	 * 回滚事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function rollBack();
}
?>