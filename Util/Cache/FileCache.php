<?
/**
 +------------------------------------------------------------------------------
 * Spring框架	php文件代码缓存组件
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class FileCache implements ICache
{
	/**
	 * 缓存路径
	 */
	public $path = null;


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
	 * 释放资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->path = null;
	}

	/**
	 * 写入数据
	 *
	 * @access	public
	 * @param	mixed	$key		键 
	 * @param	mixed   $value		值
	 * @param	int		$expire		缓存时间(0持久存储)
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function set($key, $value, $expire = 0, $encoding = 0)
	{
		if ( empty($key) || !is_string($key) ) 
		{
			return false;
		}

		$key  = trim($key);
		$file = $this->path.'/'.$key.'.php';
		
		return $this->createFile('var', $value, $file);
	}

	/**
	 * 获取数据
	 *
	 * @access	public
	 * @param	mixed	$key		键 
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	mixed
	 */
	public function get($key, $encoding = 0)
	{
		if ( empty($key) || !is_string($key) ) 
		{
			return '';
		}

		$key  = trim($key);
		$file = $this->path.'/'.$key.'.php';
		
		if ( !file_exists($file) )
		{
			return '';
		}

		return require($file);
	}

	/**
	 * 删除数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function remove($key, $encoding = 0)
	{
		if ( empty($key) || !is_string($key) ) 
		{
			return false;
		}

		$key  = trim($key);
		$file = $this->path.'/'.$key.'.php';
		if ( file_exists($file) )
		{
			@unlink($file);
			return true;
		}
		return false;
	}

	/**
	 * 清空数据
	 *
	 * @access	public
	 * @return	bool
	 */
	public function clear()
	{
		if ( file_exists($this->path) )
		{
			$handle = opendir($this->path);
			while ( $file = readdir($handle) )
			{
				$file = $this->path . DIRECTORY_SEPARATOR . $file;
				if( !is_dir($file) && $file != '.' && $file != '..' ) 
				{
					if ( !@unlink($file) ) return false;
				}
			}
		}
		return true;
	}

	/**
	 * 检查缓存键是否存在
	 *
	 * @access	public
	 * @param	string	$key	键
	 * @return	bool
	 */
	public function exist($key)
	{
		if ( empty($key) || !is_string($key) ) 
		{
			return false;
		}

		$key  = trim($key);
		$file = $this->path.'/'.$key.'.php';

		return file_exists($file);
	}

	/**
	 * 删除数据
	 *
	 * @access	private
	 * @param	string	$name	变量名
	 * @param	mixed	$value	变量值
	 * @param	string	$file	缓存文件
	 * @return	bool
	 */
	private function createFile($name, $value, $file)
	{
		return @file_put_contents($file, "<?\r\nreturn ".var_export($value, true).";\r\n?>");
	}
}
?>