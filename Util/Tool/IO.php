<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 文件、目录操作
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class IO
{
	/**
	 * 获取指定目录下的所有文件、目录
	 *
	 * @access public
	 * @param  string	$path	目录名
	 * @return array
	 */
	public static function getChild($path)
	{
		static $tree = array();
		$list        = array();

		if ( is_dir($path) ) {
			$files = scandir($path);
			foreach ( $files as $file ) {
				if ( $file != "." && $file != ".." ) {
					if ( is_dir($path."/".$file) ) {
						IO::getChild($path."/".$file);
					} else {
						$list[] = $path."/".$file;
					}
				}
			}
			$tree[$path] = $list;
			$list        = array();
		}
		return $tree;
	}

	/**
	 * 创建多级目录
	 *
	 * @access public
	 * @param  string	$path	目录名
	 * @return bool
	 */
	public static function createDir($path)
	{
		if ( empty($path) ) {
			return false;
		}

		if ( file_exists($path) ) {
			return true;
		}
		$dirs  = explode('/', $path);
		$total = count($dirs);
		$temp  = '';
		
		for ( $i = 0; $i < $total; $i++) {
			$temp .= $dirs[$i].'/';
			if ( !is_dir($temp) ) {
				if ( !@mkdir($temp) ) return false;
				@chmod($temp, 0777);
			}
		}
		return true;
	}

	/**
	 * 复制或移动源目录下的所有子目录、文件到目的目录
	 *
	 * @access public
	 * @param  string	$src	源目录名
	 * @param  string	$dest	目的目录
	 * @param  int		$self	0为不复制、移动自身|1为复制、移动自身
	 * @param  int		$move	0为复制、1为移动
	 * @return bool
	 */
	public static function move($src, $dest = '', $self = 0, $move = 0)
	{
		if ( empty($dest) || !file_exists($dest) || $src == $dest ) {
			return false;
		}

		$names   = explode("/", $src);
		$name    = array_pop($names);
		$tree    = IO::getChild($src);
		$folders = array_keys($tree);
		foreach ( $folders as $folder ) {
			
			$folder = $src != $folder ? str_replace("$src/", "", $folder) : "";
			if ( $self == 0 ) {
				$folder && IO::createDir($dest.'/'.$folder);
			} else {
				$folder && IO::createDir($dest.'/'.$name.'/'.$folder);
			}
		}

		foreach ( $tree as $folder ) {
			foreach ( $folder as $file ) {
				if ( $self == 0 ) {
					$destFile = $dest.'/'.str_replace("$src/", "", $file);
				} else {
					$destFile = $dest.'/'.$name.'/'.str_replace("$src/", "", $file);
				}
				copy($file, $destFile);
			}
		}

		foreach ( $tree as $folder ) {
			foreach ( $folder as $file ) {				
				if ( $self == 0 ) {
					$destFile = $dest.'/'.str_replace("$src/", "", $file);
				} else {
					$destFile = $dest.'/'.$name.'/'.str_replace("$src/", "", $file);
				}

				if ( !file_exists($destFile) ) {
					return false;
				}
			}
		}
		
		if ( $move ) {
			IO::removeAll($src);
		}
		
		return true;
	}

	/**
	 * 查找指定目录下的文件(模糊查找)
	 *
	 * @access public
	 * @param  string	$path	目录名
	 * @param  string	$name	文件名关键字
	 * @return array
	 */
	public static function find($path, $word)
	{
		static $result = array();
		if ( is_dir($path) ) {
			$files = scandir($path);
			foreach ( $files as $file ) {
				if ( $file != "." && $file != ".." ) {
					if ( is_dir("$path/$file") ) {
						IO::find("$path/$file", $word);
					}
					
					if ( is_file("$path/$file") && preg_match("/$word/i", $file) ) {
						$result[] = "$path/$file";
					}
				}
			}
		}

		return $result;
	}

	/**
	 * 删除指定目录下的文件(模糊删除)
	 *
	 * @access public
	 * @param  string	$path	目录名
	 * @param  string	$name	文件名关键字
	 * @return void
	 */
	public static function remove($path, $name)
	{
		$files = IO::find($path, $name);
		foreach ( $files as $file ) {
			unlink($file);
		}
	}

	/**
	 * 删除指定目录下的所有文件、目录(包括自身)
	 *
	 * @access public
	 * @param  string	$path	目录名
	 * @return void
	 */
	public static function removeAll($path)
	{
		if ( is_dir($path) ) {
			$files = scandir($path);
			foreach ( $files as $file ) {
				if ( $file != "." && $file != ".." ) {
					if ( is_dir("$path/$file") ) {
						IO::removeAll("$path/$file");
					} else {
						unlink("$path/$file");
					}
				}
			}
			rmdir($path);
		}
	}
}
?>