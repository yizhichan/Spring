<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据接收组件(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Inputer
{
	/**
	 * 过滤器
	 */
	public $filter = null;


	/**
	 * 初始化
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
	}
	
	/**
	 * 清理资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->filter = null;
	}

	/**
	 * 获取输入数据($_GET、$_POST)
	 *
	 * @access	public
	 * @return	array
	 */
	public function getInput()
	{
		$input = array_merge($_GET, $_POST);

		return get_class($this->filter) != 'stdClass' 
			   ? $this->filter->filter($input) 
			   : $input;
	}
}
?>