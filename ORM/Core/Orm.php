<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层工厂(ORM核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Orm
{
	/**
	 * 数据实体层存放目录
	 */
	public  static $entityDir = null;

	/**
	 * 实体对象缓存
	 */
	private static $caches    = array();

	
	/**
	 * 构造实体对象(外部调用入口)
	 *
	 * @access	public
	 * @param	string	$name	实体类名(不带后缀Api)
	 * @return	Entity
	 */
	public static function create($name = '')
	{
		if ( empty($name) )
		{
			throw new SpringException("实体类名不能为空!");
		}

		if ( isset(self::$caches[$name]) && !empty(self::$caches[$name]) )
		{
			return self::$caches[$name];
		}

		$file = self::$entityDir.'/'.strtolower($name).'.api.php';
		if ( !file_exists($file) )
		{
			throw new SpringException('找不到实体类文件 '.$file);
		}

		require_once($file);
		$className = $name.'Api';
		if ( !class_exists($className) ) 
		{
			throw new SpringException('找不到实体类 '.$className);
		}
		
		self::$caches[$name] = new $className();

		return self::$caches[$name];
	}
}
?>