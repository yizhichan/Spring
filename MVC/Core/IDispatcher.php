<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 调度器接口(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
interface IDispatcher
{
	/**
	 * 解析URL参数
	 *
	 * @access	public
	 * @return	void
	 */
	public function parseReq();


	/**
	 * 获取控制器对象
	 *
	 * @access	public
	 * @return	Action对象
	 */
	public function getModule();


	/**
	 * 获取请求的操作
	 *
	 * @access	public
	 * @return	string
	 */
	public function getAction();
}
?>