<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据分页
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class PageNew
{
	/**
	 * 地址栏参数
	 */
	public $input  = array();


	/**
	 * 页码
	 */
	public $page   = 0;

	/**
	 * url后缀
	 */
	public $suffix = '';

	/**
	 * url前缀
	 */
	public $prefix = '?page=';


	/**
	 * 分页方法
	 */
	public function get($total, $pageRows = 20, $point = 10, $style = 'on')
	{
		$prior   = ceil($point/2) + 1;
		$back    = ceil($point/2);
		$num     = ceil($total/$pageRows);
		$urlPage = isset($this->input['page']) ? intval($this->input['page']) : 0;
		$page    = $this->page ? $this->page : $urlPage;
		if( $page <= 0)   $page = 1;
		if( $page > $num) $page = $num;

		$param = '';
		foreach ( $this->input as $key => $value ) {
			if ( $key != 'page' ) {
				$param .= "$key=$value&";
			}
		}
		$param && $this->prefix = "?{$param}page=";
		
		$result['current']	  = $page;
		$result['first']	  = ($page>1 ? $this->prefix.'1'.$this->suffix : 'javascript:;');
		$result['pre']		  = ($page-1>0 ? $this->prefix.($page-1).$this->suffix : 'javascript:;');
		$result['next']		  = ($page+1<=$num ? $this->prefix.($page+1).$this->suffix : 'javascript:;');
		$result['last']		  = ($num>1 && $page<$num ? $this->prefix.$num.$this->suffix : 'javascript:;');
		$result['recordNum']  = $total;
		$result['pageNum']    = $num;
		$result['jump']       = '';
		$result['start']	  = ($page - 1) * $pageRows + 1 < 0 ? 0 : ($page - 1) * $pageRows + 1;
		$result['end'] 		  = $num > $page ? $page * $pageRows : $total;

		$jumper = $listStr = '';

		if ($page <= $back && ($page + $point-1 <=$num) )
		{
			for($j=1; $j<=$point; $j++)
			{
				$link = $j == $page ? '<a href="javascript:;" class="'.$style.'">'.$j."</a> " : '<a href="'.$this->prefix.$j.$this->suffix.'">'.$j."</a>";
				$listStr .= $link;
			}
		}
		else
		{
			for($i=1; $i<=$num; $i++)
			{
				if ($i < ($page + $back) && $i > ($page - $prior))
				{
					$point = $i == $page ? '<a href="javascript:;" class="'.$style.'">'.$i."</a>" : '<a href="'.$this->prefix.$i.$this->suffix.'">'.$i."</a>";
					$listStr .= $point;
				}
			}
		}
		$result['point']= $listStr;
		$result['jump']	= '
                            <script language="JavaScript" type="text/JavaScript">
                            function page_jump(targ,$num)
                            {
                                var jumpnum = document.getElementById("jumppage").value;
                                if(jumpnum>0 && jumpnum<=$num){
                                    eval(targ+".location=\''.$this->prefix.'"+jumpnum+"\'");
                                }

                            }
                            </script>
                            <span>到第 </span><input type="text" id="jumppage"/>
                            <label class="mj-qbtn mj-hco" onclick="page_jump(\'this\','.$num.')">确定</label>
                                                            ';
		return $result;
	}
}
?>