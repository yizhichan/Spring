<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据过滤组件(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataFilter
{
	/**
	 * 数据过滤
	 *
	 * @access	public
	 * @param	array	$input
	 * @return	array
	 */
	public function filter($input)
	{
		$data  = array();
		
		if ( is_array($input) )
		{
			foreach ( $input as $k => $v )
			{
				if ( is_array($input[$k]) )
				{
					foreach ( $input[$k] as $k2 => $v2 )
					{
						$data[$k][$this->cleanKey($k2)] = $this->cleanValue($v2);
					}
				}
				else
				{
					$data[$this->cleanKey($k)] = $this->cleanValue($v);
				}
			}
		}
		
		return $data;	
	}
	
	/**
	 * 过滤数组键
	 *
	 * @access	private
	 * @param	string	$val
	 * @return	string
	 */
	private function cleanKey($key)
	{
		if ( $key == "" ) 
		{
			return 0;
		}

		$key = preg_replace( "/\.\./"           , ""  , $key );
		$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
		$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );
		
		return $key;
	}
	
	/**
	 * 过滤数组值
	 *
	 * @access	private
	 * @param	string	$val
	 * @return	string
	 */
	private function cleanValue($val)
	{
		if ( is_array($val) || $val == "") 
		{
			return "";
		}
		
		$val = str_replace( "&#032;", " ", $val );
		$val = str_replace( "&"            , "&amp;"         , $val );
		$val = str_replace( "<!--"         , "&#60;&#33;--"  , $val );
		$val = str_replace( "-->"          , "--&#62;"       , $val );
		$val = preg_replace( "/<script/i"  , "&#60;script"   , $val );
		$val = str_replace( ">"            , "&gt;"          , $val );
		$val = str_replace( "<"            , "&lt;"          , $val );
		$val = str_replace( "\""           , "&quot;"        , $val );
		$val = preg_replace( "/\n/"        , "<br>"          , $val );
		$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
		$val = preg_replace( "/\r/"        , ""              , $val ); 
		$val = str_replace( "!"            , "&#33;"         , $val );
		$val = str_replace( "'"            , "&#39;"         , $val ); 
		if ( get_magic_quotes_gpc() )
		{
			$val = stripslashes($val);
		}
		
		return preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $val );
	}
}
?>