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
class Router implements IDispatcher
{
	public $input      = array();		//输入参数
	public $module     = null;			//控制器对象
	public $action     = 'index';		//控制器缺省方法
	public $defaultMod = 'Index';		//默认控制器
	public $actionPath = 'Action';		//控制器所在目录
	
	
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
		 $this->input      = null;
		 $this->module     = null;
		 $this->action     = null;
		 $this->defaultMod = null;
		 $this->actionPath = null;
	}

	/**
	 * 解析请求
	 *
	 * @access	public
	 * @return	void
	 */
	public function parseReq($_mod_='')
	{
		//传值的绑定，处理引用control的代码
		if ( empty($_mod_) ){			
			$uri   = str_replace('/?', '/', ltrim(Uri, "/"));
			$uri   = explode('/', $uri);
			$count = count($uri);
			if ( $count < 2 )
			{
				throw new SpringException("命令行参数错误!");
			}
			
			$mod          = $uri[0];
			$this->action = $uri[1];
		}else{
			$mod = $_mod_;
			$this->action = 'index';
		}
		
		if ( !(strpos(Uri, '/?') === FALSE) ) {
			$params = explode('&', $uri[2]);
			foreach ( $params as $param ) {
				$item                  = explode('=', $param);
				$this->input[$item[0]] = isset($item[1]) ? $item[1] : '';
			}
		}
		
		$file = strtolower($mod).'.action.php';
		$mod  = ucfirst($mod).'Action';
		$file = $this->find($file);
		
		if ( empty($file) )
		{
			throw new SpringException("控制器 $mod 未找到!");
		}
		
		require($file);
		if ( !class_exists($mod) ) 
		{
			throw new SpringException("控制器 $mod 未找到!");
		}
		
		$this->module = new $mod();
		if ( !method_exists($this->module, $this->action) )
		{
			throw new SpringException("未定义的操作: $this->action");
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
	 * 获取控制器对象
	 *
	 * @access	public
	 * @return	Action对象
	 */
	public function getModule()
	{
		$this->module->input = $this->input;

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