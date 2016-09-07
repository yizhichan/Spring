<?
/**
 +------------------------------------------------------------------------------
 * Spring框架入口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */

//Spring版本号
define('Version', 'Spring3.1.4');

//调试级别：1非严格模式、2严格模式
defined('Debug')       or define('Debug', 1);

//Spring框架目录
defined('LibDir')      or define('LibDir', dirname(__FILE__));

//当前应用程序运行的根目录（rewrite时使用）
defined('Root')        or define('Root', '/');

//网站目录
defined('WebDir')      or define('WebDir', '.');

//资源目录
defined('ResourceDir') or define('ResourceDir', WebDir.'/Resource');

//应用代码存放目录
defined('AppDir')      or define('AppDir', WebDir.'/App');

//业务组件存放目录
defined('ModuleDir')    or define('ModuleDir', AppDir.'/Module'); 

//基础模型存放目录
defined('ModelDir')    or define('ModelDir', AppDir.'/Model'); 

//视图层存放目录
defined('ViewDir')     or define('ViewDir', AppDir.'/View');    

//控制器存放目录
defined('ActionDir')   or define('ActionDir', AppDir.'/Action');

//表单组件存放目录
defined('FormDir')     or define('FormDir', AppDir.'/Form');

//实体层存放目录
defined('EntityDir')   or define('EntityDir', AppDir.'/Entity');	

//api代理存放目录
defined('BiDir')       or define('BiDir', AppDir.'/Bi');

//公用组件库目录
defined('UtilDir')	   or define('UtilDir', AppDir.'/Util');

//定义项目动态资源目录
defined('DataDir')     or define('DataDir', WebDir.'/Data');

//定义项目静态资源目录
defined('StaticDir')   or define('StaticDir', Root.'Static/');

//指定默认控制器 
defined('DefaultMod')  or define('DefaultMod', 'Index');

//指定发生404时的控制器 
defined('ErrorMod')    or define('ErrorMod', 'Error');

//配置文件存放目录
defined('ConfigDir')   or define('ConfigDir', WebDir.'/Config');

//日志存放目录
defined('LogDir')	   or define('LogDir', ResourceDir.'/Log');			

//缓存组件配置信息目录
defined('CacheDir')	   or define('CacheDir', ResourceDir.'/Cache');

//实体数据缓存目录
defined('EntityCacheDir') or define('EntityCacheDir', CacheDir.'/Entity');

//是否缓存对象资源
defined('IsCached')	   or define('IsCached', false);

//指定编码
header("Content-type: text/html; charset=utf-8");

//设置时间区域
ini_set("date.timezone", "Asia/Shanghai");

class Spring
{
	/**
	 * 运行模式(1为web、2为控制台)
	 */
	public static $mode      = 1;

	/**
	 * 错误、异常处理钩子函数
	 */
	public static $hook		 = '';

	/**
	 * 类地图(键为类名、值为类文件路径)
	 */
	private static $classMap = array();
	
	
	/**
	 * Spring框架入口
	 *
	 * @access	public
	 * @param	string	$mode	运行模式(1为web、2为控制台)
	 * @return	void
	 */
	public static function run($mode = 1)
	{
		self::init();
		self::$mode     = $mode;
		$appName        = $mode == 1 ? 'WebApplication' : 'ConsoleApplication';
		$app            = new $appName();
		$app->process();
		$app            = null;
		ServiceFactory::dispose();
		self::$classMap = null;
	}

	/**
     * Spring提供可引用内部组件
     *
     * @access      public
     * @return      obj
     */
    public static function out($mod='include')
    {
        self::init();
        $appName        = 'IncludeApplication';
        $app            = new $appName();
        return $app->process($mod);
    }


