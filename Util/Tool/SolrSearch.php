<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 Solr全文检索组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class SolrSearch
{
	/**
	 * 是否开启调试
	 */
	public  $debug       = false;

	/**
	 * 连接Solr服务器配置文件
	 */
	public  $configFile  = null;
	
	/**
	 * solr连接对象集合
	 */
	private $connections = array();

	/**
	 * Solr客户端对象
	 */
	private $solrClient  = null;

	/**
	 * Solr查询对象
	 */
	private $solrQuery   = null;

	/**
	 * 当前操作的文档集合
	 */
	private $collection  = '';

	/**
	 * 字段过滤查询
	 */
	private $fq          = '';
	
	/**
	 * 默认返回的数据条数
	 */
	private $limit       = 1;


	/**
	 * 检查是否安装SolrClient扩展
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		if ( !class_exists('SolrClient') )
		{
			throw new SpringException("Solr扩展不存在!");
		}
	}

	/**
	 * 清理资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		foreach ( $this->connections as $collection )
		{
			$collection['query']  = null;
			$collection['client'] = null;
		}
		$this->connections = null;
	}

	/**
	 * 创建文档（更新索引源）
	 *
	 * @access	public
	 * @param	string	$collection	文档集合
	 * @param	array	$data		键值对的一维数组（键：字段，值：字段值）
	 * @param	int		$seconds	多少秒后自动添加
	 * @return	bool
	 */
	public function create($collection, $data, $seconds = 0)
	{
		if ( empty($data) )
		{
			return false;
		}

		$this->connect($collection);
		$document = new SolrInputDocument();
		foreach ( $data as $field => $value ) 
		{
			$document->addField($field, $value);
		}

		if ( $seconds )
		{
			$this->solrClient->addDocument($document, false, 1000*$seconds);
		}
		else
		{
			$this->solrClient->addDocument($document);
			$this->solrClient->commit();
		}

		return true;
	}

	/**
	 * 删除文档（更新索引源） 
	 *
	 * @access	public
	 * @param	array	$rule	数据删除规则
	 * @return	bool
	 */
	public function remove($collection, $rule)
	{
		$this->connect($collection);
		$this->parse($rule);
		$this->fq && $this->solrQuery->setQuery(implode(' AND ', $this->fq));
		$query = $this->solrQuery->getQuery();
		if ( $query ) 
		{
			$this->solrClient->deleteByQuery($query);
			$this->solrClient->commit();

			return true;
		}

		return false;
	}

	/**
	 * 数据查询
	 *
	 * @access	public
	 * @param	string	$collection	文档集合
	 * @param	array	$rule		查询规则
	 * @return	array
	 */
	public function find($collection, $rule)
	{
		$this->connect($collection);
		$this->parse($rule);
		$this->fq && $this->solrQuery->setQuery(implode(' AND ', $this->fq));
		$response = $this->solrClient->query($this->solrQuery);
		
		return $this->buildResult($response->getResponse());
	}

	/**
	 * 数据查询（分页）
	 *
	 * @access	public
	 * @param	string	$collection	文档集合
	 * @param	array	$rule		查询规则
	 * @return	array
	 */
	public function findAll($collection, $rule)
	{
		$rule['isPage'] = 1;
		$this->connect($collection);
		$this->parse($rule);
		$this->fq && $this->solrQuery->setQuery(implode(' AND ', $this->fq));
		$response = $this->solrClient->query($this->solrQuery);
		
		return $this->buildResult($response->getResponse(), 1);
	}

	/**
	 * 按查询条件统计
	 *
	 * @access	public
	 * @param	string	$collection	文档集合
	 * @param	array	$rule		查询规则
	 * @return	int
	 */
	public function count($collection, $rule)
	{
		$this->connect($collection);
		$this->parse($rule);
		$this->fq && $this->solrQuery->setQuery(implode(' AND ', $this->fq));
		$this->solrQuery->setStart(0);
		$this->solrQuery->setRows(0);
		$response = $this->solrClient->query($this->solrQuery);
		$response = $response->getResponse();

		return isset($response['response']['numFound']) ? $response['response']['numFound'] : 0;
	}

	/**
	 * 分组统计
	 *
	 * @access	public
	 * @param	string	$collection	文档集合
	 * @param	array	$rule		查询规则
	 * @return	array
	 */
	public function groupCount($collection, $rule)
	{
		if ( !isset($rule['groupField']) || !is_array($rule['groupField']) || !$rule['groupField'] )
		{
			return array();
		}

		$this->connect($collection);
		$this->parse($rule);
		$this->fq && $this->solrQuery->setQuery(implode(' AND ', $this->fq));
		$this->solrQuery->setFacet(true);
		
		foreach ( $rule['groupField'] as $field )
		{
			$this->solrQuery->addFacetField($field);
		}

		$this->solrQuery->setFacetMinCount(2);
		$this->solrQuery->setStart(0);
		$this->solrQuery->setRows(0);
		$response = $this->solrClient->query($this->solrQuery);
		$response = $response->getResponse();

		if ( !isset($response['facet_counts']['facet_fields']) )
		{
			return array();
		}

		$count = array();
		foreach ( $response['facet_counts']['facet_fields'] as $field => $values )
		{
			foreach ( $values as $key => $value )
			{
				$count[$field][$key] = $value;
			}
		}

		return $count;
	}

	/**
	 * 连接Solr服务器
	 *
	 * @access	private
	 * @param	string	$collection	文档集合
	 * @return	void
	 */
	private function connect($collection)
	{
		$this->collection = $collection;
		if ( isset($this->connections[$this->collection]) && $this->connections[$this->collection] ) 
		{
			$this->solrClient = $this->connections[$this->collection]['client'];
			$this->solrQuery  = $this->connections[$this->collection]['query'];
			return ;
		}

		if ( !file_exists($this->configFile) ) 
		{
			throw new SpringException("Solr配置文件：".$this->configFile."不存在!");
		}

		$options                                        = require($this->configFile);
		$options['path']                                = $options['path'].$collection;
		$this->solrClient                               = new SolrClient($options);
		$this->solrQuery                                = new SolrQuery();
		$this->connections[$this->collection]['client'] = $this->solrClient;
		$this->connections[$this->collection]['query']  = $this->solrQuery;
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
		$this->parseLLike($rule);
		$this->parseLike($rule);
		$this->parseRLike($rule);
		$this->parseRaw($rule);
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
			foreach ( $rule['col'] as $field )
			{
				$this->solrQuery->addField($field);
			}
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
			$kv = array();
			foreach ( $rule['eq'] as $field => $value )
			{
				$value && $kv[] = "{$field}:{$value}";
			}
			$kv && $this->fq[] = implode(' AND ', $kv);
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
			foreach ( $rule['in'] as $field => $values )
			{
				$ins = array();
				if ( is_array($values) && !empty($values) )
				{
					$kv = array();
					foreach ( $values as $value )
					{
						$kv[] = "{$field}:{$value}";
					}
					$kv && $ins[] = implode(' OR ', $kv);
				}
				$ins && $this->fq[] = '('.implode(' OR ', $kv).')';
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
			$kv = array();
			foreach ( $rule['scope'] as $field => $value )
			{
				if ( is_array($value) && count($value) == 2 && $value[0] < $value[1] )
				{
					$kv[] = "{$field}:[{$value[0]} TO {$value[1]}]";
				}
			}
			$kv && $this->fq[] = '('.implode(' AND ', $kv).')';
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
		if ( isset($rule['lLike']) && is_array($rule['lLike']) && !empty($rule['lLike']) )
		{
			$kv = array();
			foreach ($rule['lLike'] as $field => $value)
			{
				$value && $kv[] = "{$field}:{$value}*";
			}
			$kv && $this->fq[] = '('.implode(' AND ', $kv).')';
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
		if ( isset($rule['like']) && is_array($rule['like']) && !empty($rule['like']) )
		{
			$kv = array();
			foreach ($rule['like'] as $field => $value)
			{
				$value && $kv[] = "{$field}:*{$value}*";
			}
			$kv && $this->fq[] = '('.implode(' AND ', $kv).')';
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
		if ( isset($rule['rLike']) && is_array($rule['rLike']) && !empty($rule['rLike']) )
		{
			$kv = array();
			foreach ($rule['rLike'] as $field => $value)
			{
				$value && $kv[] = "{$field}:*{$value}";
			}
			$kv && $this->fq[] = '('.implode(' AND ', $kv).')';
		}
	}

	/**
	 * 解析原始查询规则
	 *
	 * @access	private
	 * @param	array	$rule	查询规则
	 * @return	void
	 */
	private function parseRaw($rule)
	{
		if ( isset($rule['raw']) && !is_array($rule['raw']) && !empty($rule['raw']) )
		{
			$this->fq[] = $rule['raw'];
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
		if ( isset($rule['order']) && is_array($rule['order']) )
		{
			foreach ( $rule['order'] as $field => $value )
			{
				$value = strtolower($value) == 'asc' ? SolrQuery::ORDER_ASC : SolrQuery::ORDER_DESC;
				$this->solrQuery->addSortField($field, $value); 
			}
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
		if ( isset($rule['group']) && is_array($rule['group']) )
		{
			//$this->solrQuery->setGroup(true);
			foreach ( $rule['group'] as $field => $value )
			{
				$value = strtolower($value) == 'asc' ? SolrQuery::ORDER_ASC : SolrQuery::ORDER_DESC;
				$this->solrQuery->addGroupSortField($field, $value);
			}
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
			$this->solrQuery->setStart($offset);
			$this->solrQuery->setRows($limit);
		}
		else
		{
			$this->solrQuery->setStart(0);
			$this->solrQuery->setRows($limit);
		}
	}

	/**
	 * 构建结果集
	 *
	 * @access	private
	 * @param	array	$response   solr结果集
	 * @param	int		$isPage		是否分页
	 * @return	array
	 */
	private function buildResult($response, $isPage = 0)
	{
		if ( $this->debug )
		{
			print_r($response);
		}

		if ( 
			!isset($response['response']['docs']) 
			|| !$response['response']['docs']
			|| !is_array($response['response']['docs']) )
		{
			return $isPage ? array('rows' => array(), 'total' => 0) : array();
		}

		$rows = array();
		foreach ( $response['response']['docs'] as $doc )
		{
			$row = array();
			foreach ( $doc as $field => $value )
			{
				$row[$field] = is_array($value) ? $value[0] : $value;
			}
			$rows[] = $row;
		}

		if ( $isPage )
		{
			return array(
				'rows'  => $rows,
				'total' => $response['response']['numFound'],
				);
		}
		else
		{
			return $rows;
		}
	}
}
?>