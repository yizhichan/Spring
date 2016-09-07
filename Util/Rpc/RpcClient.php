<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Rpc客户端
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class RpcClient
{
	/**
	 * rpc服务端地址,如:http://api.chofn.net/
	 */
	public $url     = '';

	/**
	 * 超时（默认5秒）
	 */
	public $timeout = 5000;

	/**
	 * 发送请求
	 *
	 * @access	public
	 * @param	string	$name	请求的接口
	 * @param	string  $param	提交的参数
     * @return	array
	 */
	public function request($name, $param)
	{
		$uri = explode("/", trim($name, "/"));
		if ( count($uri) < 2 || count($uri) > 2 )
		{
			return array();
		}

		$module = $uri[0];
		$method = $uri[1];
		try
		{
			$client = new Yar_client("$this->url$module");
			$client->setOpt(YAR_OPT_CONNECT_TIMEOUT, $this->timeout);

			return $client->$method($param);
		}
		catch ( Yar_Client_Exception $e )
		{
			ErrorHandle::record($e->getMessage(), 'error');
			return array();
		}
	}
}
?>