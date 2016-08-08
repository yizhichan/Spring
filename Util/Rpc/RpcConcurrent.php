<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Rpc并发调用组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class RpcConcurrent
{
	/**
	 * 调用地址
	 */
	private static $url      = '';

	/**
	 * 调用方法
	 */
	private static $method   = '';

	/**
	 * 调用参数
	 */
	private static $param    = array();

	/**
	 * 回调方法
	 */
	private static $callback = array();

	/**
	 * 超时时间
	 */
	private static $timeout = array();


	/**
	 * 设置调用地址
	 *
	 * @access	public
	 * @param	string	$url	调用地址
     * @return	void
	 */
	public static function setUrl($url)
	{
		self::$url = $url;
	}

	/**
	 * 设置调用方法
	 *
	 * @access	public
	 * @param	string	$method		方法名
     * @return	void
	 */
	public static function setMethod($method)
	{
		self::$method = $method;
	}

	/**
	 * 设置调用参数
	 *
	 * @access	public
     * @param	array	$param	参数：array($var1, $var2, $var3,)
     * @return	void
	 */
	public static function setParam($param)
	{
		self::$param = $param;
	}

	/**
	 * 设置回调方法
	 *
	 * @access	public
	 * @param	array	$callback	回调：array(对象, "方法名")
     * @return	void
	 */
	public static function setCallback($callback)
	{
		self::$callback = $callback;
	}

	/**
	 * 设置超时时间
	 *
	 * @access	public
	 * @param	int		$seconds	超时秒数
     * @return	void
	 */
	public static function setTimeout($seconds = 10)
	{
		self::$timeout = array(
			YAR_OPT_TIMEOUT => $seconds * 1000,
			);
	}

	/**
	 * 注册一个并行的服务调用
	 *
	 * @access	public
     * @return	void
	 */
	public static function call()
	{
		$timeout = self::$timeout ? self::$timeout : array(YAR_OPT_TIMEOUT => 10000);
		Yar_Concurrent_Client::call(self::$url, self::$method, array(self::$param), self::$callback, NULL, $timeout);
	}

	/**
	 * 发送并行调用
	 *
	 * @access	public
     * @return	void
	 */
	public static function loop()
	{
		Yar_Concurrent_Client::loop();
	}
}
?>