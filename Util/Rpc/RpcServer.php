<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Rpc服务端
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class RpcServer extends ModelActionBase
{
	/**
	 * 输入参数
	 */
	public $input = array();


	/**
	 * 启动Rpc Server
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$server = new Yar_Server($this);
        $server->handle();
	}

	/**
	 * 有不存在的操作的时候执行
	 *
	 * @access	public
	 * @param	string	$method	方法名
     * @param	array	$args	参数
     * @return	mixed
	 */
	public function __call($method, $args)
	{
	}

	/**
	 * 获取输入参数
	 *
	 * @access	protected
	 * @param	string	$name	参数名
	 * @param	string	$type	参数类型
	 * @param	int		$length	参数长度(0不切取)
	 * @return	mixed(int|float|string|array)
	 */
	protected function getParam($name, $type = 'string', $length = 0)
	{
		$types = array('int', 'float', 'array', 'string');
		if ( !in_array($type, $types) )
		{
			return '';
		}

		$value = isset($this->input[$name]) ? $this->input[$name] : '';
		
		if ( empty($value) )
		{
			if ( $type == 'string' )
			{
				return '';
			}

			if ( $type == 'array' )
			{
				return array();
			}
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

		if ( $type == 'array' )
		{
			return $value;
		}

		$length = $length ? intval($length) : 0;
		return $length ? substr($value, 0, $length) : $value; 			
	}
}
?>