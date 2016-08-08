<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Sphinx全文检索组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SphinxSearch
{
	/**
	 * sphinx 客户端对象
	 */
	public  $sphinxClient = null;
	
	/**
	 * 默认返回的数据条数
	 */
	private $limit        = 1;

	/**
	 * 匹配模式
	 */
	private $matches     = array(0, 1, 2, 3, 4, 5, 6);

	/**
	 * 查询串
	 */
	private $query       = '';

	/**
	 * 数据查询
	 *
	 * @access	public
	 * @param	string	$index	索引
	 * @param	array	$rule	查询规则
	 * @return	array
	 */
	public function find($index, $rule)
	{
		$rule['isPage'] = 0;

		return $this->search($index, $rule);
	}

	/**
	 * 数据查询（分页）
	 *
	 * @access	public
	 * @param	string	$index	索引
	 * @param	array	$rule	查询规则
	 * @return	array
	 */
	public function findAll($index, $rule)
	{
		$rule['isPage'] = 1;

		return $this->search($index, $rule);
	}

	/**
	 * 数据查询
	 *
	 * @access	public
	 * @param	string	$index	索引
	 * @param	array	$rule	查询规则($rule['way']为1普通查询、2近似查询, $rule['match']匹配模式)
	 * @return	array
	 */
	private function search($index, $rule)
	{
		$this->parse($rule);
		if ( is_array($this->query) )
		{
			foreach( $this->query as $v )
			{
				if ( is_array($v) )
				{
					$this->sphinxClient->AddQuery($v[0].' '.$v[1], $index);
				}
				else
				{
					$this->sphinxClient->AddQuery($v, $index);
				}
			}
			$result = $this->sphinxClient->RunQueries();
			$data   = $this->buildResult($result, 2, $rule['isPage']);
		}
		else
		{
			$result = $this->sphinxClient->Query('', $index);
			$data   = $this->buildResult($result, 1, $rule['isPage']);
		}

		$this->sphinxClient->ResetFilters();
		
		return $data;
	}

	/**
	 * 解析查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parse($rule)
	{
		$this->parseCol($rule);
		$this->parseEq($rule);
		$this->parseIn($rule);
		$this->parseScope($rule);
		$this->parseLike($rule);
		$this->parseLLike($rule);
		$this->parseRLike($rule);
		$this->parseRaw($rule);
		
		$match = isset($rule['match']) && in_array($rule['match'], $this->matches) 
			     ? $rule['match']
			     : 6;
		$this->sphinxClient->SetMatchMode($match);

		$this->parseOrderBy($rule);
		$this->parseGroupBy($rule);
		$this->parseLimit($rule);
	}

	/**
	 * 解析取列查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseCol($rule)
	{
		if ( isset($rule['col']) && is_array($rule['col']) && !empty($rule['col']) )
		{
			$col = in_array('id', $rule['col']) 
				   ? implode(',', $rule['col'])
				   : 'id,'.implode(',', $rule['col']);
			$this->sphinxClient->SetSelect($col);
		}
	}

	/**
	 * 解析等号查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseEq($rule)
	{
		if ( isset($rule['eq']) && is_array($rule['eq']) && !empty($rule['eq']) )
		{
			foreach ( $rule['eq'] as $key => $value )
			{
				if ( $value ) 
				{
					$this->sphinxClient->SetFilter($key, array($value) );
				}
			}
		}
	}

	/**
	 * 解析in查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseIn($rule)
	{
		if ( isset($rule['in']) && is_array($rule['in']) && !empty($rule['in']) )
		{
			foreach ( $rule['in'] as $key => $value )
			{
				if ( is_array($value) && !empty($value) )
				{
					$this->sphinxClient->SetFilter($key, $value);
				}
			}
		}
	}

	/**
	 * 解析区间查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseScope($rule)
	{
		if ( isset($rule['scope']) && is_array($rule['scope']) && !empty($rule['scope']) )
		{
			foreach ( $rule['scope'] as $key => $value )
			{
				if ( is_array($value) && count($value) == 2 && $value[0] < $value[1] )
				{
					if ( is_int($value[0]) && is_int($value[1]) )
					{
						$this->sphinxClient->SetFilterRange($key, intval($value[0]), intval($value[1]) );
					} 
					else
					{
						$this->sphinxClient->SetFilterFloatRange($key, floatval($value[0]), floatval($value[1]) );
					}
				}
			}
		}
	}

	/**
	 * 解析模糊查询左匹配规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseLLike($rule)
	{
		if ( isset($rule['lLike']) && is_array($rule['lLike']) )
		{
			foreach ( $rule['lLike'] as $key => $val ) 
			{
				$this->query[] = array("@$key", '^'.$val);
			}
		}		
	}

	/**
	 * 解析模糊查询任意位置匹配规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseLike($rule)
	{
		if ( isset($rule['like']) && is_array($rule['like']) )
		{
			foreach ( $rule['like'] as $key => $val )
			{
				if ( !$val ) 
				{
					continue;
				}

				$words = $this->stringToArray($val);
				$count = count($words);
				if ( $count == 1 )
				{
					$this->query[] = array("@$key", '^'.$val.'$');
				}

				if ( $count == 2 )
				{
					$this->query[] = array("@$key", '^'.$words[0].$words[1].'$');
					$this->query[] = array("@$key", $words[0].$words[1]);
					$this->query[] = array("@$key", ''.$words[0]. '<<' .$words[1].' '.'"'.$words[0].' '.$words[1].'"~1');
				}
				else
				{
					$num           = ceil(count($words)/2);
					$wordStr       = implode(' ', $words);
					$this->query[] = array("@$key", '"'.$val.'"');
					$this->query[] = array("@$key", '"'.$wordStr.'"/'.$num);
				}
			}
		}		
	}

	/**
	 * 解析模糊查询左匹配规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseRLike($rule)
	{
		if ( isset($rule['rLike']) && is_array($rule['rLike']) )
		{
			foreach ( $rule['rLike'] as $key => $val )
			{
				$this->query[] = array("@$key", $val.'$');
			}
		}	
	}

	/**
	 * 解析模糊查询左匹配规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseRaw($rule)
	{
		if ( isset($rule['raw']) && $rule['raw'] )
		{
			$this->query[] = $rule['raw'];
		}	
	}

	/**
	 * 解析排序规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseOrderBy($rule)
	{
		$order   = isset($rule['order']) && is_array($rule['order']) 
				   ? $rule['order'] 
			       : array();
		$orderBy = array();

		foreach ( $order as $key => $value )
		{
			if ( is_numeric($value) )
			{
				$this->sphinxClient->SetSortMode($value, $key);
				break;
			} 
			else
			{
				$orderBy[] = isset($rule['pk']) && $rule['pk'] == $key 
				             ? "@id $value" 
				             : "$key $value";
			}
		}

		if ( !empty($orderBy) )
		{
			$this->sphinxClient->SetSortMode(SPH_SORT_EXTENDED, implode(',', $orderBy) );
		}
	}

	/**
	 * 解析分组规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseGroupBy($rule)
	{
		$group = isset($rule['group']) && is_array($rule['group']) 
			     ? $rule['group']
			     : array();

		foreach($group as $key => $value)
		{
			$this->sphinxClient->SetGroupBy($key, SPH_GROUPBY_ATTR, "@group $value" );
		}
	}

	/**
	 * 解析取n条和按位置获取规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseLimit($rule)
	{
		$limit  = isset($rule['limit']) ? intval($rule['limit']) : $this->limit;
		$limit  = $limit > 0 ? $limit : $this->limit;
		$isPage = isset($rule['isPage']) ? $rule['isPage'] : 0;
		
		if ( $isPage )
		{
			$page   = isset($rule['page']) ? intval($rule['page']) : 1;
			$page   = $page > 10000 ? 1 : $page;
			$offset = ($page-1)* $limit;
			$offset = ($offset < 0) ? 0 : $offset;
			$this->sphinxClient->SetLimits($offset, $limit);
		}
		else
		{
			$this->sphinxClient->SetLimits(0, $limit);
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

		while ( $strlen )
		{
			$words[] = mb_substr($keyword, 0, 1, "utf8");
			$keyword = mb_substr($keyword, 1, $strlen, "utf8");
			$strlen  = mb_strlen($keyword);
		}
		
		return $words;
	}

	/**
	 * 处理sphinx返回结果
	 *
	 * @access	private
	 * @param	array	$result	sphinx返回结果
	 * @param	int		$way	查询方式[1普通查询、2近似查询]
	 * @return	array
	 */
	private function buildResult($result, $way = 1, $isPage = 1)
	{
		$rows = $matches = array();

		//普通查询结果集
		if ( $way == 1 )
		{
			if ( !isset($result['matches']) )
			{
				return $isPage ? array('rows'  => $rows, 'total' => 0) : array();
			}

			$matches = $result['matches'];
			foreach ( $matches as $match )
			{
				$row       = array();
				$row['id'] = $match['id']; 
				foreach ( $match['attrs'] as $key => $value )
				{
					$row[$key] = $value;
				}
				$rows[] = $row;
			}
			
			if ( $isPage )
			{
				return array(
					'rows'  => $rows,
					'total' => isset($result['total_found']) ? $result['total_found'] : 0,
					);
			}

			return $rows;
		}

		//近似查询结果集
		if ( $way == 2 )
		{
			foreach ( $result as $v )
			{
				isset($v['matches']) && $matches = $v['matches'];
				if ( !empty($matches) )
				{
					foreach ( $matches as $match )
					{
						$row       = array();
						$row['id'] = $match['id']; 
						foreach ( $match['attrs'] as $key => $value )
						{
							$row[$key] = $value;
						}
						$rows[] = $row;
					}

					if ( $isPage )
					{
						return array(
							'rows'  => $rows,
							'total' => isset($v['total_found']) ? $v['total_found'] : 0,
							);
					}
					
					return $rows;			
				}
			}
		}
		
		return $isPage ? array('rows'  => $rows, 'total' => 0) : array();
	}

}
?>