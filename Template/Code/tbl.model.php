<?
/**
 * 工作日志
 *
 * 获取工作日志id分页列表、添加工作日志、编辑工作日志、删除工作日志、删除所有工作日志
 * 
 * @package	Model
 * @author	void
 * @since	2014-12-09
 */
class TblModel extends AppModel
{
	/**
	 * 工作日志标签
	 */
	public $tags  = array(
		'1'  => '熟悉需求',
		'2'  => '编写代码',
		'3'  => '工作会议',
		'4'  => '撰写文档',
		'5'  => '学习新技术',
		'6'  => '帮带、分享',
		'15' => '其他事务',
		);

	/**
	 * 获取工作日志id分页列表
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @param	int		$userId		用户id
	 * @param	int		$page		页码
	 * @param	int		$num		返回条数
	 * @return	array
	 */
	public function getIdsPageList($userId, $page = 1, $num = 20)
	{
		$r['eq']    = array('userId' => $userId);
		$r['col']   = array('id');
		$r['order'] = array('doTime' => 'desc');
		$r['page']  = $page;
		$r['limit'] = $num;

		return $this->findAll($r);
	}

	/**
	 * 添加工作日志
	 * @author	void
	 * @since	2014-12-09
	 * 
	 * @access	public
	 * @param	int		$userId		用户id
	 * @param	array	$data		日志数据
	 * @return	int
	 */
	public function add($userId, $data)
	{
		$event = array(
			'userId'     => $userId,
			'content'    => $data['content'],
			'tagIds'     => $data['tagIds'],
			'attachment' => $data['attachment'],
			'doTime'     => $data['doTime'],
			'created'    => time(),
			);
		
		return $this->create($event);
	}

	/**
	 * 编辑工作日志
	 * @author	void
	 * @since	2014-12-09
	 * 
	 * @access	public
	 * @param	int		$userId		用户id
	 * @param	array	$data		日志数据
	 * @return	bool
	 */
	public function edit($userId, $data)
	{
		if ( !$this->check($userId, $data['id']) ) {
			return false;
		}

		$event = array(
			'content'    => $data['content'],
			'tagIds'     => $data['tagIds'],
			'attachment' => $data['attachment'],
			'doTime'     => $data['doTime'],
			'updated'    => time(),
			);
		$r['eq'] = array('userId' => $userId, 'id' => $data['id']);
		
		return $this->modify($data, $r);
	}
	
	/**
	 * 删除工作日志
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @param	int		$userId		用户id
	 * @param	int		$id			工作日志id
	 * @return	bool
	 */
	public function delete($userId, $id)
	{
		if ( !$this->check($userId, $id) ) {
			return false;
		}
		
		$r['eq'] = array('userId' => $userId, 'id' => $id);

		return $this->remove($r);
	}

	/**
	 * 删除所有工作日志
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @param	int		$userId		用户id
	 * @return	bool
	 */
	public function deleteAll($userId)
	{
		$r['eq'] = array('userId' => $userId);

		return $this->remove($r);
	}

	/**
	 * 检查工作日志是否属于所有者
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @param	int		$userId		用户id
	 * @param	int		$id			工作日志id
	 * @return	bool
	 */
	private function check($userId, $id)
	{
		$data = $this->get($id);
		if ( empty($data) || $userId != $data['userId'] ) {
			return false;
		}
		
		return true;
	}
}
?>