	/**
	 * 框架初始化
	 *
	 * @access	private
	 * @return	void
	 */
	public static function init()
	{
		//设置错误、异常处理句柄
		register_shutdown_function(array("Spring", "fatalError"));
		set_error_handler(array("Spring", "appError"));
		set_exception_handler(array("Spring", "appException"));

		//自动加载类
		spl_autoload_register(array('Spring', 'loader'));

		ServiceFactory::$cacheDir   = CacheDir;
		ServiceFactory::$isCached   = IsCached;
		ServiceFactory::$configFile = LibDir.'/Config/map.config.php';
		Orm::$entityDir             = EntityDir;
		
		//载入应用所需类库
		if ( file_exists(UtilDir.'/import.php') )
		{
			require(UtilDir.'/import.php');
		}
	}
	
	/**
	 * 获取组件
	 *
	 * @access	public
	 * @param	string	$name	组件标签
	 * @return	object
	 */
	public static function getComponent($name)
	{
		return ServiceFactory::getObject($name);
	}

	/**
	 * 类文件自动加载
	 *
	 * @access	public
	 * @param	string	$className	类名(区分大小写)
	 * @return	bool
	 */
	public static function loader($className)
	{
		if ( empty(self::$classMap) )
		{
			$classMapA = array();
			$classMapB = require(LibDir.'/Config/classmap.config.php');
			if ( file_exists(ConfigDir.'/Extension/classmap.config.php') )
			{
				$classMapA = require(ConfigDir.'/Extension/classmap.config.php');
			}

			self::$classMap = array_merge($classMapA, $classMapB);
		}
		
		if ( isset(self::$classMap[$className]) )
		{
			require(self::$classMap[$className]);
		}
		
		return class_exists($className, false) || interface_exists($className, false);
	}

	/**
	 * 异常处理
	 *
	 * @access	public
	 * @param	Exception	$e	异常处理对象
	 * @return	void
	 */
	public static function appException($e)
	{
		$error             = array();
		$error['desc']     = $e->getMessage();
        $trace             = $e->getTrace();
        if ( $trace[0]['function'] == 'E' )
		{
			$error['file'] = $trace[0]['file'];
			$error['line'] = $trace[0]['line'];
        }
		else
		{
			$error['file'] = $e->getFile();
			$error['line'] = $e->getLine();
        }
		ErrorHandle::record($e, 'error');
        
		if ( Debug == 2 )
		{
			self::halt($error);
		}
	}

	/**
	 * 错误处理
	 *
	 * @access	public
	 * @return	void
	 */
	public static function appError($errno, $errstr, $errfile, $errline)
	{
		if ( !$errno )
		{
			return ;
		}

		$errors    = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
		$e['desc'] = $errstr;
		$e['file'] = $errfile;
		$e['line'] = $errline;
		if ( in_array($errno, $errors) )
		{
			if ( php_sapi_name() != "cli" )
			{
				ob_end_clean();
			}

			ErrorHandle::record($e, 'error');
			self::halt($e);
		}
		else
		{
			ErrorHandle::record($e, 'notice');
			if ( Debug == 2 )
			{
				self::halt($e);
			}
		}
	}

	/**
	 * 致命错误处理
	 *
	 * @access	public
	 * @return	void
	 */
	public static function fatalError()
	{
		$errors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
		$error  = error_get_last();
		if ( isset($error['type']) && in_array($error['type'], $errors) )
		{
			$e['desc']  = $error['message'];
			$e['file']  = $error['file'];
			$e['line']  = $error['line'];
			
			if ( php_sapi_name() != "cli" )
			{
				ob_end_clean();
			}

			ErrorHandle::record($e, 'error');
			self::halt($e);
		}
	}

	/**
	 * 异常、错误信息输出
	 *
	 * @access	public
	 * @param	array	$e	异常、错误信息
	 * @return	void
	 */
	public static function halt($e)
	{		
		//执行钩子函数
		if ( self::$hook && function_exists(self::$hook) )
		{
			$function = self::$hook;
			$function($e);
			exit();
		}
		ErrorHandle::output($e);
		exit();
	}
}
?>