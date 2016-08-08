<?
/**
 * 业务代理工厂
 *
 * 构造业务对象代理
 *
 * @package	Util
 * @author	void
 * @since	2014-09-28
 */
class ServiceProxy
{
	/**
	 * 构造业务对象代理
	 *
	 * @access	public
	 * @param	string	$name	业务代理类名
	 * @param	array	$config	接口配置
	 * @return	object  返回业务对象
	 */
	public static function create($name, $config)
	{
		static $objList = array();
		if ( isset($objList[$name]) && $objList[$name] ) {
			return $objList[$name];
		}
		$file = BiDir.'/'.strtolower($name).'.bi.php';
		require($file);
		$className      = $name.'Bi';
		$objList[$name] = new $className();
		Http::$apiId    = $objList[$name]->apiId;
		Http::$url      = $config[$objList[$name]->apiId]['url'];
		Http::$token    = $config[$objList[$name]->apiId]['token'];

		return $objList[$name];
	}
}
?>