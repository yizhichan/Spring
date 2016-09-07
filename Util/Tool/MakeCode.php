<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体生成工具
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class MakeCode
{
	/**
	 * 数据库配置文件目录
	 */
	public  static $configFileDir = 'Config/Db';

	/**
	 * 数据库对象
	 */
	private static $db = null;

	/**
	 * 生成实体、模型、表路由配置文件
	 *
	 * @access	public
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	array
	 */
	public static function create($configs)
	{
		$code = '';
		foreach ( $configs as $config ) {
			MakeCode::$db             = new DbQuery();		
			MakeCode::$db->configFile = MakeCode::$configFileDir.'/'.$config['name'].'.master.config.php';
			MakeCode::createApiFile($config);
			MakeCode::createModelFile($config);
			MakeCode::createTableRouteConfigFile($config);
			$configFile = $config['name'].".table.config.php";
			$code .= "require('$configFile');\r\n";
		}
		file_put_contents(ConfigDir."/Table/table.route.config.php", "<?\r\n$code?>");		
	}

	/**
	 * 构造配置信息
	 *
	 * @access	private
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	array
	 */
	private static function configure($config)
	{
		$tables = self::getTable($config);
		$prefix = $config['prefix'];

		foreach ( $tables as $table ) {
			if ( substr($table, 0, strlen($prefix)) != $prefix ) {
				continue;
			}

			$pk        = "id";
			$fields    = self::$db->fetchAll("desc {$table}");
			foreach ($fields as $data) {
				if ( $data['Key'] == 'PRI' ) {
					$pk = $data['Field'];
					break;
				}
			}
			
			$name      = substr($table, strlen($prefix), strlen($table));
			$items     = explode("_", $name);
			$className = '';
			$tableKey  = '';
			foreach ( $items as $item ) {
				$className .= ucfirst($item);
				if ( $tableKey )  {
					$tableKey .= ucfirst($item);
				} else {
					$tableKey = $item;
				}
			}
			
			$list[]    = array(
				'pk'        => $pk,
				'name'      => $name,
				'tableKey'  => $tableKey,
				'apiName'   => $className."Api",
				'modelName' => $className."Model",
				'apiFile'   => str_replace('_', '', $name).".api.php",
				'modelFile' => str_replace('_', '', $name).".model.php",
				);
		}
		
		return $list;
	}

	/**
	 * 获取数据表
	 *
	 * @access	private
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	array
	 */
	private static function getTable($config)
	{
		$sql    = "show tables";
        $key    = "Tables_in_".$config['db'];
		$list   = array();
		$prefix = $config['prefix'];
		$list   = self::$db->fetchAll($sql);
		$tables = array();

		if ( isset($config['contain']) && is_string($config['contain']) && $config['contain'] == '*' ) {
			foreach ( $list as $val ) {
				$tables[] = $val[$key];
			}
			return $tables;
		}
		
		if ( isset($config['contain']) && is_array($config['contain']) ) {
			foreach ( $list as $val ) {
				if ( in_array($val[$key], $config['contain']) ) {
					$tables[] = $val[$key];
				}
			}
		}

		return $tables;
	}
	
	/**
	 * 生成api文件
	 *
	 * @access	private
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['mode']		数据库访问方式[mode = pdo|mysql]
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	void
	 */
	private static function createApiFile($config)
	{
		$list   = self::configure($config);
		$apiTpl = file_get_contents(LibDir.'/Template/Code/tbl.api.php');
		foreach ( $list as $data ) {
			$content = str_replace('TblApi', $data['apiName'], $apiTpl);
			$content = str_replace('tbl', $data['tableKey'], $content);
            $content  	= str_replace('id', $data['pk'], $content);
            $filename 	= EntityDir."/$data[apiFile]";
            if ( !file_exists($filename) ) {
                file_put_contents($filename, $content);
			}
		}
	}
	
	/**
	 * 生成model文件
	 *
	 * @access	private
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['mode']		数据库访问方式[mode = pdo|mysql]
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	void
	 */
	private static function createModelFile($config)
	{
		//if ( !isset($config['createModel']) || !$config['createModel'] ) {
		//	return ;
		//}

		$list     = self::configure($config);
		$modelTpl = file_get_contents(LibDir.'/Template/Code/tbl.model.php');
		foreach ( $list as $data ) {
			$content = str_replace('TblModel', $data['modelName'], $modelTpl);
			$content = str_replace('2014-12-09', date("Y-m-d", time()), $content);
            $mkdir 		= ModelDir."/$config[name]";

			if ( !self::findFile(ModelDir, $data['modelFile']) ) {
            	if (!is_dir($mkdir)) mkdir($mkdir); // 如果不存在则创建
                file_put_contents($mkdir."/$data[modelFile]", $content);
			}
		}
	}

	/**
	 * 目录下查找文件(仅支持一级目录)
	 *
	 * @access	private
	 * @param	string	$name	文件名
	 * @return	string
	 */
	private static function findFile($path, $name)
	{
		if ( file_exists("$path/$name") )
		{
			return "$path/$name";
		}

		$files = scandir($path);
		foreach ( $files as $file )
		{
			if ( is_dir("$path/$file") && file_exists("$path/$file/$name") )
			{
				return "$path/$file/$name";
			}
		}
		return '';
	}
	
	/**
	 * 生成表配置文件
	 *
	 * @access	private
	 * @param	string	$config['name']		数据库名 
	 * @param	string	$config['prefix']	表前缀
	 * @param	string	$config['file']		数据库配置文件
	 * @param	string	$config['mode']		数据库访问方式[mode = pdo|mysql]
	 * @param	string	$config['path']		表配置文件存放路径
	 * @return	void
	 */
	private static function createTableRouteConfigFile($config)
	{
		$dbName     = $config['name'];
		$prefix     = $config['prefix'];
		$configFile = "$dbName.master.config.php";

		$code    = "\$prefix\t\t= '$prefix';\r\n\$dbId\t\t= '$dbName';\r\n\$configFile\t= array( ConfigDir.'/Db/$configFile' );\r\n\r\n";
		$list = self::configure($config);
		foreach ( $list as $data ) {
			$key   = $data['tableKey'];
			$name  = $data['name'];
			$code .= "\$tbl['$key'] = array(\r\n\t'name'\t\t=> \$prefix.'$name',\r\n\t'dbId'\t\t=> \$dbId, \r\n\t'configFile'=> \$configFile,\r\n);\r\n\r\n";
		}
		file_put_contents(ConfigDir."/Table/$dbName.table.config.php", "<?\r\n$code\r\n?>");
	}
}
?>