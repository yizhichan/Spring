<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Session 组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Session
{
	/**
	 * 前缀
	 */
	public static $prefix = '';

	/**
	 * 路径
	 */
	public static $path   = '';

	/**
	 * 域名
	 */
	public static $domain = '';

	/**
	 * 数据
	 */
	public static $data   = array();


	/**
	 * 初始化
	 *
	 * @access	private
	 * @return	void
	 */
	private static function init()
	{
		if ( empty(Session::$domain) )
		{
			$arr             = explode('.', $_SERVER['HTTP_HOST']);
			$length          = count($arr);
			$domain          = '.'.$arr[$length-2].'.'.$arr[$length-1];
			$domain          = preg_replace("/:\d+/", '', $domain);
			Session::$domain = $domain;
		}

		if ( empty(Session::$path) )
		{
			Session::$path = '/';
		}

		if ( empty(Session::$prefix) )
		{
			Session::$prefix = 'qpZg_'.substr(md5(Session::$path.'|'.Session::$domain), 0, 4).'_';
		}
	}

	/**
	 * 写入
	 *
	 * @access	public
	 * @param	string   $key    键
	 * @param	string   $value  数据
	 * @param	int      $life   生命周期
	 * @param	int      $encode 是否加密(0否、1是)
	 * @return	void
	 */
	public static function set($key, $value = '', $life = 600, $encode = 1)
	{
		if ( empty($key) ) 
		{
			return ;
		}

		self::$data[$key] = $value;
		Session::init();

		$httponly  = false;
		$timestamp = time();
		$key       = Session::$prefix.$key;

		if ( $value == '' || $life < 0 )
		{
			$value = '';
			$life  = -1;
		}
	
		$life   = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);
		$path   = $httponly && PHP_VERSION < '5.2.0' 
			      ? Session::$path.'; HttpOnly' 
			      : Session::$path;
		$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;

		if ( $encode == 0 )
		{
			setcookie($key, $value, $life, $path, Session::$domain);
			return '';
		}
	
		$value = $value == '' ? $value : authCode($value, 'ENCODE');
	
		if ( PHP_VERSION < '5.2.0' )
		{
			setcookie($key, $value, $life, $path, Session::$domain, $secure);
		}
		else
		{
			setcookie($key, $value, $life, $path, Session::$domain, $secure, $httponly);
		}
	}

	/**
	 * 读取
	 *
	 * @access	public
	 * @param	string   $key    键
	 * @param	int      $encode 是否加密(0否、1是)
	 * @return	mixed
	 */
	public static function get($key, $encode = 1)
	{
		if ( isset(self::$data[$key]) )
		{
			return self::$data[$key];
		}

		Session::init();
		$key = Session::$prefix.$key;

		if ( isset($_COOKIE[$key]) && $_COOKIE[$key] )
		{		
			return $encode == 1 ? authCode($_COOKIE[$key], 'DECODE') : $_COOKIE[$key];
		}
		else
		{
			return '';
		}
	}

	/**
	 * 删除key对应的值
	 *
	 * @access	public
	 * @param	string   $key    键
	 * @return	void
	 */
	public static function remove($key)
	{
		Session::set($key);
	}

	/**
	 * 清除全部
	 *
	 * @access	public
	 * @return	void
	 */
	public static function clear()
	{
		Session::init();
		$keys = array();
		$pos  = strlen(Session::$prefix);
		foreach ( $_COOKIE as $key => $val )
		{
			$keys[] = substr($key, $pos, strlen($key));
		}
	
		foreach ( $keys as $key )
		{
			Session::set($key);
		}
	}
}

/**
 * 数据加密、解密
 *
 * @param  string   $string     加密、解密字符串
 * @param  string   $operation  加密、解密操作符(ENCODE加密、DECODE解密)
 * @param  string   $key        密钥
 * @param  string   $expiry     过期时间
 * @return string
 */
function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
	$authKey     = defined('AuthKey') ? AuthKey : md5('8905c0eZJYeXPgc2');
	$authKey    .= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$ckey_length = 4;
	$key         = md5($key != '' ? $key : $authKey);
	$keya        = md5(substr($key, 0, 16));
	$keyb        = md5(substr($key, 16, 16));
	$keyc        = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey    = $keya.md5($keya.$keyc);
	$key_length  = strlen($cryptkey);
	$string = $operation == 'DECODE' ? 
		base64_decode(substr($string, $ckey_length)) 
		: sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		}else{
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
?>