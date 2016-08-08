<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 图形验证码组件(MVC辅助工具)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
session_start();
class VerifyImg
{
	public $text        = null;    //验证码上的文字
	public $font        = 5;       //字体文件路径或者整数1-5（系统内部字体）
	public $x           = 2;       //首字符x坐标
	public $y           = 2;       //首字符y坐标
	public $width       = 50;      //图片宽度
	public $height      = 20;      //图片高度 
	public $bgColor     = array(255, 255, 255);
	public $textColor   = array(0, 0, 0);
	public $borderColor = array(255, 255, 255);
	public $noiseColor  = array();
	public $noiseRate   = 0.3;
	public $textSpace   = 2;       //字间距
	public $image       = null;
	public $vName       = 'authNum';  //验证变量名
	
	
	/**
	 * 创建图像
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		$len = strlen($this->text);
		
		//载入字体
		if ( !is_int($this->font) )
		{
			$this->font = imageloadfont($this->font);
		}
		
		//设置文字位置
		if ( is_null($this->x) )
		{
			$this->x = $this->textSpace;
		}

		if ( is_null($this->y) )
		{
			$this->y = $this->textSpace;
		}
		
		//设置宽度
		if ( is_null($this->width) )
		{
			if ( $len == 0 )
			{
				$this->width = $this->x * 2;
			}
			else
			{
				$this->width = $this->textSpace * ($len - 1) + imagefontwidth($this->font) * $len + $this->x * 2;
			}
		}
		
		//设置高度
		if ( is_null($this->height) )
		{
			$this->height = imagefontheight($this->font) + $this->y * 2;
		}
		//噪声数量
		$noiseNum = floor($this->height * $this->width * $this->noiseRate);
		$this->image = imagecreatetruecolor($this->width, $this->height);
		//$this->image = imagecreate($this->width, $this->height);   //如果服务器不支持真彩色
		$colorBG = imagecolorallocate ($this->image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
		$colorText = imagecolorallocate($this->image, $this->textColor[0], $this->textColor[1], $this->textColor[2]);
		$colorBorder = imagecolorallocate($this->image, $this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
		$colorNoise = count($this->noiseColor) == 3 ? imagecolorallocate($this->image, $this->noiseColor[0], $this->noiseColor[1], $this->noiseColor[2]) : null;
		
		//填充背景
		imagefilledrectangle($this->image, 0, 0, $this->width - 1,$this->height - 1, $colorBG);
		//绘制边框
		imagerectangle($this->image, 0, 0, $this->width - 1,$this->height - 1, $colorBorder);
		$isAutoNoiseColor = count($this->noiseColor) != 3;
		
		//绘制噪音
		for ( $i = 0; $i < $noiseNum; $i++ )
		{
			if ($isAutoNoiseColor)
			{
				$colorNoise = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			}
			imagesetpixel($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), $colorNoise);
		}
		
		//绘制文字
		for ( $i = 0; $i < strlen($this->text); $i++ )
		{
			$chr = $this->text[$i];
			$x = $this->x + ($this->textSpace + imagefontwidth($this->font)) * $i;
			imagestring($this->image, $this->font, $x, $this->y, $chr, $colorText);
		}
	}
	
	/**
	 * 生成验证码上的文字
	 *
	 * @access public
	 * @return string
	 */
	public function getRandNum()
	{
		$code = strtoupper(substr(md5(time()),1,4));
		$_SESSION[$this->vName] = $code;
		
		return $code;
	}
	
	/**
	 * 校验验证码
	 *
	 * @access	public
	 * @param	string $authNum	验证码
	 * @return	bool
	 */
	public function verify($authNum='')
	{
		$code = $_SESSION[$this->vName];

		if ( empty($code) ) 
		{
			return false;
		}
		
		if ( strtoupper($authNum) != $code )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * 清除验证码
	 *
	 * @access public
	 * @return void
	 */
	public function clear()
	{
		unset($_SESSION[$this->vName]);
	}

	/**
	 * 输出验证码图片
	 *
	 * @access public
	 * @return void
	 */
	public function show()
	{
		ob_clean();
		header("Content-type:image/jpeg");
		imagejpeg($this->image);
		imagedestroy($this->image);
	}
}
?>