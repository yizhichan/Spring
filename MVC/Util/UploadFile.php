<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 文件上传组件(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class UploadFile
{
	public  $msg     = null;    //异常消息
	public  $path    = null;    //上传文件路径
	public  $upFile  = null;    //上传到服务器上的文件名
	public	$size    = null;    //文件大小
	public  $maxSize = null;    //上传文件最大大小
	public  $upType  = null;    //上传文件类型
	public  $ext     = null;    //扩展名
	
	
	/**
	 * 初始化
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
	}
	
	/**
	 * 清理资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		 $this->msg     = null;
		 $this->path    = null;
		 $this->upFile  = null;
		 $this->maxSize = null;
		 $this->upType  = null;
		 $this->ext     = null;
	}

	/**
	 * 检查上传文件信息
	 *
	 * @access	private
	 * @param	string	$name	文件名 
	 * @param	int		$size	文件大小
	 * @return	bool
	 */
	private function checkFile($name, $size)
	{
		if ( $size > $this->maxSize )
		{
			$this->msg = "上传文件 $name 超过规定大小!";
			return false;
		}

		if ( !strstr(strtolower($this->upType), strtolower($this->getExt($name))) )
		{
			$this->msg = "没有上传.".$this->getExt($name)."文件格式的权限, ";
			$this->msg .= "只允许上传" . strtolower($this->upType) . "格式的文件";
			return false;
		}
		return true;
	}
	
	/**
	 * 获取文件扩展名
	 *
	 * @access	public
	 * @param	string	$name	文件名 
	 * @return	string
	 */
	public function getExt($name)
	{
		return pathinfo($name, PATHINFO_EXTENSION);
	}

	/**
	 * 获取文件大小
	 *
	 * @access	public
	 * @param	string	$name	文件名 
	 * @return	string
	 */
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	 * 创建目录
	 *
	 * @access	public
	 * @param	string	$path	路径
	 * @return	bool
	 */
	public function createDir($path)
	{
		if ( file_exists($path) ) {
			return true;
		}

		$dirs  = explode('/', $path);
		$total = count($dirs);
		$temp  = '';

		for ( $i=0; $i<$total; $i++ ) {
			$temp .= $dirs[$i].'/';
			if ( !is_dir($temp) ) {
				if ( !@mkdir($temp) ) {
					return false;
				}
				@chmod($temp, 0777);
			}
		}
		return true;
	}
	
	/**
	 * 修改文件名
	 *
	 * @access	private
	 * @param	string	$name	文件名 
	 * @return	string
	 */
	private function changeName($name)
	{
		return md5(time().$name).".".$this->getExt($name);
	}
	
	/**
	 * 检查路径，不存在则创建
	 *
	 * @access	private
	 * @return	bool
	 */
	private function checkPath()
	{
		if ( !file_exists($this->path) )
		{
			$this->createDir($path);
		}
		return true;
	}


	
	/**
	 * 检查文件是否上传成功
	 *
	 * @access	private
	 * @param	array	$file	上传的文件信息 
	 * @return	bool
	 */
    private function checkUpload($file)
    {
        if ( !empty($file['error']) ) {
            switch( $file['error'] ) {
                case '1':
                    $this->msg = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $this->msg = '超过表单允许的大小。';
                    break;
                case '3':
                    $this->msg = '文件只有部分被上传。';
                    break;
                case '4':
                    $this->msg = '请选择文件。';
                    break;
                case '6':
                    $this->msg = '找不到临时目录。';
                    break;
                case '7':
                    $this->msg = '写文件到硬盘出错。';
                    break;
                case '8':
                    $this->msg = 'File upload stopped by extension。';
                    break;
                default:
                    $this->msg = '未知错误。';
            }

            return false;
        }
        return true;
    }
	
	/**
	 * 文件上传
	 *
	 * @access	public
	 * @param	array	$file	上传的文件信息 
	 * @return	bool
	 */
	public function upload($file)
	{
		$this->size = $file["size"];
		if ( !$this->checkUpload($file) ) 
		{
			return false;
		}

		if ( !$this->checkPath() )
		{
			return false;
		}

		if ( !$this->checkFile($file["name"], $file["size"]) )
		{
			return false;
		}
		
		$name = $this->changeName($file["name"]);
		if ( move_uploaded_file($file["tmp_name"], $this->path."/".$name) )
		{
			$this->upFile = $name;
			return true;
		}
		$this->msg = "网络故障,上传失败";
		return false;
	}
}
?>