<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层Solr接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SolrApi extends Entity
{
	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	array	$rule  数据查询规则
	 * @return	array
	 */
	public function find($rule)
	{
		return $this->com('solr')->find($this->tableKey, $rule);
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
		return $this->com('solr')->findAll($this->tableKey, $rule);
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
		return $this->com('solr')->count($this->tableKey, $rule);
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
		return $this->com('solr')->create($this->tableKey, $data);
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
		return $this->com('solr')->remove($this->tableKey, $rule);
	}
}
?>