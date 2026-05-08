<?php
/**
 * @file
 *
 * @brief 
 * 加解密
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


// openssl settings 
define('SSL_ROOT',				 RPATH_CONFIG.DS.'ssl');
define('SSL_CRYPT_BITS', 		512);
define('SSL_PASSPHRASE', 		'3NWARE');

class CEncrypt 
{
	//初始置换IP=[64]
	protected  $IP_Tbl = array(
		58, 50, 42, 34, 26, 18, 10, 2, 60, 52, 44, 36, 28, 20, 12, 4,
		62, 54, 46, 38, 30, 22, 14, 6, 64, 56, 48, 40, 32, 24, 16, 8,
		57, 49, 41, 33, 25, 17,  9, 1, 59, 51, 43, 35, 27, 19, 11, 3,
		61, 53, 45, 37, 29, 21, 13, 5, 63, 55, 47, 39, 31, 23, 15, 7
		);
	//逆转换IP^-1=[64]
	protected  $IPR_Tbl = array(
		40, 8, 48, 16, 56, 24, 64, 32, 39, 7, 47, 15, 55, 23, 63, 31,
		38, 6, 46, 14, 54, 22, 62, 30, 37, 5, 45, 13, 53, 21, 61, 29,
		36, 4, 44, 12, 52, 20, 60, 28, 35, 3, 43, 11, 51, 19, 59, 27,
		34, 2, 42, 10, 50, 18, 58, 26, 33, 1, 41,  9, 49, 17, 57, 25
		);
	//扩展用矩阵=[48]
	protected  $E_Tbl = array(
		32,  1,  2,  3,  4,  5,  4,  5,  6,  7,  8,  9,
		8,  9, 10, 11, 12, 13, 12, 13, 14, 15, 16, 17,
		16, 17, 18, 19, 20, 21, 20, 21, 22, 23, 24, 25,
		24, 25, 26, 27, 28, 29, 28, 29, 30, 31, 32,  1
		);
	//32位置换函数 P 用于输出=[32]
	protected  $P_Tbl = array(
		16, 7, 20, 21, 29, 12, 28, 17, 1,  15, 23, 26, 5,  18, 31, 10,
		2,	8, 24, 14, 32, 27, 3,  9,  19, 13, 30, 6,  22, 11, 4,  25
		);
	//序号选择表=[56]
	protected  $PC1_Tbl = array(
		57, 49, 41, 33, 25, 17,  9,  1, 58, 50, 42, 34, 26, 18,
		10,  2, 59, 51, 43, 35, 27, 19, 11,  3, 60, 52, 44, 36,
		63, 55, 47, 39, 31, 23, 15,  7, 62, 54, 46, 38, 30, 22,
		14,  6, 61, 53, 45, 37, 29, 21, 13,  5, 28, 20, 12,  4
		);
	// permuted choice key (Tbl) =[48]
	protected  $PC2_Tbl = array(
		14, 17, 11, 24,  1,  5,  3, 28, 15,  6, 21, 10,
		23, 19, 12,  4, 26,  8, 16,  7, 27, 20, 13,  2,
		41, 52, 31, 37, 47, 55, 30, 40, 51, 45, 33, 48,
		44, 49, 39, 56, 34, 53, 46, 42, 50, 36, 29, 32
		);
	// number left rotations of pc1 =[16]
	protected  $LOOP_Tbl = array(
		1,1,2,2,2,2,2,2,1,2,2,2,2,2,2,1
		);
	// The (in)famous S-boxes =[8][4][16]
	protected  $S_Box = // S1
	array(14, 4,	13,	 1,  2, 15, 11,  8,  3, 10,  6, 12,  5,  9,  0,  7,
		0, 15,  7,  4, 14,  2, 13,  1, 10,  6, 12, 11,  9,  5,  3,  8,
		4,  1, 14,  8, 13,  6,  2, 11, 15, 12,  9,  7,  3, 10,  5,  0,
		15, 12,  8,  2,  4,  9,  1,  7,  5, 11,  3, 14, 10,  0,  6, 13,
		// S2
		15,  1,  8, 14,  6, 11,  3,  4,  9,  7,  2, 13, 12,  0,  5, 10,
		3, 13,  4,  7, 15,  2,  8, 14, 12,  0,  1, 10,  6,  9, 11,  5,
		0, 14,  7, 11, 10,  4, 13,  1,  5,  8, 12,  6,  9,  3,  2, 15,
		13,  8, 10,  1,  3, 15,  4,  2, 11,  6,  7, 12,  0,  5, 14,  9,
		// S3
		10,  0,  9, 14,  6,  3, 15,  5,  1, 13, 12,  7, 11,  4,  2,  8,
		13,  7,  0,  9,  3,  4,  6, 10,  2,  8,  5, 14, 12, 11, 15,  1,
		13,  6,  4,  9,  8, 15,  3,  0, 11,  1,  2, 12,  5, 10, 14,  7,
		1, 10, 13,  0,  6,  9,  8,  7,  4, 15, 14,  3, 11,  5,  2, 12,
		// S4
		7, 13, 14,  3,  0,  6,  9, 10,  1,  2,  8,  5, 11, 12,  4, 15,
		13,  8, 11,  5,  6, 15,  0,  3,  4,  7,  2, 12,  1, 10, 14,  9,
		10,  6,  9,  0, 12, 11,  7, 13, 15,  1,  3, 14,  5,  2,  8,  4,
		3, 15,  0,  6, 10,  1, 13,  8,  9,  4,  5, 11, 12,  7,  2, 14,
		// S5
		2, 12,  4,  1,  7, 10, 11,  6,  8,  5,  3, 15, 13,  0, 14,  9,
		14, 11,  2, 12,  4,  7, 13,  1,  5,  0, 15, 10,  3,  9,  8,  6,
		4,  2,  1, 11, 10, 13,  7,  8, 15,  9, 12,  5,  6,  3,  0, 14,
		11,  8, 12,  7,  1, 14,  2, 13,  6, 15,  0,  9, 10,  4,  5,  3,
		// S6
		12,  1, 10, 15,  9,  2,  6,  8,  0, 13,  3,  4, 14,  7,  5, 11,
		10, 15,  4,  2,  7, 12,  9,  5,  6,  1, 13, 14,  0, 11,  3,  8,
		9, 14, 15,  5,  2,  8, 12,  3,  7,  0,  4, 10,  1, 13, 11,  6,
		4,  3,  2, 12,  9,  5, 15, 10, 11, 14,  1,  7,  6,  0,  8, 13,
		// S7
		4, 11,  2, 14, 15,  0,  8, 13,  3, 12,  9,  7,  5, 10,  6,  1,
		13,  0, 11,  7,  4,  9,  1, 10, 14,  3,  5, 12,  2, 15,  8,  6,
		1,  4, 11, 13, 12,  3,  7, 14, 10, 15,  6,  8,  0,  5,  9,  2,
		6, 11, 13,  8,  1,  4, 10,  7,  9,  5,  0, 15, 14,  2,  3, 12,
		// S8
		13,  2,  8,  4,  6, 15, 11,  1, 10,  9,  3, 14,  5,  0, 12,  7,
		1, 15, 13,  8, 10,  3,  7,  4, 12,  5,  6, 11,  0, 14,  9,  2,
		7, 11,  4,  1,  9, 12, 14,  2,  0,  6, 10, 13, 15,  3,  5,  8,
		2,  1, 14,  7,  4, 10,  8, 13, 15, 12,  9,  0,  3,  5,  6, 11
		);
	//////////////////////////////////////////////////////////////////////////
	protected  $SubKey=array(array());	//16圈子密钥=[16][48]
	protected  $strary=array();		//源码所对应的数组
	protected  $keyary=array();		//密匙数组
	
	
	/**
	 * iv for AES
	 *
	 * @var mixed 
	 *
	 */
	protected $_iv = ""; 
	
	//构造
	public function __construct()
	{
		//
		//$this->_iv = pack('H*', "336e776172650cd8b54763051cef08bc");
		$this->_iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"; //"1234567812345621";
		if (!is_dir(SSL_ROOT))
			s_mkdir(SSL_ROOT);
	}	
	
	//兼容php4构造
	public function CEncrypt($file=null) 
	{
		$this->__construct();
	}
	
	
	//////////////////////////////////////////////////////////////////////////
	//初始化密匙及原文
	protected function initary($key,$str){
		for($i=0;$i<8;$i++)			//8位密匙
			$this->keyary[$i]=ord($key[$i]);
		for($i=0;$i<8;$i++)
			$this->strary[$i]=ord($str[$i]);
	}
	//循环左移
	protected function RotateL(&$In,$len,$loop)
	{
		$Tmp=array();
		for($i=0;$i<$loop;$i++)
			$Tmp[$i]=$In[$i];
		for($i=0;$i<$len-$loop;$i++)
			$In[$i]=$In[$i+$loop];
		for($i=0;$i<$loop;$i++)
			$In[$len-$loop+$i]=$Tmp[$i];
	}
	//把位变换成字节
	function BitToByte(&$Out,$In,$bits)
	{
		for($i=0;$i<$bits;$i++){
			$Out[$i/8] |= $In[$i]<<($i%8);
		}
	}
	//置换操作
	protected function Transform(&$Out,$In,$Tbl,$len){
		static $Tmp=array();
		for($i=0;$i<$len;$i++)
			$Tmp[$i] = $In[$Tbl[$i]-1 ];
		for($i=0;$i<$len;$i++)
			$Out[$i]=$Tmp[$i];
	}
	//设置加密因子
	protected function SetKey() {
		$K=array();$Rk=array();$Lk=array();
		$this->ByteToBit($K,$this->keyary,64);
		$this->Transform($K,$K,$this->PC1_Tbl, 56);
		for($i=0; $i<16; $i++) {
			for($j=0;$j<28;$j++){
				$Rk[$j]=$K[$j+28];
				$Lk[$j]=$K[$j];
			}
			$this->RotateL($Rk,28,$this->LOOP_Tbl[$i]);
			$this->RotateL($Lk,28,$this->LOOP_Tbl[$i],28);
			for($j=0;$j<28;$j++){
				$K[$j]=$Lk[$j];
				$K[$j+28]=$Rk[$j];
			}
			$this->Transform($this->SubKey[$i], $K, $this->PC2_Tbl, 48);
		}
	}
	//将In字符串转换成64位二进制位到Out数组中
	protected  function ByteToBit(&$Out,$In,$bits){
		for($i=0; $i<$bits; $i++){
			$Out[$i]=($In[$i/8]>>($i%8)) & 1;
		}
	}
	//S操作
	protected function S_func(&$Out,$In)
	{	$Tmp=array();
		for($i=0;$i<8;$i++) {
			$j=($In[0+$i*6]<<1)+$In[5+$i*6];
			$k=($In[1+$i*6]<<3)+($In[2+$i*6]<<2)+($In[3+$i*6]<<1)+ $In[4+$i*6];
			for($x=0;$x<4;$x++){
				$Tmp[$x]=($this->S_Box[$x/8+$i*64+$j*16+$k]>>($x%8))&1;
				$Out[$i*4+$x]=$Tmp[$x];
			}
		}
	}
	//F操作
	protected  function F_func(&$In,$Ki)	//In[32],Ki[48]
	{
		$MR=array();			//MR[48]
		$this->Transform($MR,$In,$this->E_Tbl,48);
		for($i=0;$i<48;$i++){
			$MR[$i]^=$Ki[$i];		//异或处理
		}
		$this->S_func($In,$MR);
		$this->Transform($In,$In,$this->P_Tbl, 32);
	}
	//加解密
	protected function Run(&$Out,$key,$str,$type=0){
		$M=array();$Rm=Array();$Lm=array();
		$this->initary($key,$str);
		$this->SetKey();
		$this->ByteToBit($M,$this->strary,64);
		$this->Transform($M,$M,$this->IP_Tbl,64);
		if($type==0)	//加密
			for($i=0;$i<16;$i++) {
				for($j=0;$j<32;$j++){
					$Rm[$j]=$M[32+$j];	//右32位
					$Lm[$j]=$M[$j];		//左32位
					$Tmp[$j]=$Rm[$j];	//保存右32位
				}
				$this->F_func($Rm,$this->SubKey[$i]);//右32位
				for($j=0;$j<32;$j++)
					$Rm[$j]^=$Lm[$j];		//异或处理
				
				for($j=0;$j<32;$j++)		//置左32位为原先的右32位
					$Lm[$j]=$Tmp[$j];
				for($j=0;$j<32;$j++){		//重置数组M
					$M[$j]=$Lm[$j];
					$M[$j+32]=$Rm[$j];
				}
			}
		else for($i=15; $i>=0; $i--){
				for($j=0;$j<32;$j++){
					$Rm[$j]=$M[32+$j];	//右32位
					$Lm[$j]=$M[$j];		//左32位
					$Tmp[$j]=$Lm[$j];	//保存右32位
				}
				$this->F_func($Lm,$this->SubKey[$i]);
				for($j=0;$j<32;$j++)
					$Lm[$j]^=$Rm[$j];
				for($j=0;$j<32;$j++)
					$Rm[$j]=$Tmp[$j];
				for($j=0;$j<32;$j++){		//重置数组M
					$M[$j]=$Lm[$j];
					$M[$j+32]=$Rm[$j];
				}
			}
		$this->Transform($M,$M,$this->IPR_Tbl, 64);
		$this->BitToByte($Out,$M, 64);
	}
	//加密
	function Encode($key,$str){
		$ret=array();
		$this->Run($ret,$key,$str);
		return $this->arytostr($ret);
	}
	//解密
	function Decode($key,$str){
		$ret=array();
		$this->Run($ret,$key,$str,1);
		return $this->arytostr($ret);
	}
	//将数组转换成字串
	function arytostr($ary){
		$ret="";
		for($i=0;$i<count($ary);$i++){
			$ret.=chr($ary[$i]);
		}
		return $ret;
	}
	
	
	//
	/*/	DES字符加密解密
	//		$type	=	0	->使用$key加密$str;
	//				=	1	->使用$key解密$str;
	//*/
	
	/**
	 * This is method des
	 * 
	 * 测试发现运行效率差
	 * 
	 * @param mixed $str This is a description
	 * @param mixed $key This is a description
	 * @param mixed $type This is a description
	 * @return mixed This is the return value description
	 *
	 */
	function des($str,$key="",$type=0){
		switch ($type) {
			case 0	:
			default	: //加密encode
				$ret="";
				for($i=0;$i<strlen($str)/8;$i++){
					$src = substr($str,$i*8,8);
					$ret .= $this->Encode($key,$src);
				}
				break;
			case 1	: //解密decode
				$ret="";
				for($i=0;$i<strlen($str)/8;$i++){
					$src = substr($str,$i*8,8);
					$ret .= $this->Decode($key,$src);
				}
				$str="";
				for($i=0;$i<strlen($ret);$i++)
					if($ret[$i]!=chr(0)) $str.=$ret[$i];
				$ret=$str;
				break;
		}
		return $ret;
	}
	
	
	
	function key($txt) 
	{
		$encrypt_key = md5($cf->hash);
		$ctr = 0;
		$tmp = "";
		
		for ($i=0; $i<strlen($txt); $i++)
		{
			if ($ctr == strlen($encrypt_key))  $ctr=0;
			$tmp .= substr($txt,$i,1) ^ substr($encrypt_key, $ctr, 1);
			$ctr++;
		}
		
		return $tmp;
	}
	
	function html_encrypt ($txt) 
	{
		return base64_encode($this->_encrypt($txt));
	}
	
	
	function _encrypt($txt) 
	{
		$encrypt_key = md5(microtime()); // Public key
		$ctr=0;
		$tmp = "";
		for ($i=0; $i< strlen($txt); $i++)
		{
			if ($ctr==strlen($encrypt_key))
			{
				$ctr=0;
			}
			
			$tmp.= substr($encrypt_key, $ctr,1) .
				(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
			
			$ctr++;
		}
		
		return $this->key($tmp);
	}
	
	function html_decrypt($txt) 
	{
		return $this->_decrypt(base64_decode($txt));
	}
	
	protected function _decrypt($txt)
	{
		$txt = $this->key($txt);
		$tmp = "";
		for ($i=0; $i<strlen($txt); $i++)
		{
			$md5 = substr($txt,$i,1);
			$i++;
			$tmp.= (substr($txt,$i,1) ^ $md5);
		}
		
		return $tmp;
	}
	
	
	protected function pkcs5_pad($text,$block=8)
	{
		$pad = $block - (strlen($text) % $block);
		return $text . str_repeat(chr($pad), $pad);
	}
	
	
	protected function pkcs5_unpad($text)
	{
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return $text;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) 
			return $text;
		return substr($text, 0, -1 * $pad);
	}
	
	
	
	public function mcrypt_des_decode($key, $encrypted)
	{
		if (!$encrypted)
			return false;
		$key = substr($key, 0, 8);
		//' ' => '+' 
		$base64code = str_replace(' ', '+', $encrypted);
		$encrypted = base64_decode($base64code);
		if (!$encrypted)
			return false;
		
		$td = mcrypt_module_open(MCRYPT_DES,'',MCRYPT_MODE_CBC,''); //使用MCRYPT_DES算法,cbc模式
		//$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		//$ks = mcrypt_enc_get_key_size($td);
		mcrypt_generic_init($td, $key, $key);       //初始处理
		
		$decrypted = mdecrypt_generic($td, $encrypted);       //解密
		
		mcrypt_generic_deinit($td);       //结束
		mcrypt_module_close($td);
		
		$y =  $this->pkcs5_unpad($decrypted);
		return $y;
	}
	
	public function mcrypt_des_encode($key, $text)
	{
		if (!$text)
			return false;
		
		$y = $this->pkcs5_pad($text);
		
		$td = mcrypt_module_open(MCRYPT_DES,'',MCRYPT_MODE_CBC,''); //使用MCRYPT_DES算法,cbc模式
		/// $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		//$ks = mcrypt_enc_get_key_size($td);
		$key = substr($key, 0, 8);
		mcrypt_generic_init($td, $key, $key);       //初始处理
		$encrypted = mcrypt_generic($td, $y);       //解密
		mcrypt_generic_deinit($td);       //结束
		mcrypt_module_close($td);
		
		return base64_encode($encrypted);
	}
	
	
	/**
	 * aes加密
	 *
	 * @param mixed $key This is a description
	 * @param mixed $data This is a description
	 * @return mixed 加密后的base64编码字串
	 *
	 */
	public function aesEncrypt($key, $data)
	{
		$iv = $this->_iv;
		// 加密数据
		//$encrypted_data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
		
		/*
		$options 数据格式选项(可选)【选项有：】
		
		1、0 默认填充方式
		2、OPENSSL_RAW_DATA=1 会用PKCS#7进行补位
		3、OPENSSL_ZERO_PADDING=2
		4、OPENSSL_NO_PADDING=3
		*/
		
		$encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 
			/*OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING*/0, $iv);
		
		$aesencode = base64_encode($encrypted_data);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$aesencode='.$aesencode);
		
		return $aesencode;
	}
	
	
	/**
	 * ASE解密
	 *
	 * @param mixed $key This is a description
	 * @param mixed $data This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function aesDecrypt($key, $data)
	{
		//AES-CBC 解密
		$iv = $this->_iv;
		
		$encryptedData = base64_decode($data);
		//$decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_CBC, $iv);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$decode='.$decode);
		
		//Warning: openssl_decrypt(): Failed to base64 decode on line 508
		$decode = @openssl_decrypt($encryptedData, 'AES-256-CBC', $key,
				/*OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING*/0, $iv);
		/*if (!$decode) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'call openssl_decrypt failed!', $key, $data, $encryptedData);
		}*/
		
		return $decode; 
	}	


	public function aesEncrypt2($key, $data)
	{
		$iv = $this->_iv;
		// 加密数据
		$encrypted_data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
		
			
		$aesencode = base64_encode($encrypted_data);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$aesencode='.$aesencode);
		
		return $aesencode;
	}

	public function aesDecrypt2($key, $data)
	{
		//AES-CBC 解密
		$iv = $this->_iv;
		
		$encryptedData = base64_decode($data);
		$decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_CBC, $iv);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$decode='.$decode);
				
		return $decode; 
	}


	public function aesDecryptJS($key, $data)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$key='.$key.',$data='.$data);

		$encrypted_param_passwd = $data;
		//AES-CBC 解密
		$iv='1234567812345678';
		$encryptedData = base64_decode($encrypted_param_passwd);
		//$edb = explode('-', $encryptedData);
		$_key = md5($key);
		//$encryptedData = $edb[1];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$key='.$key.',$encryptedData='.$encryptedData);

		//$encryptedData2 = base64_decode($encryptedData);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $encryptedData, $encryptedData2);
		
		//$decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_CBC, $iv);
		$decode = openssl_decrypt($encryptedData, 'AES-256-CBC', $_key,
				OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$encrypted_param_passwd='.$encrypted_param_passwd.',$decode='.$decode.',$encryptedData='.$encryptedData.',key='.$key);

		return $decode;
	}	

	public function aesEncryptJS($key, $data)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$key='.$key.',$data='.$data);
		//AES-CBC 解密
		$iv='1234567812345678';
		
		$key = md5($key);
		
		$encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 
				OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
		
		
		$encryptedData = base64_encode($encrypted_data);
		
		//$encryptedData = base64_encode($encryptedData);
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$encrypted_param_passwd='.$encrypted_param_passwd.',$param_passwd='.$decode);
		
		return $encryptedData;
	}	
	
	

	
	public function initRSA()
	{
		//if (!file_exists(SSL_ROOT.DS.'id_rsa')) {
			/*
			$config = array(
					'config' => SSL_ROOT.DS.'openssl.cnf',
					'digest_alg' => 'md5',
					'x509_extensions' => 'v3_ca',
					'req_extensions'   => 'v3_req',
					'private_key_bits' => (int)SSL_CRYPT_BITS,
					'private_key_type' => SSL_KEYTYPE_RSA,
					'encrypt_key' => false,
					);
					*/
			$config = array(
					'config' => SSL_ROOT.DS.'openssl.cnf',
					'digest_alg' => 'sha512',
					'private_key_bits' => SSL_CRYPT_BITS, //字节数    512 1024  2048   4096 等
					'private_key_type' => OPENSSL_KEYTYPE_RSA,     //加密类型
					'encrypt_key' => false,
					
					);		
			$dn = array(
					'countryName' => 'CN',
					'stateOrProvinceName' => 'ANHUI',
					'localityName' => 'HEFEI',
					'organizationName' => '3NWARE',
					'organizationalUnitName' => 'RD',
					'commonName' => 'sanxinware.com',
					'emailAddress' => 'master@sanxinware.com'
					);
			
			// Generate a new private (and public) key pair
			//创建公钥和私钥   返回资源
		$res = openssl_pkey_new($config);
		//var_dump($res);	
			//从得到的资源中获取私钥    并把私钥赋给
			
		openssl_pkey_export($res, $privkey, null, $config);
		//var_dump($privkey);
		// Get public key
		$pubkey=openssl_pkey_get_details($res);
		//var_dump($pubkey);
					
			//根据dn提供的信息生成新的CSR（证书签名请求）
			//必须安装有效的 openssl.cnf 以保证此函数正确运行
		$configargs = array('config'=>SSL_ROOT.DS.'openssl.cnf');
		$csr = openssl_csr_new($dn, $privkey,$configargs);
			//var_dump($csr);
			
		if (! openssl_pkey_export_to_file($privkey, SSL_ROOT.DS.'id_rsa', SSL_PASSPHRASE, $config)) {
				rlog('call openssl_pkey_export_to_file error');
				return false;
			}
		//}
		
		//加载
		$privateKey = openssl_pkey_get_private("file://".SSL_ROOT.DS.'id_rsa', SSL_PASSPHRASE);	
		//var_dump($privateKey);
		$keyDetails = openssl_pkey_get_details($privateKey);		
		$max_digits = array( 128  =>19, 256  =>38, 512  =>76, 1024 =>130, 2048 =>260 ); // the js required bits list for different RSA bits.
		//var_dump($keyDetails); 	
		$res['max_digits'] = $max_digits[$keyDetails['bits']];
		$res['rsa_e'] = bin2Hex($keyDetails['rsa']['e']);
		$res['rsa_n'] = bin2Hex($keyDetails['rsa']['n']);
		
		return $res;			
	}
	
}
