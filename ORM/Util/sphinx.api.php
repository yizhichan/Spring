<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层sphinx接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SphinxApi extends Entity
{
	/**
	 * 索引名称,其值格式为: product 表示数据源只有一个索引
	 * 'productMain,productDelta' 表示数据源为主索引+增量索引
	 */
	protected $index   = '';

	/**
	 * 属性字段(参与索引的表字段)
	 */
	protected $field  = array();

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	array	$rule  数据查询规则
	 * @return	array
	 */
	public function find($rule)
	{
		$rule['pk']     = $this->pk;
		$rule['isPage'] = 0;

		return $this->com('sphinx')->find($this->index, $rule);
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
		$rule['pk']     = $this->pk;
		$rule['isPage'] = 1;

		return $this->com('sphinx')->findAll($this->index, $rule);
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
		$id = parent::create($data);
		if ( $id ) 
		{
			//写数据入增量表
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
		return parent::modify($data, $rule);
		$bool            = parent::modify($data, $rule);
		$rule            = array();
		$rule['scope']   = array('updated' => array(time()-4, time()+1));
		$rule['order']   = array('updated' => 'desc');
		$rule['limit']   = 10;
		$list            = parent::find($rule);
		$this->updateAttributes($list);
		$list = $rule    = null;
		
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
		return parent::remove($rule);
		$rule['col']   = array($this->pk);
		$rule['limit'] = 20;
		$list          = parent::find($rule);
		$field         = array('removed');

		foreach ( $list as $data )
		{
			$value = array($data[$this->pk] => array(1));
			$this->UpdateAttributes($this->index, $field, $value);
		}
		$list = $field = null;
		
		return parent::remove($rule);
	}

	/**
	 * 更新文档属性值
	 *
	 * @access private
	 * @param  array	$list	列表数据
	 * @return void
	 */
	private function updateAttributes($list)
	{
		foreach ( $list as $data )
		{
			$value = array();
			foreach ( $this->field as $key ) 
			{
				$data[$key] = isset($data[$key]) ? $data[$key] : 0;
				$value[]    = preg_match("/\d+\.\d+/", $data[$key]) 
							  ? floatval($data[$key])
							  : intval($data[$key]);
			}
			$value = array($data[$this->pk] => $value);
			$this->com('sphinx')->UpdateAttributes($this->index, $this->field, $value);
		}	
	}
}
?>