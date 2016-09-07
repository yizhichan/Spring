<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 队列接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
interface IQueue
{
	/**
	 * 数据入队
	 *
	 * @access	public
	 * @param	string	$data	待入队数据
	 * @return	bool
	 */
	public function push($data);

	/**
	 * 数据出队
	 *
	 * @access	public
	 * @return	string
	 */
	public function pop();

	/**
	 * 队列长度(队列中元素个数)
	 *
	 * @access public
	 * @return int
	 */
	public function size();	
}
?>