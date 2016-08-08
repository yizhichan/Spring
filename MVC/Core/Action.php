<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 控制器(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class Action extends ModelActionBase
{
	/**
	 * 要执行令牌认证的操作
	 */
	public $token  = array();

	/**
	 * 缓存要执行的操作
	 */
	public $caches = array();

	/**
	 * 当前被执行的控制器
	 */
	public $mod    = 'index';

	/**
	 * 当前被执行的操作
	 */
	public $action = 'index';

	/**
	 * 保存$_GET、$_POST数据
	 */
	public $input  = array();


	/**
	 * 前置操作(框架自动调用)
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function before()
	{
	}

	/**
	 * 后置操作(框架自动调用)
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function after()
	{
	}

	/**
	 * 设置模板变量
	 *
	 * @access	protected
	 * @param	mixed	$key	键 
	 * @param	mixed	$value	值
	 * @return	void
	 */
	protected function set($key, $value)
	{
		$this->com('view')->set($key, $value);
	}

	/**
	 * 装载模板文件、解析变量并显示
	 *
	 * @access	protected
	 * @param	string	$file	文件名
	 * @return	void
	 */
	protected function display($file = null)
	{
		if ( !$file ) 
		{
			$mod    = strtolower($this->mod);
			$action = strtolower($this->action);
			$file   = "{$mod}/{$mod}.{$action}.html";
		}

		if ( in_array($this->action, $this->caches) && !$this->isPost() )
		{
			$data  = $this->com('view')->fetch($file);
			$cache = $this->getCacheObject();
			$key   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$cache->set(md5($key), $data, $this->expire, 0);

			ob_start('ob_gzip');
			print $data;
			ob_end_flush();
			return ;
		}

		$this->com('view')->display($file);
	}

	/**
	 * 装载模板文件、解析变量并显示(php变量js化)
	 *
	 * @access	protected
	 * @param	string	$file	文件名
	 * @return	void
	 */
	protected function output($file = null)
	{
		if ( $file )
		{
			$this->com('view')->output($file);
		}
		else 
		{
			$mod    = strtolower($this->mod);
			$action = strtolower($this->action);
			$file   = "{$mod}/{$mod}.{$action}.html";
			$this->com('view')->output($file);
		}
	}

	/**
	 * 装载模板文件、解析变量,返回解析后的html
	 *
	 * @access	protected
	 * @param	string	$file	文件名
	 * @return	string
	 */
	protected function fetch($file = null)
	{
		if ( $file )
		{
			return $this->com('view')->fetch($file);
		}
		else 
		{
			$mod    = strtolower($this->mod);
			$action = strtolower($this->action);
			$file   = "{$mod}/{$mod}.{$action}.html";
			return $this->com('view')->fetch($file);
		}
		
	}

	/**
	 * 呼叫控制器执行操作
	 *
	 * @access	public
	 * @param	string	$action	方法名
	 * @return	void
	 */
	public function call($action)
	{
		$this->mod     = strtolower( str_replace( 'Action', '', get_class($this) ) );
		$this->action  = strtolower( $action );
		Request::$data = $this->input;
		
		//通过钩子拦截操作
		$hook = $this->com('appHook');
		if ( get_class($hook) != 'stdClass' ) 
		{
			$hook->mod    = $this->mod;
			$hook->action = $this->action;
			$hook->work();
		}

		//执行前置操作
		$this->before();

		//执行操作(页面级缓存)
		if ( in_array($this->action, $this->caches) && !$this->isPost() )
		{
			$cache = $this->getCacheObject();
			$key   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$data  = $cache->get(md5($key), 0);
			if ( empty($data) )
			{
				$this->$action();
			}
			else
			{
				print $data;
			}
			return '';
		}

		//表单令牌验证
		if ( in_array($this->action, $this->token) )
		{
			$key = md5($this->mod.$this->action);
			!$this->isPost() && Session::set($key, $this->action);
			if ( $this->isPost() )
			{
				if ( Session::get($key) != $this->action )
				{
					throw new SpringException('非法请求!');
				}
				Session::remove($key);
			}
		}

		//执行操作
		$this->$action();
		
		//输出已执行的sql语句
		if ( $this->debug )
		{
			$log = $this->com('dbLog');
			if ( get_class($log) != 'stdClass' )
			{
				$log->output();
			}
		}

		//执行后置操作
		$this->after();
	}
	
	/**
	 * 获取表单数据
	 *
	 * @access	protected
	 * @param	string	$name	表单模型名
	 * @return	array			返回表单数据
	 */
	protected function getFormData($name = '')
	{
		empty($name) && $name = strtolower($this->mod).strtolower($this->action);
		$file = FormDir.'/'.$name.'.form.php';
		if ( !file_exists($file) )
		{
			throw new SpringException(" $file 不存在");
			return array();
		}
		
		require($file);
		$class = ucfirst($name).'Form';
		if ( !class_exists($class) )
		{
			throw new SpringException(" $file 中类 $class 不存在");
			return array();
		}

		$form        = new $class();
		$form->input = $this->input;
		
		return $form->parse();
	}

	/**
	 * 当前请求是否为POST
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected function isPost()
	{
		return Request::isPost();
	}

	/**
     * 是否AJAX请求
	 *
     * @access protected
     * @return bool
     */
    protected function isAjax()
	{
		return Request::isAjax();
    }

	/**
	 * 获取用户输入参数($_GET、$_POST)
	 *
	 * @access	public
	 * @param	string	$name		参数名
	 * @param	string	$type		参数类型
	 * @param	string	$default	设定默认值
	 * @param	int		$length		参数长度(0不切取)
	 * @return	mixed(int|float|string)
	 */
	public function input($name, $type = 'string', $default = '', $length = 0)
	{
		return Request::input($name, $type, $default, $length);
	}

	/**
	 * 数据分页
	 *
	 * @access	protected
	 * @param	int		$total		数据总条数
	 * @param	int		$pageRows	每页显示条数
	 * @param	int		$point		锚点数(数字分页时有效)
	 * @param	string	$style		被选中锚点的样式(数字分页时有效)
	 * @return	array
	 */
	protected function pager($total = 0, $pageRows = 20, $point = 10, $style = 'on')
	{
		if ( empty($total) ) 
		{
			return array();
		}

		$pager = $this->com('pager');
		if ( get_class($pager) != 'stdClass' )
		{
			$pager->input = $this->input;
			return $pager->get($total, $pageRows, $point, $style);
		}
		else
		{
			throw new SpringException("该组件接口已被移除!");
		}
	}
        
        /**
	 * 数据分页
	 *
	 * @access	protected
	 * @param	int		$total		数据总条数
	 * @param	int		$pageRows	每页显示条数
	 * @param	int		$point		锚点数(数字分页时有效)
	 * @param	string	$style		被选中锚点的样式(数字分页时有效)
	 * @return	array
	 */
	protected function pagerNew($total = 0, $pageRows = 20, $point = 10, $style = 'on')
	{
		if ( empty($total) ) 
		{
			return array();
		}

		$pager = $this->com('pagerNew');
                if ( $this->action == 'index' ){
                    $pager->prefix = '/'.$this->mod.'/'.$pager->prefix;
                }else{
                    $pager->prefix = '/'.$this->mod.'/'.$this->action.'/'.$pager->prefix;
                }
                
		if ( get_class($pager) != 'stdClass' )
		{
			$pager->input = $this->input;
			return $pager->get($total, $pageRows, $point, $style);
		}
		else
		{
			throw new SpringException("该组件接口已被移除!");
		}
	}
        
	/**
	 * 显示消息提示框(带提示信息+跳转)
	 *
	 * @access	protected
	 * @param	string	$desc		消息文本
	 * @param	string	$url		跳转地址
	 * @param	array	$scripts	被执行的js脚本
	 * @return	void
	 */
	protected function redirect($desc, $url, $scripts = array())
	{
		MessageBox::redirect($desc, $url, $scripts, 1);
	}
}
?>