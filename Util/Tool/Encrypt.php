<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  数据加密、解密
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Encrypt
{
	/**
	 * 数据加密
	 *
	 * @access	public
	 * @param	string   $string	加密字符串
	 * @return	string
	 */
	public static function encode($string)
	{
		return self::authCode($string, 'ENCODE');
	}

	/**
	 * 数据解密
	 *
	 * @access	public
	 * @param	string   $string	解密字符串
	 * @return	string
	 */
	public static function decode($string)
	{
		return self::authCode($string, 'DECODE');
	}

	/**
	 * 数据加密、解密
	 *
	 * @access	public
	 * @param	string   $string     加密、解密字符串
	 * @param	string   $operation  加密、解密操作符(ENCODE加密、DECODE解密)
	 * @param	string   $key        密钥
	 * @param	string   $expiry     过期时间
	 * @return	string
	 */
	private static function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
	{
		$authKey     = defined('AuthKey') ? AuthKey : md5('8905c0eZJYeXPgc2');
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
}
?>