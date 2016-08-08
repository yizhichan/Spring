<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 模板引擎(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class View
{
	/**
	 * 模板根目录
	 */
	public  $tplDir  = null;

	/**
	 * 模板变量数组
	 */
	private $tplVar  = null;


	/**
	 * 初始化工作
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
	}

	/**
	 * 负责资源的清理工作
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->tplVar  = null;
		$this->tplDir  = null;
	}

	/**
	 * 设置模板变量
	 *
	 * @access	public
	 * @param	string	$key	变量名
	 * @param	mixed	$val	变量值
	 * @return	void
	 */
	public function set($key, $val)
	{
		$this->tplVar[$key] = $val;
	}

	/**
	 * 装载模板文件并显示
	 *
	 * @access	public
	 * @param	string	$file	文件名
	 * @return	void
	 */
	public function display($file = null)
	{
		if ( $file )
		{
			$file = $this->tplDir .'/'.$file;
			if ( !file_exists($file) ) 
			{
				throw new SpringException("模板文件: $file 不存在!");
			}

			if ( is_array($this->tplVar) && !empty($this->tplVar) ) 
			{
				extract($this->tplVar);
			}
			require($file);
		}
	}

	/**
	 * 装载模板文件并显示(php变量js化)
	 *
	 * @access	public
	 * @param	string	$file	文件名
	 * @return	void
	 */
	public function output($file = null)
	{
		if ( $file )
		{
			$file = $this->tplDir .'/'.$file;
			if ( !file_exists($file) ) 
			{
				throw new SpringException("模板文件: $file 不存在!");
			}

			if ( is_array($this->tplVar) && !empty($this->tplVar) ) 
			{
				$data = json_encode($this->tplVar);
			}
			require($file);
		}
	}

	/**
	 * 装载模板文件并返回解析后的html
	 *
	 * @access	public
	 * @param	string	$file	文件名
	 * @return	string
	 */
	public function fetch($file = null)
	{
		if ( $file )
		{
			$file = $this->tplDir .'/'.$file;
			if ( !file_exists($file) ) 
			{
				throw new SpringException("模板文件: $file 不存在!");
			}

			if ( is_array($this->tplVar) && !empty($this->tplVar) ) 
			{
				extract($this->tplVar);
			}
			ob_start();
			require($file);
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
		return '';
	}
}

function ob_gzip($content)
{
	$bool = isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") 
		    ? true
		    : false;
	if ( !headers_sent() && extension_loaded("zlib") && $bool )
    {
        $content = gzencode($content,9);
       
        header("Content-Encoding: gzip");
        header("Vary: Accept-Encoding");
        header("Content-Length: ".strlen($content));
    }
    return $content;
}
?>