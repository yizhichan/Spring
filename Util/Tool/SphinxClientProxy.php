<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 全文索引组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SphinxClientProxy
{
	/**
	 * sphinx 客户端对象
	 */
	public $sphinxClient = null;
	
	/**
	 * 默认返回的数据条数
	 */
	public $limit        = 1;

	/**
	 * 匹配模式
	 */
	private $matches     = array(0, 1, 2, 3, 4, 5, 6);

	/**
	 * 数据查询
	 *
	 * @access	public
	 * @param	string	$index	索引
	 * @param	array	$rule	查询规则($rule['way']为1普通查询、2近似查询, $rule['match']匹配模式)
	 * @return	array
	 */
	public function find($index, $rule)
	{
		$seek = $this->parse($rule);
		if ( empty($seek) ) {
			return array();
		}

		if ( is_array($seek) ) {
			foreach( $seek as $k => $v ) {
				$this->sphinxClient->AddQuery($v[0].' '.$v[1], $index);
			}
			$result = $this->sphinxClient->RunQueries();
			$data   = $this->getResult($result, 2);
		} else {
			$result = $this->sphinxClient->Query($seek, $index);
			$data   = $this->getResult($result, 1);
		}

		$this->sphinxClient->ResetFilters();
		
		return $data;
	}

	/**
	 * 解析查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	string
	 */
	private function parse($rule)
	{
		if ( isset($rule['col']) && is_array($rule['col']) && !empty($rule['col']) ) {
			$this->sphinxClient->SetSelect(implode(',', $rule['col']));
		}

		//处理"="查询
		if ( isset($rule['eq']) && is_array($rule['eq']) && !empty($rule['eq']) ) {
			foreach ( $rule['eq'] as $key => $value ) {
				if ( trim($value) == '') continue;
				$this->sphinxClient->SetFilter($key, array($value) );
			}
		}

		//处理"in"查询
		if ( isset($rule['in']) && is_array($rule['in']) && !empty($rule['in']) ) {
			foreach ( $rule['in'] as $key => $value ) {
				if ( is_array($value) && !empty($value) ) {
					$this->sphinxClient->SetFilter($key, $value);
				}
			}
		}

		//处理范围查询
		if ( isset($rule['scope']) && is_array($rule['scope']) && !empty($rule['scope']) ) {
			foreach ( $rule['scope'] as $key => $value ) {
				if ( is_array($value) && count($value) == 2 && $value[0] < $value[1] ) {
					if ( is_int($value[0]) && is_int($value[1]) ) {
						$this->sphinxClient->SetFilterRange($key, intval($value[0]), intval($value[1]) );
					} else {
						$this->sphinxClient->SetFilterFloatRange($key, floatval($value[0]), floatval($value[1]) );
					}
				}
			}
		}

		//处理关键字查询
		$seek  = $this->makeSeek($rule);
		
		$match = isset($rule['match']) && in_array($rule['match'], $this->matches) 
			     ? $rule['match']
			     : 6;
		$this->sphinxClient->SetMatchMode($match);

		//处理排序
		$order   = isset($rule['order']) && is_array($rule['order']) 
				   ? $rule['order'] 
			       : array();
		$orderBy = array();

		foreach ( $order as $key => $value ) {
			if ( is_numeric($value) ) {
				$this->sphinxClient->SetSortMode($value, $key);
				break;
			} else {
				$orderBy[] = isset($rule['pk']) && $rule['pk'] == $key 
				             ? "@id $value" 
				             : "$key $value";
			}
		}

		if ( !empty($orderBy) ) {
			$this->sphinxClient->SetSortMode(SPH_SORT_EXTENDED, implode(',', $orderBy) );
		}

		//处理分组
		$group = isset($rule['group']) && is_array($rule['group']) 
			     ? $rule['group'] 
			     : array();

		foreach($group as $key => $value) {
			$this->sphinxClient->SetGroupBy($key, SPH_GROUPBY_ATTR, "@group $value" );
		}

		//处理数据返回条数
		$limit  = isset($rule['limit']) ? intval($rule['limit']) : $this->limit;
		$limit  = $limit > 0 ? $limit : $this->limit;
		$isPage = isset($rule['isPage']) ? $rule['isPage'] : 0;
		$page   = isset($rule['page']) ? intval($rule['page']) : 1;
		$offset = $isPage == 1 ? $this->getOffset($limit, $page) : 0;
		$this->sphinxClient->SetLimits($offset, $limit);

		return $seek;
	}

	/**
	 * 构造查询串
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	mixed   string|array
	 */
	private function makeSeek($rule)
	{
		$way  = isset($rule['way']) ? intval($rule['way']) : 1;
		$way  = in_array($way, array(1, 2)) ? $way : 1;
		$seek = '';
		
		if ( isset($rule['like']) && is_array($rule['like']) && $way == 1 ) {
			foreach ( $rule['like'] as $key => $val ) {
				$keys[]   = $key;
				if ( is_array($val) ) {
					foreach ( $val as $v ) {
						trim($v) && $values[] = $v;
					}
				} else {
					trim($val) && $values[] = $val;
				}
			}

			if ( !empty($keys) && !empty($values) ) {
				$seek = "@(".implode(",", $keys).") (".implode(" | ", $values).")";
			}

			return $seek;
		}

		if ( isset($rule['like']) && is_array($rule['like']) && $way == 2 ) {
			foreach ( $rule['like'] as $key => $val ) {
				$words = $this->stringToArray($val);
				$count = count($words);
				if ( $count == 1 ) {
					return "@$key ^$val$";
				}
				
				if ( $count == 2 ) {
					$seek[0] = array("@$key", '^'.$words[0].$words[1].'$');
					$seek[1] = array("@$key", '^'.$words[1].$words[0].'$');
					$seek[2] = array("@$key", $words[0].$words[1]);
					$seek[3] = array("@$key", ''.$words[0]. '<<' .$words[1].' '.'"'.$words[0].' '.$words[1].'"~1');

					return $seek;
				}
				
				$num     = ceil(count($words)/2);
				$wordStr = implode(' ', $words);
				$seek[0] = array("@$key", '"'.$val.'"');
				$seek[1] = array("@$key", '"'.$wordStr.'"/'.$num);
				
				return $seek;
			}

			return $seek;
		}
	}
	
	/**
	 * 把含有中文的字符串转换为数组
	 *
	 * @access	private
	 * @param	string	$keyword	关键字
	 * @return	array
	 */
	private function stringToArray($keyword)
	{
		$keyword = trim($keyword);
		$strlen  = mb_strlen($keyword);
		$words   = array();

		while ( $strlen ) {
			$words[] = mb_substr($keyword, 0, 1, "utf8");
			$keyword = mb_substr($keyword, 1, $strlen, "utf8");
			$strlen  = mb_strlen($keyword);
		}
		
		return $words;
	}
	
	/**
	 * 获取分页偏移量
	 *
	 * @access	private
	 * @param	string	$pageRows	每页数据条数
	 * @param	int		$page		页码
	 * @return	int
	 */
	private function getOffset($pageRows = 15, $page = 0)
	{
		$page   = $page > 10000 ? 1 : $page;
		$offset = ($page-1)* $pageRows;
		$offset = ($offset < 0) ? 0 : $offset;

		return $offset;
	}

	/**
	 * 处理sphinx返回结果
	 *
	 * @access	private
	 * @param	array	$result	sphinx返回结果
	 * @param	int		$way	查询方式[1普通查询、2近似查询]
	 * @return	array
	 */
	private function getResult($result, $way = 1)
	{
		$rows = $matches = array();

		//普通查询结果集
		if ( $way == 1 ) {
			if ( !isset($result['matches']) )  {
				return array(
					'rows'  => $rows,
					'total' => 0,
					);
			}

			$matches = $result['matches'];
			foreach ( $matches as $match ) {
				$row       = array();
				$row['id'] = $match['id']; 
				foreach ( $match['attrs'] as $key => $value ) {
					$row[$key] = $value;
				}
				$rows[] = $row;
			}			
			return array(
				'rows'  => $rows,
				'total' => isset($result['total_found']) ? $result['total_found'] : 0,
				);

		}

		//近似查询结果集
		if ( $way == 2 ) {
			foreach ( $result as $v ) {
				isset($v['matches']) && $matches = $v['matches'];
				if ( !empty($matches) ) {
					foreach ( $matches as $match ) {
						$row       = array();
						$row['id'] = $match['id']; 
						foreach ( $match['attrs'] as $key => $value ) {
							$row[$key] = $value;
						}
						$rows[] = $row;
					}
					return array(
						'rows'  => $rows,
						'total' => isset($v['total_found']) ? $v['total_found'] : 0,
						);
				}
			}
		}
		
		return array(
			'rows'  => $rows,
			'total' => 0,
			);
	}

}
?>