<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 模型工厂(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class FileSearcher
{
	/**
	 * 文件查找（只支持一级目录）
	 *
	 * @access	private
	 * @param	string	$directory	目录
	 * @param	string	$destFile	目标文件
	 * @return	string
	 */
	public static function search($directory, $destFile)
	{
		if ( file_exists("$directory/$destFile") )
		{
			return "$directory/$destFile";
		}

		$files = scandir($directory);
		foreach ( $files as $file )
		{
			if ( $file != "." && $file != ".." )
			{
				if ( is_dir("$directory/$file") )
				{
					if ( file_exists("$directory/$file/$destFile") )
					{
						return "$directory/$file/$destFile";
					}
				}
			}
		}

		return '';
	}
}
?>