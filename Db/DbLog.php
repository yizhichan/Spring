<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据库日志记录组件
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class DbLog
{
	/**
	 * SQL日志
	 */
	private $sqlLog    = null;

	/**
	 * 开始执行SQL的时间
	 */
	private $startTime = null;

	/**
	 * 结束时间
	 */
	private $endTime   = null;

	/**
	 * SQL计数器
	 */
	private $counter   = 0;


	/**
	 * 初始化
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->startTime = array_sum(explode(' ', microtime()));
	}


	/**
	 * 记录sql执行日志
	 *
	 * @access	public
	 * @param	string	$table	表名
	 * @param	string	$sql	sql语句
	 * @return	void
	 */
	public function record($table, $sql)
	{
		++$this->counter;
		$this->sqlLog .= "<fieldset><legend><font color=red>第 {$this->counter} 次查询</font></legend>{$sql}</fieldset><br>";
		$this->endTime = array_sum(explode(' ', microtime()));
		$this->write($table, $sql);
	}
	
	/**
	 * 输出sql执行日志
	 *
	 * @access public
	 * @return void
	 */
	public function output()
	{
		if ( $this->sqlLog )
		{
			echo $this->sqlLog;
			echo '<div align=center><font color=red>SQL Process: '.number_format(($this->endTime -$this->startTime), 6).'s</font></div>';
		}
	}

	/**
	 * 写日志到文件
	 *
	 * @access	public
	 * @param	string	$table	表名
	 * @param	string	$sql	sql语句
	 * @return	void
	 */
	private function write($table, $sql)
	{
		if ( file_exists(LogDir.'/Sql') )
		{
			$logFile = 'sql-'.date("Y-m-d", time()).'.log';
			$excute  = date("Y-m-d H:i:s", time())."\t".number_format(($this->endTime -$this->startTime), 6).'s';
			file_put_contents(LogDir."/Sql/{$logFile}", "【{$excute}】\t$sql\r\n", FILE_APPEND);
		}
	}
}
?>