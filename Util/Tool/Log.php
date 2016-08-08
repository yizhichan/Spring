<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 日志记录
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Log
{
	/**
	 * 日志记录
	 *
	 * @param	mixed	$content	日志内容
	 * @param	string  $file		日志文件名
	 * @param	string	$dir		日志存放目录
	 * @return	void
	 */
	public static function write($content, $file = 'log.log', $dir = '')
	{
		if ( preg_match('/php/i',$file) )
		{
			return ;
		}
		
		$logDir = $dir ? $dir : LogDir;
		IO::createDir($logDir);

		is_array($content) && $content = var_export($content, true);
		file_put_contents($logDir.'/'.$file, $content, FILE_APPEND);
	}
}
?>