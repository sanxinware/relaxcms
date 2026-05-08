<?php

/**
 * @file
 *
 * @brief 
 * 缓存类定义
 *
 * @author Jonny <xjlicn@163.com>
 * @date	2018-8-3
 *
 * Copyright (c), 2018, relaxcms.com
 */
class CCache
{
	protected $_cache_dir;
	
	function __construct()
	{
		$dir = RPATH_CACHE;
		if (!is_dir($dir))
			s_mkdir($dir);
		$this->_cache_dir = $dir;
	}
	
	function CCache()
	{
		$this->__construct();
	}
	
	//创建
	static function GetInstance()
	{
		static $instance;		
		if(!is_object($instance))	{
			$instance = new CCache();			
		}
		return $instance;
	}
		
			
	
	
	private function _get_sql_files($dir)
	{
		$files = array();
		$file = array();
		
		$d = dir($dir);
		$ext = ".sql";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext)
			{
				$file['data'] = file_get_contents($dir.DS.$entry);
				$file['name'] = $entry;
				$file['time'] = fileatime($dir.DS.$entry);
				
				$files[] = $file;
			}
		}
		$d->close();
		
		return $files;
	}
	
	//缓存备份
	public function cache_backup($file, $data, $left, $start, $dir=null)
	{
		$cf = get_config();
		
		$mode = 0777;
		if ($dir === null)
			$dir = RPATH_CACHE;
		
		$dir = $dir.DS."backup";
		is_dir($dir) || @mkdir($dir, $mode);
		
		$path = $dir.DS.$file.".sql";
		
		if($start == 0)
			s_write($path,  $data); //清除文件内容
		else
			s_write($path,  $data, "a+"); //添加模式
		
		$ts = time();
		$bname = $cf['dbname'].'_'.date('Y_m_d_H_i_s', $ts);
		//压缩目录
		if ($left === 0 ) {
			$zlib = $dir.DS.$bname.".zip";
			$files = $this->_get_sql_files($dir);
			
			$zip = Factory::GetZip();
			$zip->compress($zlib, $files);
			
			//删除缓存sql
			s_unlink($dir.DS."*.sql");
		}
		
		return 1;
	}
	
	
	
	//提取Zip文件列表
	public function get_backup_zip_files()
	{
		$files = array();
		$file = array();
		
		$dir = RPATH_CACHE.DS."backup";
		if (!is_dir($dir))
		{
			return false;
		}
		
		$d = dir($dir);
		$ext = ".zip";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext) {
				$file['name'] = $entry;
				$file['time'] = filemtime($dir.DS.$entry);
				$file['size'] = filesize($dir.DS.$entry);
				
				$files[$file['time']] = $file;
			}
		}
		$d->close();
		
		
		return $files;
	}
	
	private function _get_sql_files2($dir)
	{
		$files = array();
		$file = array();
		
		$d = dir($dir);
		$ext = ".sql";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext)
			{
				$file['table'] = substr($entry, 0, strlen($entry)-4);
				$file['name'] = $entry;
				$file['time'] = fileatime($dir.DS.$entry);
				$file['size'] = filesize($dir.DS.$entry);
				
				$files[] = $file;
			}
		}
		$d->close();
		
		return $files;
	}
	
	
	//提取zip中文件信息
	public function get_zip_info($zip, $have_path=false)
	{
		if (!$have_path) {
			$dir = RPATH_CACHE.DS."backup";
			$path = RPATH_CACHE.DS."backup".DS.$zip;
		} else {
			$dir = dirname($zip);
			$path = $zip;
		}
		
		//清理目录
		s_unlink($dir.DS."*.sql");
		
		//解压		
		$z = Factory::GetZip();		
		$z->extract($path, $dir);
		
		//提联sql信息
		return $this->_get_sql_files2($dir);	
	}
	
	
	//备分表恢复
	function backin($table, $left, $start, $count, &$total)
	{
		$dir = RPATH_CACHE.DS."backup";
		$path = $dir.DS.$table.".sql";
		
		$db = Factory::GetDBO();
		$db->backup_in($path, $start, $count, $total);
		
		//
		/*$next = $start + $count;
		if ($next != $total)
		{
			if($left == 0) $left = 1;
		}*/
		
		//清理目录
		if ($left === 0 )
			s_unlink($dir.DS."*.sql");
		
		return "1";		
	}
		
	function cache_var()
	{
		$m = Factory::GetModel('var');
		$m->cache();		
	}

	private function _cache_array($arr, $space='')
	{
		$space .= "\t";
		$res = "array(\n"; 
		foreach($arr as $key=>$v) {
			
			$key = addslashes($key);
			if (is_array($v)) {
				$res .= "$space'$key'=>".$this->_cache_array($v, $space);
			} else {
				$v = addslashes($v);
				$res .= "$space'$key'=>'$v',\n";
			}
		}
		$res .= "$space),\n";
		
		return $res;		
	}
	
	
	/**
	 * 缓存数组
	 *
	 * @param mixed $name 缓存文件名
	 * @param mixed $arr 数组
	 * @return mixed 成功true, 失败false
	 *
	 */
	public function cache_array($name, $arr = null, $cachefile=null)
	{
		if ($cachefile)
			$file = $cachefile;
		else
			$file = RPATH_CACHE.DS.$name.'.php';
		
		$old = array();
		if (file_exists($file)) {
			require $file;
			$old = ${$name};
		}
		
		if ($arr != null ) {
			$cache="<?php\n";
			$cache_array = "\${$name} = array(\n";
			$space = "\t";
			foreach($arr as $key=>$v)
			{
				if (!$key)
					continue;
				//fixed for :It's Your Turn
				$key = addslashes($key);
				
				if (is_array($v)) {
					$cache_array .= "\t'$key'=>".$this->_cache_array($v, $space);
				} else {
					//It's Your Turn
					$v = addslashes($v);
					
					$cache_array .= "\t'$key'=>'$v',\n";	
				}		
			}
			
			$cache_array .= ");\n";
			$cache .= $cache_array."?>";
			
			s_write($file,  $cache);
		}
		

		return $old;
	}
	
	
	public function cache_component2pids($pdb)
	{
		$component2pids = array ();
		foreach ($pdb as $key=>$v) {
			$component2pids[$key] = $v['pid'];
			if ($v['child']) {
				foreach ($v['child'] as $k2 =>$v2) {
					$component2pids[$k2] = $v2['pid'];
				}
			}
		}
		
		$this->cache_array('component2pids', $component2pids);
		return true;
	}
}
