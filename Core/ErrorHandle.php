<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 异常|错误信息输出、记录(框架核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class ErrorHandle
{
	/**
	 * 异常、错误信息输出
	 *
	 * @access	public
	 * @param	array	$e	异常、错误信息
	 * @return	void
	 */
	public static function output($e)
	{
		if ( Spring::$mode == 1 )
		{
			if ( file_exists(ResourceDir.'/MessageBox/error.html') )
			{
				$msgFile = ResourceDir.'/MessageBox/error.html';
				$path    = '/'.ResourceDir.'/';
			}
			else
			{
				$msgFile = LibDir.'/Template/UI/error.html';
				$path    = '';
			}

			$msg  = "所在文件:{$e['file']}<br>";
			$msg .= "所在行数:{$e['line']}行<br><br>";
			$msg .= "详细描述:{$e['desc']}<br>";
			
			require($msgFile);
		}
		else
		{
			$msg  = "所在文件:{$e['file']}\n";
			$msg .= "所在行数:{$e['line']}行\n\n";
			$msg .= "详细描述:{$e['desc']}\n";
			print iconv('utf-8', 'gbk', $msg);
		}
	}

	/**
	 * 异常、错误信息记录
	 *
	 * @access	public
	 * @param	array	$e		异常、错误信息
	 * @param	string	$level	错误级别
	 * @return	void
	 */
	public static function record($e, $level)
	{
		if ( is_array($e) )
		{
			$msg  = "所在文件:\t{$e['file']}\r\n";
			$msg .= "所在行数:\t第{$e['line']}行\r\n";
			$msg .= "详细描述:\t{$e['desc']}\r\n";
		}
		else
		{
			$msg = $e."\r\n";
		}

		$host    = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']     : '';
		$uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$reqUrl  = $host & $uri ? 'url: http://'.$host.$uri : '';
		$content = "【".date("Y-m-d H:i:s", time())."】\r\n".$reqUrl."\r\n".$msg."\r\n";
		$logFile = "spring.{$level}-".date("Y-m-d", time()).".log";   
		Log::write($content, $logFile, LogDir.'/Error');
	}
}
?>