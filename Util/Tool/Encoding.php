<?
/**
 +------------------------------------------------------------------------------
 * Spring框架  数据编码、解码
 +------------------------------------------------------------------------------
 * @mobile  13183857698
 * @oicq    78252859
 * @author  VOID(空) <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class Encoding
{
	/**
	 * 数据编码
	 *
	 * @access	public
	 * @param	mixed	$data		待编码数据
	 * @param	int		$encoding	编码方式(0无、1序列化、2json、3msgpack)
	 * @return	mixed
	 */
	public function encode($data, $encoding = 1)
	{
		if ( $encoding == 0 ) 
		{
			return $data;
		}

		if ( $encoding == 1 ) 
		{
			return serialize($data);
		}

		if ( $encoding == 2 ) 
		{
			return json_encode($data);
		}

		if ( $encoding == 3 ) 
		{
			return msgpack_pack($data);
		}

		return $data;
	}

	/**
	 * 数据解码
	 *
	 * @access	public
	 * @param	mixed	$data		待解码数据
	 * @param	int		$encoding	编码方式(0无、1序列化、2json、3msgpack)
	 * @return	mixed
	 */
	public function decode($data, $encoding = 1)
	{
		if ( $encoding == 0 ) 
		{
			return $data;
		}

		if ( $encoding == 1 )
		{
			return unserialize($data);
		}

		if ( $encoding == 2 ) 
		{
			return json_decode($data);
		}

		if ( $encoding == 3 ) 
		{
			return msgpack_unpack($data);
		}

		return $data;
	}
}
?>