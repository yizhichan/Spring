<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 二维数组分页
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DataPage
{
	/**
	 * 二维数组分页
	 *
	 * @param  array	$rule	规则
	 * @access public
	 * @return array
	 */
	public static function getPageRows($rule)
	{
		if ( !isset($rule['list']) || !is_array($rule['list']) || empty($rule['list']) ) 
		{
			return array(
				'rows'  => array(),
				'total' => 0,
			);
		}

		$limit  = isset($rule['limit']) ? intval($rule['limit']) : 20;
		$limit  = $limit <= 0 ? 20 : $limit; 
		$page   = isset($rule['page']) ? intval($rule['page']) : 1;
		$page   = $page <= 0 ? 1 : $page;
		$page   = $page > 1000 ? 1 : $page;
		$offset = ($page-1)* $limit;
		$offset = ($offset < 0) ? 0 : $offset;

		return array(
			'rows'  => array_slice($rule['list'], $offset, $limit),
			'total' => count($rule['list']),
			);
	}
}
?>