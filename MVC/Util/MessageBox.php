<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 页面消息框(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class MessageBox
{
	/**
	 * 重定向消息框架
	 *
	 * @access	public
	 * @param	string  $msg		消息文本
	 * @param	string  $url		跳转地址
	 * @param	string  $scripts	待执行的多个JS文件地址
	 * @param	int		$seconds	停留时间(秒)
	 * @return  void
	 */
	public static function redirect($msg, $url, $scripts = array(), $seconds=1)
	{
		if ( empty($msg) ) 
		{
			if ( is_array($scripts) && !empty($scripts) ) 
			{
				print implode('', $scripts);
			}
			print "<script language='javascript'>";
			print "location.href='$url';";
			print "</script>";
			exit();
		}

		if ( file_exists(ResourceDir.'/MessageBox/redirect.html') )
		{
			$msgFile = ResourceDir.'/MessageBox/redirect.html';
			$path    = '/'.ResourceDir.'/';
		}
		else
		{
			$msgFile = LibDir.'/Template/UI/redirect.html';
			$path    = '';
		}

		$js = '';
		foreach ( $scripts as $script ) 
		{
			$js .= $script;
		}

		$gotoUrl  = "<meta http-equiv='Refresh' content='$seconds; url=$url'>";
		require($msgFile);
		exit();
	}

	/**
	 * 终止消息提示框
	 *
	 * @access	public
	 * @param	string  $msg	消息文本
	 * @return  void
	 */
	public static function halt($msg)
	{
		if ( file_exists(ResourceDir.'/MessageBox/halt.html') )
		{
			$msgFile = ResourceDir.'/MessageBox/halt.html';
			$path    = '/'.ResourceDir.'/';
		}
		else
		{
			$msgFile = LibDir.'/Template/UI/halt.html';
			$path    = '';
		}

		require($msgFile);
		exit();
	}
}
?>