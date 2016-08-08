<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 控制器、模型、实体层等基类(框架核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class Object
{
	/**
	 * 缓存组件标识id
	 */
	protected $cacheId = 'mem';

	/**
	 * 过期时间(5分钟)
	 */
	protected $expire  = 300;


	/**
	 * 获取组件对象
	 *
	 * @access	protected
	 * @param	string	$objId	对象标识 
	 * @return	object
	 */
	protected function com($objId = null)
	{
		return Spring::getComponent($objId);
	}

	/**
	 * 创建缓存对象
	 *
	 * @access	public
	 * @return	object
	 */
	protected function getCacheObject()
	{
		$cache     = Spring::getComponent($this->cacheId);
		$className = get_class($cache);
		if ( $className == 'stdClass' ) 
		{
			throw new SpringException("该组件接口已被移除!");
		}

		$interface = class_implements($className);
		if ( in_array('ICache', $interface) )
		{
			$cache->expire = $this->expire;
			return $cache;
		}
		else
		{
			throw new SpringException("类 $className 未实现 ICache 接口!");
		}
	}
}
?>