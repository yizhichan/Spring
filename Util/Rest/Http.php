<?
class Http
{
	/**
	 * 接口URL地址
	 */
	public static $url   = '';

	/**
	 * 接口标识
	 */
	public static $apiId = 2;

	/**
	 * 编码方式
	 */
	public static $mode  = 1;   

	/**
	 * 接口令牌
	 */
	public static $token = '';

	/**
	 * 格式化数据并输出
	 *
	 * @access	public
	 * @param	mixed	$data	数据
	 * @param	int		$encode	编码[0不编码、1编码]
	 * @return	void
	 */
	public static function output($data, $encode = 1)
	{
		if ( $encode == 1 ) {
			print empty($data) ? 'ok' : 'ok'.Http::encode($data);
		} else {
			print empty($data) ? 'ok' : 'ok'.$data;
		}
	}

	/**
	 * 编码
	 *
	 * @access	public
	 * @param	mixed	$data	数据
	 * @return	mixed
	 */
	public static function encode($data)
	{
		if ( self::$mode == 1 ) {
			$data = serialize($data);
		}

		if ( self::$mode == 2 ) {
			$data = json_encode($data);
		}

		return Encrypt::encode($data);
	}

	/**
	 * 解码
	 *
	 * @access	public
	 * @param	mixed	$data	数据
	 * @return	mixed
	 */
	public static function decode($data)
	{
		if ( empty($data) ) {
			return '';
		}
		$data = str_replace(" ","+", trim($data));
		$data = Encrypt::decode($data);

		if ( self::$mode == 1 ) {		
			return unserialize($data);
		}

		if ( self::$mode == 2 ) {
			return json_decode($data, true);
		}
	}

	/**
	 * 日志记录
	 *
	 * @access	public
	 * @param	string	$content	日志内容
	 * @param	string  $file		日志文件名
	 * @param	string  $dir		日志存放目录
     * @return	void
	 */
	public static function writeLog($content, $file = 'service.log', $dir = '')
	{
		if ( preg_match('/php/i', $file) ) {
			return ;
		}
		
		$logDir = $dir ? $dir : LogDir;
		if ( !file_exists($logDir) ) {
			@mkdir($logDir);
			@chmod($logDir, 0777);
		}
		
		if ( is_array($content) ) {
			$content = var_export($content, true);
		} else {
			$content = "【".date("Y-m-d H:i:s", time())."】\t\t".$content."\r\n";
		}
		file_put_contents($logDir.'/'.$file, $content, FILE_APPEND);
	}

	/**
	 * 模拟HTTP请求
	 *
	 * @param	string	$url		请求的地址
	 * @param	string	$method		0为GET、1为POST
	 * @param	string	$param		提交的参数
	 * @return	string
	 */
	public static function curl($url, $method = 0, $param = '')
	{
		$userAgent ="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
		$ch        = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		if ( $method == 1 ) {
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 25); 
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt( $ch,CURLOPT_HTTPHEADER, array(
			'Accept-Language: zh-cn',
			'Connection: Keep-Alive',
			'Cache-Control: no-cache',
			));
		$document = curl_exec($ch); 
		$info     = curl_getinfo($ch); 
		if ( $info['http_code'] == "405" ) {
			curl_close($ch);
			return 'error';
		}
		curl_close($ch);
		return $document;
	}

	/**
	 * 接口发送请求入口
	 *
	 * @access	public
	 * @param	string	$name		请求的接口
	 * @param	string  $param		提交的参数
     * @return	mixed
	 */
	public static function request($name, $param)
	{
		$url   = Http::$url.$name;
		$param = "apiId=".Http::$apiId."token=".Http::$token."&param=".Http::encode($param);
		$data  = Http::curl($url, 1, $param);
		
		if ( substr($data, 0, 2) == 'ok' ) {
			$data = ltrim($data, 'ok');
			return Http::decode($data);
		} else {
			Http::writeLog(strip_tags($data), 'rest.log');
			return '';
		}
	}
}
?>