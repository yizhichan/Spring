<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Http Request组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Request
{
	/**
	 * get、post数据
	 */
	public static $data = array();


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
	public static function input($name, $type = 'string', $default = '', $length = 0)
	{
		$types = array('int', 'float', 'text', 'string', 'array');
		if ( !in_array($type, $types) )
		{
			return '';
		}

		$value = isset(self::$data[$name]) ? self::$data[$name] : 'empty';
		
		if ( $value == 'empty' )
		{
			if ( $type == 'array' )
			{
				return array();
			}

			if ( $default )
			{
				return $default; 
			}
			
			return in_array($type, array('text', 'string')) ? '' : 0;
		}

		if ( $type == 'int' )
		{
			return is_numeric ( $value ) ? intval ( $value ) : (is_numeric ( $default ) ? $default : 0);
		}

		if ( $type == 'float' )
		{
			return $length == 0 
				? sprintf("%.2f", floatval($value)) 
				: sprintf("%.{$length}f", floatval($value));
		}

		if ( $type == 'text' )
		{
			return isset($_POST[$name]) ? $_POST[$name] : $_GET[$name];
		}

		$length = $length ? intval($length) : 0;

		return $length ? substr($value, 0, $length) : $value; 			
	}

	/**
     * 是否AJAX请求
	 *
     * @access protected
     * @return bool
     */
    public static function isAjax()
	{
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) )
		{
			return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		}

		return false;
    }

	/**
	 * 当前请求是否为POST
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;
	}
}
?>