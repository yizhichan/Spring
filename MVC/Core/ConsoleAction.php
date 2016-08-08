<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 控制器(Console使用)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class ConsoleAction extends ModelActionBase
{
	/**
	 * 当前被执行的控制器
	 */
	public $mod    = 'index';

	/**
	 * 当前被执行的操作
	 */
	public $action = 'index';

	/**
	 * 输入参数
	 */
	public $input  = array();


	/**
	 * 前置操作(框架自动调用)
	 *
	 * @access	public
	 * @return	void
	 */
	public function before()
	{
	}

	/**
	 * 后置操作(框架自动调用)
	 *
	 * @access	public
	 * @return	void
	 */
	public function after()
	{
	}

	/**
	 * 呼叫控制器执行操作
	 *
	 * @access	public
	 * @param	string	$action	方法名
	 * @return	void
	 */
	public final function call($action)
	{
		$this->mod    = strtolower( str_replace( 'Action', '', get_class($this) ) );
		$this->action = strtolower( $action );

		//执行前置操作
		$this->before();

		//执行操作
		$this->$action();
		
		//执行后置操作
		$this->after();
	}
	
	/**
	 * 获取用户输入参数
	 *
	 * @access	public
	 * @param	string	$name	参数名
	 * @param	string	$type	参数类型
	 * @param	int		$length	参数长度(0不切取)
	 * @return	mixed(int|float|string)
	 */
	public final function input($name, $type = 'string', $length = 0)
	{
		$types = array('int', 'float', 'text', 'string');
		if ( !in_array($type, $types) )
		{
			return '';
		}

		$value = isset($this->input[$name]) ? $this->input[$name] : '';
		
		if ( empty($value) )
		{
			return in_array($type, array('text', 'string')) ? '' : 0;
		}

		if ( $type == 'int' )
		{
			return intval($value);
		}

		if ( $type == 'float' )
		{
			return $length == 0 
				? sprintf("%.2f", floatval($value)) 
				: sprintf("%.{$length}f", floatval($value));
		}
		
		$length = $length ? intval($length) : 0;

		return $length ? substr($value, 0, $length) : $value; 			
	}
}
?>