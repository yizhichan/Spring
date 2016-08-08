<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 表单组件(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Form
{
	/**
	 * $_GET、$_POST数据
	 */
	public    $input    = array();

	/**
	 * 已收集的表单数据
	 */
	public    $data     = array();

	/**
	 * 错误输出格式[0消息框提示、1json格式提示]
	 */
	protected $format   = 0;

	/**
	 * 字段映射(表单字段与数据表字段建立关联)
	 */
	protected $map      = array();

	/**
	 * 回调方法
	 */
	protected $cbMethod = 'callback';

	/**
	 * 验证规则
	 */
	protected $rules = array(
		'require'  =>  '/.+/',
		'email'    =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
		'currency' =>  '/^\d+(\.\d+)?$/',
		'number'   =>  '/^\d+$/',
		'zip'      =>  '/^\d{6}$/',
		'int'	   =>  '/^[-\+]?\d+$/',
		'float'    =>  '/^[-\+]?\d+(\.\d+)$/',
		'english'  =>  '/^[A-Za-z]+$/',
		);


	/**
	 * 验证、解析表单数据(处理字段映射)
	 *
	 * @access	public
	 * @return	array
	 */
	public function parse()
	{
		if ( !is_array($this->map) )
		{
			throw new SpringException("映射规则错误!");
		}
		
		foreach ( $this->map as $key => $val )
		{
			if ( !is_array($val) || empty($val) || count($val) != 2 )
			{
				throw new SpringException("映射规则错误!");
			}

			$value = isset($this->input[$key]) ? $this->input[$key] : '';
			if ( !isset($val['field']) || empty($val['field']) )
			{
				throw new SpringException("映射的字段不能为空!");
			}

			$field = $val['field'];

			//设置回调方法进行验证处理
			if ( isset($val['method']) && $val['method'] ) 
			{
				$method = $val['method'];
				if ( !method_exists($this, $method) )
				{
					throw new SpringException("回调设置错误!");
				}
				
				$this->data[$field] = $this->$method($value);
				continue;
			}

			//通过匹配模式进行验证处理
			if ( isset($val['match']) && is_array($val['match']) && count($val['match']) == 3 )
			{
				$bool = $this->regex($value, $val['match'][0]);
				!$bool && $val['match'][2] && $this->stop($val['match'][2]);
				$this->data[$field] = $bool ? $value : $val['match'][1];
				continue;
			}
			throw new SpringException("映射规则错误!");
		}

		if ( method_exists($this, $this->cbMethod) )
		{
			$method = $this->cbMethod;
			$this->$method();
		}

		return $this->data;
	}
	
	/**
	 * 使用正则验证数据
	 *
	 * @access	protected
	 * @param	string	$value	待验证的数据
	 * @param	string	$rule	验证规则
	 * @return	bool
	 */
    protected function regex($value, $rule)
	{
		if ( $rule && $rule == 'ignore' ) 
		{
			return true;
		}

        // 检查是否有内置的正则表达式
        if ( isset($this->rules[strtolower($rule)]) )
		{
			return preg_match($this->rules[strtolower($rule)], $value) === 1;
		}

		return true;		
    }

	/**
	 * 中断并输出提示信息
	 *
	 * @access	public
	 * @param   mixed	$error 提示信息
	 * @return	void
	 */
	protected function stop($error = 'error')
	{
		if ( $this->format )
		{
			exit(json_encode($error));
		}
		else
		{
			MessageBox::halt($error);
		}
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
	protected function input($name, $type = 'string', $default = '', $length = 0)
	{
		return Request::input($name, $type, $default, $length);
	}
}
?>