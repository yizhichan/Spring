<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 异常处理(框架核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SpringException extends Exception
{
	/**
	 * 检查是否安装pdo
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($msg, $code = 0)
	{
		parent::__construct($msg, $code);
	}
}
?>