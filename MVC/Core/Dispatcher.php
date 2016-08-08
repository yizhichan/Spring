<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 调度器(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Dispatcher implements IDispatcher
{
	public  $rootPath    = '/';             //应用程序根路径
	public  $param       = array();         //输入参数(GET、POST)
	public  $inputer     = null;            //数据输入器
	public  $isRewrite   = true;            //是否url重写
	public  $configFile	 = null;			//自定义路由规则配置文件
	public  $module      = null;			//控制器对象
	public  $action      = 'index';			//控制器缺省方法
	public  $errorMod    = 'Error';         //发生404时的控制器
	public  $defaultMod  = 'Index';         //默认控制器
	public  $actionPath  = 'Action';        //控制器所在目录


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
		 $this->rootPath    = null;
		 $this->param       = null;
		 $this->inputer     = null;
		 $this->isRewrite   = null;
		 $this->configFile  = null;
		 $this->module      = null;
		 $this->action      = null;
		 $this->errorMod    = null;
		 $this->defaultMod  = null;
		 $this->actionPath  = null;
	}

	/**
	 * 解析请求
	 *
	 * @access	public
	 * @return	void
	 */
	public function parseReq($_mod_='')
	{
		$this->param = $this->inputer->getInput();

		if ( $this->isRewrite )
		{
			$fullUrl   = $_SERVER['REQUEST_URI'];
			$filename  = $_SERVER['SCRIPT_NAME'];
			$getArgs   = $_SERVER['QUERY_STRING'];
			$searchStr = array($filename,'?'.$getArgs,'index.php',$this->defaultMod.'.php');
			$url       = str_replace($searchStr,'',$fullUrl);
			$url       = explode('/',$url);

			$urltemp   = array();
			foreach ( $url as $k => $v ) 
			{
				if ( $v !=null ) 
				{
					$urltemp[] = $v;
				}
			}
			
			$key = $this->rootPath == '/' ? 0 : count(explode('/',$this->rootPath)) - 2;
			$mod = isset($urltemp[$key]) ? $urltemp[$key] : $this->defaultMod;
			if ( isset($urltemp[$key+1]) && strpos($urltemp[$key+1], '?') === false ) 
			{
				$this->action = $urltemp[$key+1];
			}

			//重新定义url规则
			$req          = $this->rewrite($mod, $this->action, $this->param);
			$key          = $req['action'].'/';
			unset($req['param']['mod']);
			unset($req['param']['action']);
			unset($req['param'][$key]);
			$mod          = $req['mod'];
			$this->action = $req['action'];	
			$this->param  = $req['param'];
		}
		else
		{
			$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : $this->defaultMod;
			if ( isset($_REQUEST['action']) ) 
			{
				$this->action = $_REQUEST['action'];
			}
		}
		//传值的绑定，处理引用control的代码
		if ( !empty($_mod_) ){
			$mod = $_mod_;
			$this->action = 'index';
		}
		
		$file = strtolower($mod).'.action.php';
		$mod  = ucfirst($mod).'Action';
		$file = $this->find($file);
		
		if ( empty($file) )
		{
			ErrorHandle::record("控制器 $mod 未找到!", '404');
			$this->notFound();
			return '';
		}
		
		require($file);
		if ( !class_exists($mod) ) 
		{
			ErrorHandle::record("控制器 $mod 未找到!", '404');
			$this->notFound();
		}
		else
		{
			$this->module = new $mod();
			if ( !method_exists($this->module, $this->action) )
			{
				ErrorHandle::record("未定义的操作: $this->action", '404');
				$this->notFound();
			}
		}
	}
	
	/**
	 * 处理404错误
	 *
	 * @access	private
	 * @return	void
	 */
	private function notFound()
	{
		$file = $this->actionPath.'/'.strtolower($this->errorMod).'.action.php';
		$mod  = $this->errorMod.'Action';
		if ( file_exists($file) )
		{
			require($file);
			$this->module = new $mod();
			$this->action = 'index';
		}
		else
		{
			require(LibDir.'/MVC/Util/error.action.php');
			$this->module = new ErrorAction();
			$this->action = 'index';
		}
	}

	/**
	 * 在控制器目录下查找文件(支持一级目录)
	 *
	 * @access	private
	 * @param	string	$name	文件名
	 * @return	string
	 */
	private function find($name)
	{
		if ( file_exists($this->actionPath.'/'.$name) )
		{
			return $this->actionPath.'/'.$name;
		}

		$files = scandir($this->actionPath);
		foreach ( $files as $file )
		{
			if ( is_dir("$this->actionPath/$file") && file_exists("$this->actionPath/$file/$name") )
			{
				return "$this->actionPath/$file/$name";
			}
		}
		return '';
	}

	/**
	 * 重新定义框架重写规则
	 *
	 * @access	private
	 * @param	string	$mod	控制器名
	 * @param	string	$action	请求的操作
	 * @param	array	$param	待构造的参数
	 * @return	array
	 */
	private function rewrite($mod, $action, $param)
	{
		if ( !file_exists($this->configFile) )
		{
			return array(
				'mod'    => $mod,
				'action' => $action,
				'param'  => $param,
				);
		}

		require($this->configFile);
		if ( !isset($rules) || empty($rules) )
		{
			return array(
				'mod'    => $mod,
				'action' => $action,
				'param'  => $param,
				);
		}

		$url = $_SERVER['REQUEST_URI'];
		foreach ( $rules as $rule )
		{
			$bool = false;
			if ( preg_match($rule[0], $url) ) 
			{
				$bool   = true;
				$mod    = isset($rule[1]['mod'])    ? $rule[1]['mod']    : $mod;
				$action = isset($rule[1]['action']) ? $rule[1]['action'] : $action;
			}
			$hasAgr = isset($rule[2]) && is_array($rule[2]) && !empty($rule[2]) ? true : false;
			if ( $bool && $hasAgr )
			{
				foreach ( $rule[2] as $key => $value )
				{
					if ( !(strpos($value, '#') === FALSE) )
					{
						preg_match("$value", $url, $result);
						$param[$key] = isset($result[1]) ? $result[1] : '';
					}
					else
					{
						$param[$key] = $value;
					}
				}
			}
		}

		return array(
			'mod'    => $mod,
			'action' => $action,
			'param'  => $param,
			);
	}

	/**
	 * 获取控制器对象
	 *
	 * @access	public
	 * @return	Action对象
	 */
	public function getModule()
	{
		$this->module->input = $this->param;

		return $this->module;
	}

	/**
	 * 获取请求的操作
	 *
	 * @access	public
	 * @return	string
	 */
	public function getAction()
	{
		return $this->action;
	}
}
?>