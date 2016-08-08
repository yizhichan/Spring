<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据库访问接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
interface IDataSource
{
	/**
	 * 获取结构信息
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	array
	 */
	public function struct($tableKey);

	/**
	 * 获取一条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function findOne($tableKey, $rule);

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function find($tableKey, $rule);
	
	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	array
	 */
	public function findAll($tableKey, $rule);

	/**
	 * 统计数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据查询规则
	 * @return	int
	 */
	public function count($tableKey, $rule);
	
	/**
	 * 创建一条数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$data		数据信息[键值对]
	 * @param	array	$rule		数据创建规则
	 * @return	int     0失败、大于0成功
	 */
	public function create($tableKey, $data, $rule);
	
	/**
	 * 修改数据
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$data		数据信息[键值对]
	 * @param	array	$rule		数据修改规则
	 * @return	bool
	 */
	public function modify($tableKey, $data, $rule);

	/**
	 * 删除数据 
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @param	array	$rule		数据删除规则
	 * @return	bool
	 */
	public function remove($tableKey, $rule);

	/**
	 * 开始事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function begin($tableKey);

	/**
	 * 提交事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function commit($tableKey);

	/**
	 * 回滚事务
	 *
	 * @access	public
	 * @param	string	$tableKey	数据表标识
	 * @return	bool
	 */
	public function rollBack($tableKey);
}
?>