<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 应用程序入口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class Application
{
	/**
	 * 处理请求
	 *
	 * @access	public
	 * @return	void
	 */
	public function process()
	{
		$dispatcher             = Spring::getComponent($this->routerId);
		$dispatcher->rootPath   = Root;
		$dispatcher->errorMod   = ErrorMod;
		$dispatcher->defaultMod = DefaultMod;

		//解析URL参数,获取请求信息
		$dispatcher->parseReq();
		$module = $dispatcher->getModule();
		$action = $dispatcher->getAction();
		
		//呼叫控制器执行操作
		$module->call($action);
		$dispatcher = $module = $action = null;
	}
}
?>