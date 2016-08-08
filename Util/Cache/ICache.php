<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据缓存接口
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
interface ICache
{
	/**
	 * 写入数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	mixed   $value		值
	 * @param	int		$expire		缓存时间(0持久存储)
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	bool
	 */
	public function set($key, $value, $expire = 0, $encoding = 1);

	/**
	 * 获取数据
	 *
	 * @access	public
	 * @param	mixed	$key		键 
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	mixed
	 */
	public function get($key, $encoding = 1);

	/**
	 * 删除数据
	 *
	 * @access	public
	 * @param	mixed	$key		键
	 * @param	int		$encoding	编码方式(0-3)
	 * @return	mixed
	 */
	public function remove($key, $encoding = 1);
	
	/**
	 * 清空数据
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function clear();

}
?>