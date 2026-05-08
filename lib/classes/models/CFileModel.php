<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件基础模型类
 * 
 * Copyright (c), 2024, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('FF_CHECKED', 0x1);
define ('FF_RELEASE', 0x2);
define ('FF_DOWNLOAD', 0x4);
define ('FF_READONLY', 0x8);
define ('FF_SHARE', 0x10);
define ('FF_CONVERTED', 0x20);
define ('FF_SNAPPED', 0x40);
define ('FF_MYFLAGS', FF_RELEASE|FF_DOWNLOAD|FF_READONLY|FF_SHARE);

define ('FILE_S_TMP', 0);
define ('FILE_S_NORMAL', 1);
define ('FILE_S_CONVERT', 2);
define ('FILE_S_CONVERTING', 3);
define ('FILE_S_CONVERTED', 4);

define ('FILE_S_LINK', 5);
define ('FILE_S_SYMLINK', 11);

define ('FILE_S_UPLOADDING', 12);

define ('FILE_S_DOWNLOAD', 6);
define ('FILE_S_DOWNLOADING', 7);
define ('FILE_S_SHARED', 8);

define ('FILE_S_DELETING', 9);
define ('FILE_S_DELETED', 10);



define ('FT_VIDEO', 0x1);
define ('FT_AUDIO', 0x2);
define ('FT_IMAGE', 0x4);
define ('FT_DOC',   0x8);
define ('FT_TAR',   0x10);
define ('FT_CODE',  0x20);
define ('FT_OTHER', 0xFF);


/*
'0'=>'临时',
			'1'=>'正常',
			'2'=>'待转码',
			'3'=>'转码中',
			'4'=>'已转码',
			'5'=>'待下载',
			'6'=>'下载中',*/
			

class CFileModel extends CTableModel
{	
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CFileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	/* ============================================================================
	* init functions
	* 
	* ===========================================================================*/
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'lsize':
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'shared':
				$f['input_type'] = 'yesno';
				$f['edit'] = false;
				break;
			case 'filename':
				$f['show'] = false;
				break;
			case 'type':
				$f['show'] = false;
				$f['edit'] = false;
			case 'status':
				$f['input_type'] = 'selector';
				$f['sortable'] = true;
				$f['edit'] = false;
				break;
			case 'cuid':
				$f['readonly'] = true;				
			case 'uid':
				$f['input_type'] = 'UID';
				$f['edit'] = false;
				$f['show'] = false;
				break;
			case 'ctime':
				$f['show'] = false;
				$f['readonly'] = true;				
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'flags':
				$f['input_type'] = 'multicheckbox';
				$f['show'] = false;
				break;
			case 'fromurl':
				$f['edit'] = false;
			case 'reserved':
			case 'description':
				//$f['input_type'] = "ckeditorsimple";
				$f['show'] = false;
				break;
			case 'path':
				$f['sort'] = 1;
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'downloads':
			case 'hits':
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'size':
				$f['input_type'] = "SIZE";
				$f['sortable'] = true;
			case 'used':
				$f['edit'] = false;
				break;
			case 'extname':
				$f['searchable'] = false;
			case 'md5id':
			case 'fileid':			
			case 'target_id':
			case 'convert_id':
			case 'snap_id':
			case 'isdir':
			case 'mtime':
			case 'mimetype':
			case 'width':
			case 'height':
			case 'duration':
			case 'bitrate':
			case 'gid':
			case 'oid':
			case 'mid':
			case 'sid':
			case 'is_default':
			case 'model':
			case 'pid':
				$f['edit'] = false;
				$f['show'] = false;	
				break;
			case 'taxis':
				$f['input_type'] = "sort";			
				break;
			default:
				break;
		}
		
		return true;
	}
	
	protected function newID(&$params=array())
	{
		$id = parent::newID($params); 
		if (!isset($params['taxis']))
			$params['taxis'] = $id;	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);exit;
		
		return $id;
	}
	
	
	/* ============================================================================
	* Utility Helper functions
	* 
	* ===========================================================================*/
	public function is_image($fileinfo)
	{
		if ($fileinfo['type'] == FT_IMAGE)
			return true;
		
		return false;
	}
	
	public function is_video($fileinfo)
	{
		if ($fileinfo['type'] == FT_VIDEO)
			return true;
		
		return false;
	}
	
	public function is_av($fileinfo)
	{
		if ($fileinfo['type'] == FT_VIDEO || $fileinfo['type'] == FT_AUDIO)
			return true;		
		return false;		
	}
	
	public function is_tar($fileinfo)
	{
		if ($fileinfo['type'] == FT_TAR)
			return true;		
		return false;		
	}
	
	
	protected function is_playmp4($vinfo)
	{
		
		$nb_streams = $vinfo['format']['nb_streams'];
		if ($nb_streams <= 0)
			return false;
		
		$format_name = $vinfo['format']['format_name'];		
		$codec_name = $vinfo['codec_name'];
		
		//format_name
		//格式1：string(23) "mov,mp4,m4a,3gp,3g2,mj2"	
		//格式2：string(13) "matroska,webm"
		
		//codec_name
		//编码格式1： string(4) "h264"
		//编码格式2： string(4) "mpeg4"
		
		if ($codec_name != 'h264') 
			return false;
		
		if (!strstr($format_name, 'mp4')) 
			return false;
		
		//包含有字定义字段
		if (isset($vinfo['includeunknownmetakey']) && $vinfo['includeunknownmetakey'])
			return false;
		
		return true;
	}	
	
	
	public function is_dir($fileinfo)
	{
		if ($fileinfo['isdir'] == 1)
			return true;		
		return false;		
	}
	
	
	/**
	 * checkFileVideoInfo 是否需要转码（非H5 MP4格式的不能直接播放，须转码后才可在支持H5的浏览器上播放）
	 *
	 * @param mixed $fileinfo 文件信息
	 * @return mixed 需要转码: true, 否则：false
	 *
	 */
	protected function checkFileVideoInfo(&$params)
	{
		$tinfo = CFileType::ext2tinfo($params['extname']);
		if (!$tinfo)
			return false;
		if ($tinfo['type'] != FT_VIDEO && $tinfo['type'] != FT_AUDIO)
			return false;
		
		$vinfo = get_video_info($params['opath']);
		if (!$vinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Unknown video info!");
			return false;
		}
		
		//format_name
		//格式1：string(23) "mov,mp4,m4a,3gp,3g2,mj2"	
		//格式2：string(13) "matroska,webm"
		$format_name = $vinfo['format']['format_name'];
		$nb_streams =  $vinfo['format']['nb_streams'];			
		//codec_name
		//编码格式1： string(4) "h264"
		//编码格式2： string(4) "mpeg4"
		$codec_name = $vinfo['codec_name'];		
		//width
		$width = $vinfo['width'];
		$height = $vinfo['height'];
		$duration = $vinfo['duration'];
		$bit_rate = $vinfo['bit_rate'];
		$avg_frame_rate = $vinfo['avg_frame_rate'];
		
		//
		if (empty($params['description'])) {
			$desc = "{format_name:$format_name, codec:$codec_name,  width:$width, height:$height, bit_rate:$bit_rate, avg_frame_rate:$avg_frame_rate,duration:$duration}";
			$params['reserved'] = $desc;			
		}		
		$params['width'] = $width;
		$params['height'] = $height;
		$params['duration'] = $duration;
		$params['bitrate'] = $bit_rate;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $tinfo);						
		$is_need_convert = !isset($tinfo['h5v']) || !$this->is_playmp4($vinfo);		
		if ($is_need_convert) {
			//待转码	
			$params['status'] = FILE_S_CONVERT; 
		}
		
		//截图
		
		
		return true;
	}
	
	protected function ext2type($extname)
	{
		return CFileType::ext2type($extname);
	}
	
	protected function ext2mimetype($extname)
	{
		return CFileType::ext2mimetype($extname);
	}
	
	protected function ext2typeid($extname)
	{
		return CFileType::ext2typeid($extname);
	}
	
	/* ============================================================================
	 * UI functions
	 * 
	 * ===========================================================================*/
	
	protected function fetchStorageInfo(&$fileinfo)
	{
		//查询存储
		$s = Factory::GetModel('storage');
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN1");
		$si = $s->get($fileinfo['sid']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN2", $si);
		if (!empty($fileinfo['path']))		 {
			$fileinfo['opath'] = $si['mountdir'].DS.$fileinfo['path'];
			if ($fileinfo['type'] == FT_AUDIO || $fileinfo['type'] == FT_VIDEO)
				$fileinfo['playurl'] = $si['vodrooturl'].'/'.$fileinfo['path'];
			
			$fileinfo['downloadUrl'] = $si['path'].'/'.$fileinfo['path'];
		}
	}
	
	
	public function get($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		$res = parent::get($id);
		if (!$res) {
			return false;
		}
		
		$this->fetchStorageInfo($res);
		
		return $res;
	}
	
	public function getFileInfo($id, &$options = array())
	{
		$fileinfo = $this->getForView($id, $options);
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$row = $options['row'];

		$fileinfo['type'] = $row['type'];
		$fileinfo['status'] = $row['status'];
		$fileinfo['flags'] = $row['flags'];
		$fileinfo['size'] = $row['size'];
		
		$type = $fileinfo['type'];
		$status = $fileinfo['status'];
		$size = $fileinfo['size'];
		
		if ($type == FT_VIDEO && $fileinfo['snap_id'] > 0) {
		}
		
		return $fileinfo;
	}
	
	public function getFileListByAids($aids, $options=array(), &$_fileinfo=array())
	{
		$fdb = array();
		if ($aids) {
			$aidsdb = explode(',', $aids);
			$udb = array();			
			foreach ($aidsdb as $aid) {
				if (is_md5($aid)) {
					$fileinfo = $this->getFileInfoByFileIDForView($aid, $options);
				} else {
					$fileinfo = $this->getForView($aid, $options);
				}				
				$udb[] = $fileinfo;
			}
			if ($udb)
			$_fileinfo = $udb[0];
			
			foreach($udb as $key=>$v) {
				if ($v['isdir']) { //目录	
					$udb2 = $this->gets(array('pid'=>$v['id'])); //"where pid=$pid and type=1 and status=1", $options);
					foreach ($udb2 as $k2=>$v2) {
						$this->formatForView($v2, $options);
						$fdb[] = $v2;
					}
				} else {
					$fdb[] = $v;
				}
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $udb);
		if ($fdb) {
			array_sort_by_field($fdb, 'taxis', true);
		}
		return $fdb;
	}
	
	/*
	1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
	*/
	public function getFileInfoByUrl__unused($url)
	{
		$tdb = explode('/', $url);
		$nr = count($tdb);
		for ($i=$nr-1; $i>0; $i--) {
			$val = $tdb[$i];
			$pos = strpos($val, '_');	
			if ($pos !== false) { //1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
				$id = substr($val, 0, $pos);
				$fileinfo = $this->get($id);
				if ($fileinfo) {
					return $fileinfo;
				}				
			} else {// eg: /rc8/f/42/a.tar.gz
				if ($val == 'f' || $val == 'file') {
					$id = is_numeric($tdb[$i+1])?$tdb[$i+1]:$tdb[$i+2];
					
					$fileinfo = $this->get($id);
					if ($fileinfo) {
						return $fileinfo;
					} else {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
					}	
				}
			}		
		}
		return false;
	}

	public function getFileInfoByUrl($url, $opath=true)
	{
		$tdb = explode('/', $url);
		$nr = count($tdb);
		for ($i=$nr-1; $i>0; $i--) {
			$val = $tdb[$i];
			if (is_md5($val)) {
				$fileinfo = $this->getOne(array('fileid'=>$val));
				if ($fileinfo) {
					
					//$opath
					$this->fetchStorageInfo($fileinfo);
					
					return $fileinfo;
				}	
			} else {// eg: /rc8/f/42/a.tar.gz
				if ($val == 'f' || $val == 'file') {
					$id = is_numeric($tdb[$i+1])?$tdb[$i+1]:$tdb[$i+2];					
					$fileinfo = $this->get($id);
					if ($fileinfo) {
						return $fileinfo;
					} else {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
					}	
				}
			}		
		}
		return false;
	}
	
	public function getFileInfoByFileID($fileid)
	{
		$fileinfo = $this->getOne(array('fileid'=>$fileid));
		if (!$fileinfo) {			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no fileid '$fileid'!");
			return false;
		}
		return $fileinfo;
	}
	
	public function getFileInfoByFileIDForView($fileid, $options)
	{
		$fileinfo = $this->getFileInfoByFileID($fileid);
		if (!$fileinfo) {			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no fileid '$fileid'!");
			return false;
		}
		
		$this->fetchStorageInfo($fileinfo);
		$this->formatForView($fileinfo, $options);
				
		return $fileinfo;
	}
	
	
	public function getFileInfoByFileIDWithStorageInfo($fileid)
	{
		$fileinfo = $this->getFileInfoByFileID($fileid);
		if (!$fileinfo) {			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no fileid '$fileid'!");
			return false;
		}
		
		$this->fetchStorageInfo($fileinfo);
		
		return $fileinfo;
	}
	
	public function fileid2id($fileid)
	{
		$fileinfo = $this->getOne(array('fileid'=>$fileid));
		if (!$fileinfo) {			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no fileid '$fileid'!");
			return false;
		}
		return $fileinfo['id'];
	}

	public function getFileInfoByMd5Id($md5id)
	{
		$fileinfo = $this->getOne(array('md5id'=>$md5id));
		if (!$fileinfo) {			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no md5id '$md5id'!");
			return false;
		}
		return $fileinfo;
	}
	
	
	public function getImagePath($id)
	{
		$fileinfo = $this->get($id);
		
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no file of id '$id'!");
			return false;
		}
		
		if ($fileinfo['type'] != FT_IMAGE) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not img file of id '$id'!");
			return false;
		}
		
		return $fileinfo['opath'];
	}
	
	public function getPostions($pid)
	{
		$positions = array();
		
		$pdb = array();
		if (($res = $this->getParents($pid, $pdb))) {
			
			foreach ($pdb as $key => $v2) {
				$p = array('name'=>$v2['name'], 'id'=>$v2['id']);
				array_unshift($positions, $p);
			}	
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$positions='.$positions, $pdb);
		
		return $positions;
	}
	
	
	public function getPlayUrl($fileinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN getPlayUrl 1 ", $fileinfo);
		
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($fileinfo['sid']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN getPlayUrl 2 ", $storageinfo);
		
		$playurl = $storageinfo['vodrooturl'].'/'.$fileinfo['path'];
		
		if ($fileinfo['_status'] == 4 && $fileinfo['convert_id'] > 0) { //被转码完成的VIDEO
			$convert_id = $fileinfo['convert_id'];
			$convertfileinfo = $this->get($convert_id);
			if ($convertfileinfo) {
				$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
			} 
		}		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT getPlayUrl ............");
		return $playurl;		
	}
	
	public function getPlayInfo($sid, $fileinfo, $options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... ", $fileinfo);
		
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($sid);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN getPlayUrl 2 ", $storageinfo);
		
		$playurl = $storageinfo['vodrooturl'].'/'.$fileinfo['path'];
		$lanplayurl = $storageinfo['lanvodrooturl'].'/'.$fileinfo['path'];
		
		if ($fileinfo['_status'] == 4 && $fileinfo['convert_id'] > 0) { //被转码完成的VIDEO
			$convert_id = $fileinfo['convert_id'];
			$convertfileinfo = $this->get($convert_id);
			if ($convertfileinfo) {
				$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
				$lanplayurl = $storageinfo['lanvodrooturl'].'/'.$convertfileinfo['path'];
			} 
		}		
		
		$item = array();
		$item['fid'] = $fileinfo['id'];
		$item['name'] = $fileinfo['name'];
		$item['oid'] = $storageinfo['oid'];
		$item['sid'] = $sid;
		$item['sname'] =  $storageinfo['name'];
		$item['playurl'] = $playurl;
		$item['lanplayurl'] = $lanplayurl;
		
		//status
		$m2 = Factory::GetModel('file2org');
		$f2oinfo = $m2->getOne(array('fid'=>$fileinfo['id'], 'sid'=>$sid));
		if ($f2oinfo) {
			$m2->formatForView($f2oinfo, $options);
			$item['status'] = $f2oinfo['status'];
			$item['_status'] = $f2oinfo['_status'];
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ... ", $item);
		
		return $item;
	}
	
	public function formatForVideoPlay(&$row, $storageinfo, $options=array())
	{
		if ( $row['status'] == FILE_S_LINK && !empty($row['fromurl'])) {
			$playurl = $row['fromurl'];
		} else {
			/*$playurl = $storageinfo['vodrooturl'].'/'.$row['path'];
			if ($row['status'] == FILE_S_CONVERTED && $row['convert_id'] > 0) { //被转码完成的VIDEO
				$convert_id = $row['convert_id'];
				$convertfileinfo = $this->get($convert_id);
				if ($convertfileinfo) {
					$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
				} 
			}*/
			//fixed: 以上处理忽略 
			$playurl = $row['url'];
		}
		
		$row['playurl'] = $playurl;	
		
		$row['_extinfo'] = " <a href='$playurl' data-url='$playurl' class='videobox' data-id='".$row['id']."' title='test video play'><i class='fa fa-film'></i></a>";
		$row['_name'] = "<a href='$row[url]' class='videobox' data-url='$playurl' data-id='$row[id]'>$row[name]</a>";
		
		
		if (!empty($row['snap_id'])) {
			$snapinfo = $this->get($row['snap_id']);
			if ($snapinfo) {
				//snapImageUrl
				$row['_snapImageUrl'] = $options['_webroot'].'/f/'.$snapinfo['fileid'].'/'.urlencode($snapinfo['filename']);
			}
		}
	}
	
	
	
	public function formatForAudioPlay(&$row, $storageinfo, $options=array())
	{
		$playurl = $storageinfo['vodrooturl'].'/'.$row['path'];
		
		$row['playurl'] = $playurl;		
		
		$row['_extinfo'] = " <a href='$playurl' data-url='$playurl' class='audiobox' data-id='".$row['id']."'> <i class='fa fa-music'></i> </a>";
	}
	
	public function formatForImageUrl(&$row, $storageinfo, $options=array())
	{
		if ( $row['status'] == FILE_S_LINK && !empty($row['fromurl'])) {
			$imgurl = $row['fromurl'];
		} else {
			$imgurl = $row['url'];
		}
		//
		$row['_extinfo'] = "<a href='$imgurl' class='gallery-img' data-url='$imgurl' data-id='$imgurl' > <i class='fa fa-image'></i> </a>";
		$row['_name'] = "<a href='$imgurl' class='gallery-img' data-url='$imgurl' data-id='$row[id]'>$row[name]</a>";
		
		/*if ($row['snap_id'] > 0) { //是从视频中截取来的图			
			$snapinfo = $this->get($row['snap_id']);
			if ($snapinfo) {				
				$playurl = $storageinfo['vodrooturl'].'/'.$snapinfo['path'];
				if ($snapinfo['status'] == FILE_S_CONVERTED && $snapinfo['convert_id'] > 0) { //被转码完成的VIDEO
					$convert_id = $snapinfo['convert_id'];
					$convertfileinfo = $this->get($convert_id);
					if ($convertfileinfo) {
						$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
					} 
				}
				$row['playurl'] = $playurl;				
			}
		}*/
	}
	
	protected function getFileBaseName($options)
	{
		return 'f';
	}	
	
	protected function getFileBaseUri($row, $storageinfo, $options)
	{
		if ($row['status'] == FILE_S_LINK && !empty($row['fromurl'])) {
			$pos = strpos($row['fromurl'], $row['fileid']);			
			if ($pos !== false) {
				$base = substr($row['fromurl'], 0, $pos-1); //不带 '/'
				return $base; 
			} else {
				$urlPrefix = $options['base'];
			}
		} else { 
			if ($row['status'] == FILE_S_SHARED) {
				$urlPrefix = $options['base'];
			} else if ($storageinfo['type'] == ST_NODE) {
				$urlPrefix = $storageinfo['web_prefix'];	
			} else {
				$urlPrefix = $options['base'];
			}
		}
		$base = $urlPrefix.'/'.$this->getFileBaseName($options);
		
		return $base;
	}
	
	
	
	
	protected function getPreviewFileName($fileinfo)
	{
		return $fileinfo['fileid'].'.jpg'; //jpg格图片比PNG小很多？？
	}
	
	
	protected function getStorageInfo($sid)
	{
		$m = Factory::GetModel('storage');
		return $m->get($sid);
	}
	
	
	protected function getMyStorageInfo()
	{
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];
		
		$m = Factory::GetModel('storage');
		$si = $m->getUserStorageInfo($uid);
		
		return $si;
	}
	
	
		
	
	protected function getStorageMountDir($sid)
	{
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($sid);
		if (!$storageinfo)
			return false;
		
		return $storageinfo['mountdir'];
	}
	
	
	protected function getFileStorageInfo($fileinfo)
	{
		$sid = $fileinfo['sid'];
		$storageinfo = $this->getStorageInfo($sid);
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no storage sid '$sid' info!");
			return false;
		}
		
		//basepath
		//basedir
		$baepath = s_dirname($fileinfo['path']);
		$storageinfo['basepath'] = $baepath;
		$storageinfo['basedir'] = $storageinfo['mountdir'].DS.$baepath;
		
		return $storageinfo;
	}
	
	
	public function formatForViewUrl(&$row, $options)
	{
		$storageinfo = $this->getStorageInfo($row['sid']);
		
		/*if ($storageinfo['type'] == ST_NODE) {
			$urlPrefix = $storageinfo['web_prefix'];	
			
			$path = $row['path'];
			
			$row['url'] = $urlPrefix.'/'.$path;			
			//preview
			$filename = $this->getPreviewFileName($row);
			
			$base = $urlPrefix.'/f';
			
			
			$row['previewUrl'] = $base.'/preview/'.$filename;
			$row['lpreviewUrl'] = $base.'/lpreview/'.$filename;
			$row['spreviewUrl'] = $base.'/spreview/'.$filename;
			$row['downloadUrl'] = $base.'/download/'.$filename;
			
			//download
			$row['downloadUrl'] = $row['url'];
					
		} else {*/
			
			$filename = urlencode($row['filename']);
			
			//$fileinfo['fileid']
			$fname = $row['fileid'].'/'.$filename;
			
			$base = $this->getFileBaseUri($row, $storageinfo, $options);
			
			//url
			$row['url'] = $base.'/'.$fname;	
					
			//preview
			$row['previewUrl'] = $base.'/preview/'.$fname;
			$row['lpreviewUrl'] = $base.'/lpreview/'.$fname;
			$row['spreviewUrl'] = $base.'/spreview/'.$fname;
			//download
			$row['downloadUrl'] = $base.'/download/'.$fname;
		//}
		
		//default extinfo
		$row['_extinfo'] = "";
		
		switch($row['type']) {
			case FT_VIDEO:
				$this->formatForVideoPlay($row, $storageinfo, $options);
				break;
			case FT_AUDIO:
				$this->formatForAudioPlay($row, $storageinfo, $options);
				break;
			case FT_IMAGE:
				$this->formatForImageUrl($row, $storageinfo, $options);
				break;
			default:
				break;
		}
		
		
		
		return true;
	}
	
	protected function formatForSummary(&$row)
	{
		$keydb = array('type', 'size', 'description');
		$fields = $this->getFields();
		$_summary = '';
		foreach ($fields as $key=>$v) {
			if (in_array($key, $keydb)) {
				$val = (isset($row['_'.$key])?$row['_'.$key]:$row[$key]);
				if ($val) {
					$_summary .= $v['title'].':'.(isset($row['_'.$key])?$row['_'.$key]:$row[$key]).' / ';
				}
			}
		}
		$row['_summary'] = rtrim($_summary, '/ ');
	}
	
	
	
	public function formatForView(&$row, &$options = array())
	{
		$type = $row['type'];
		$status = $row['status'];
		$size = $row['size'];
		
		parent::formatForView($row, $options);
		
		//title
		$row['title'] = $row['name'];
		
		//format name
		$statusColor = $status;
		switch($status) {
			case FILE_S_SYMLINK://symlink
				$statusColor = FILE_S_LINK;		
				break;
			case FILE_S_DOWNLOAD://download
				$statusColor = 2;
				break;
			case FILE_S_DOWNLOADING://downloading	
				$statusColor = 5;			
			case 7:
			case 8:
			default:			
				break;
		}
		
		//status
		$row['_status'] = $this->formatLabelColorForView($statusColor, $row['_status']);
		//shared
		$row['_shared'] = $this->formatLabelColorForView($row['shared']>0?4:0, $row['_shared']);
		
		$this->formatForViewUrl($row, $options);
		
		//dir and icon
		if ($row['isdir']) {
			$row['icon'] = "ft-dir";
			$class = "isdir";
		} else {
			$row['icon'] = "ft-$row[extname]";
			$class = "file";
		}
		$row['_name'] = "<a href='$row[url]' class='$class' data-id='$row[id]'>$row[name]</a>";
			
		$row['summary'] = $row['description'];
		
		//mimetype
		$row['mimetypename'] = $this->ext2mimetype($row['extname']);
		
		//for listview
		$row['__ctype'] = $type;
		
		//_summary
		$this->formatForSummary($row);
		
		
		return true;
	}
	
	public function getFileInfoForViewByUrl($url, &$options=array())
	{
		$fileinfo = $this->getFileInfoByUrl($url);
		if ($fileinfo) 
			$this->formatForView($fileinfo, $options);
		
		return $fileinfo;
	}
	
	protected function getActions($row=array(), &$options=array())
	{
		$id = $row['id'];
		$actions = parent::getActions($row, $options);
		
		
		//下载
		$actions['download'] = array(
				'name'=>'download',
				'icon'=>'fa fa-download',
				'title'=>'下载',
				'class'=>'btn-primary',
				'action'=>'alink',
				'enable'=>true,
				);
		
		//重命名
		$actions['rename'] = array(
				'name'=>'rename',
				'icon'=>'fa fa-edit',
				'title'=>'重命名',
				'class'=>'btn-primary',
				'action'=>'edit',
				'enable'=>true,
				'detail'=>false,
				);
		
		//移动到
		$actions['moveto'] = array(
				'name'=>'moveto',
				'icon'=>'fa fa-cut',
				'title'=>'移动到',
				'class'=>'btn-primary',
				'action'=>'moveto',
				'enable'=>true,
				'detail'=>false,
				);
				
		//复制到
		$actions['copyto'] = array(
			'name'=>'copyto',
			'icon'=>'fa fa-copy',
			'title'=>'复制到',
			'class'=>'btn-primary',
			'action'=>'copyto',
			'enable'=>true,
			'detail'=>false,
			);
			
		//发布到
		$actions['pubto'] = array(
				'name'=>'pubto',
				'icon'=>'fa fa-share',
				'title'=>'发布到',
				'class'=>'btn-primary',
				'action'=>'tmbox',
				'enable'=>true,
				);
		//共享
		$actions['share'] = array(
				'name'=>'share',
				'icon'=>'fa fa-share-alt',
				'title'=>'共享',
				'class'=>'btn-primary',
				'action'=>'button',
				'enable'=>true,
				);
		
		//下载
		if ($row['status'] == 	FILE_S_LINK || $row['status'] == 	FILE_S_DOWNLOAD) {	
			$actions['downloadshare'] = array(
					'name'=>'downloadshare',
					'icon'=>'fa fa-cloud-download',
					'title'=>'下载共享',
					'description'=>'下载到本地',
					'class'=>'btn-primary',
					'action'=>'button',
					'enable'=>true,
					'showbutton'=>true,
					);
		} else if ($row['status'] == FILE_S_DOWNLOADING) {
				$actions['checkDownloading'] = array(
						'name'=>'checkDownloading',
						'icon'=>'fa fa-refresh',
						'title'=>'刷新',
						'description'=>'刷新',
						'class'=>'btn-primary',
						'action'=>'button',
						'enable'=>true,
						'showbutton'=>true,
						);			
		}
		
		return $actions;
	}
	
	public function checkDownloading($id)
	{
		$fileinfo = $this->get($id);
		$res = $this->checkDownloadingOne($fileinfo);
		
		return true;
	}
	
	protected function checkParamsForSearch(&$params, &$options=array())
	{
		if (isset($params['type']) && $params['type'] < 0) {
			unset($params['type']);
		}
		return parent::checkParamsForSearch($params, $options);
	}
	
	/**
	 * This is method selectForView_unused
	 * unused
	 * @param mixed $params This is a description
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function selectForView_unused(&$params=array(), &$options=array())
	{
		$data = parent::selectForView($params, $params);	
		foreach ($params['rows'] as $key=>&$v) {
			$this->formatForView($v, $options);
		}
		
		return $data;
	}
	
	protected function hasSubDir($id)
	{
		$res = $this->getOne(array('pid'=>$id, 'isdir'=>1));
		return !!$res;
	}
	
	public function getSubDir($pid, $params=array(), &$options=array())
	{
		$params['pid'] = $pid;		
		$params['isdir'] = 1;		
		
		$rows = $this->select($params, $options);
		foreach ($rows as $key=>&$v) {
			$v['hasChildren'] = $this->hasSubDir($v['id']);
		}
		
		return $rows;
	}
	
	
	
	public function hasChildren($id)
	{
		
		$cdb = $this->getOne(array('pid'=>$id));
		return !!$cdb;
	}
	
	protected function deleteFile($fileinfo)
	{
		if ($fileinfo['status'] == FILE_S_SYMLINK) {
			$target_id = $fileinfo['target_id'];
			$this->dec($target_id, 'used');
			return true;			
		}
		
		if (empty($fileinfo['opath']))
			return true;
		
		if (file_exists($fileinfo['opath'])) {
			$res = @unlink($fileinfo['opath']);
			if (!$res) 
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call unlink failed!opath='".$fileinfo['opath']."'");
		}
				
		return true;
	}	
	
	protected function deletePreview($fileinfo)
	{
		//s_rmdir($this->_previewdir);	
		
		//del: <id>_*.*
		$mntdir = $this->getStorageMountDir($fileinfo['sid']);
		if (!$mntdir)
			return false;
		
		//preview
		$filename = $this->getPreviewFileName($fileinfo);
		
		@unlink($mntdir.DS.'preview'.DS.$filename);	
		@unlink($mntdir.DS.'spreview'.DS.$filename);	
		@unlink($mntdir.DS.'lpreview'.DS.$filename);	
		
	}
	
	
	protected function deleteUpdateStorageSpace($fileinfo)
	{
		if ($fileinfo['isdir'])
			return false;
		
		$delta = - $fileinfo['size'];
		if ($delta >= 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid delta! delta=$delta");	
			return false;
		}
		
		//存储id
		$sid = $fileinfo['sid'];		
		//上传者
		$uid = $fileinfo['uid'];
				
		$m = Factory::GetModel('storage');
		$m->updateUsedBy($sid, $uid);
		
		
		return true;
	}
	
	
	public function del($id, &$options=array())
	{
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'");
			return false;
		}
		
		//检查child
		if ($this->hasChildren($id)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "file has children of id '$id'!");
			return false;	
		}
		
		//查询引用
		if ($fileinfo['used'] > 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "file '$id' is used({$fileinfo['used']})");
			$this->dec($id, 'used');			
			return true;	
		}
		
		$this->deletePreview($fileinfo);						
		$this->deleteFile($fileinfo);
		
		$res = parent::del($id, $options);		
		if ($res) {
			//$this->deleteUpdateStorageSpace($fileinfo);
		}
		
		$m = Factory::GetModel('file2model');
		$m->trigger('delete', $res);
		
		
		return $res;
	}
	
	public function delByUrl($url)
	{
		$res = false;
		$fileinfo = $this->getFileInfoByUrl($url);
		if ($fileinfo) {
			$res = $this->del($fileinfo['id']);
		}		
		return $res;
	}
	
	/* ============================================================================
	 * Preview functions
	 * 
	 * ===========================================================================*/
	
	
	protected function getVideoPreview($src, $dst, $width, $height, &$mimetype)
	{
		//ffmpeg
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in getVideoPreview");	
		
		$infile = $src;
		
		//$dst = $dst.".jpg";		
		$mimetype = "image/jpeg";
		
		if (file_exists($dst)) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: dst $dst exists!");
			return $dst;
		}
		
		//ffmpeg -i input.mp4 -ss 00:00:20 -t 1 -r 1 -q:v 2 -f image2 pic-%03d.jpeg
		//mp4 : 00:00:02
		$bindir = '';// RPATH_SHELL;
		$ffmpegcmdline = "ffmpeg -v quiet -i \"$infile\" -y -f image2 -ss 1 -t 1 -s $width".'x'."$height $dst";
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ffmpegcmdline);
		
		$res = system($ffmpegcmdline, $return_value);
		if ($res === false || !file_exists($dst)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call system('$ffmpegcmdline') failed!", $res);
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res, $return_value);		
		return $dst;
	}
	
	
	protected function genOtherFilePreview($fileinfo, $dst, $width=128, $height=128)
	{
		$text = $fileinfo['extname'];
		
		!$width && $width=128;
		!$height && $height=128;
		
		
		$im = imagecreatetruecolor($width, $height);
		if (!$im){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecreatetruecolor failed!w=".$width.',h='.$height);
			return false;
		}
		
		$bgcolor = imagecolorallocatealpha($im, 240, 240, 240, 100); // (PHP 4 >= 4.3.2, PHP 5)
		if (!$bgcolor){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecolorallocatealpha failed!");
			return false;
		}
		
		//填充
		imagefill($im, 0, 0, $bgcolor);
		
		
		//src
		$src = RPATH_STATIC.DS."img".DS."filetypes".DS.$fileinfo['extname'].".gif";
		if (file_exists($src) && ($szs = @getimagesize($src))) { //源文件是否存在
			
			list($orig_width, $orig_height, $bigType) = $szs;
			$mimetype = $szs['mime'];
			
			switch ($bigType) {
				case 1: 
					$sim = @imagecreatefromgif($src);
					break;	 
				case 2: 
					$sim = @imagecreatefromjpeg($src); 
					break;	 
				case 3: 
					$sim = @imagecreatefrompng($src); 
					break;
				default:
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Unkown cropping image type '$bigType'!");
					break;
			}
			
			if ($sim) {
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $x, $y, $w, $h, $target_w, $target_h);
				$dst_x = 0;
				$dst_y = 0;
				$src_x = 0;
				$src_y = 0;
				$dst_w = $width;
				$dst_h = $height;
				
				$src_w = $orig_width;
				$src_h = $orig_height;
				
				$res = imagecopyresampled($im, $sim, $dst_x, $dst_y, $src_x, $src_y,
						$dst_w, $dst_h, $src_w, $src_h);				
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call imagecopyresampled failed!");
				}
			}
		}	
		
		$nr = strlen($text);
		$size = 14;
		$angle = 0;
		$x = 0;
		$y = 0;
		
		
		
		$textcolor = imagecolorallocate($im, 102, 102, 102);
		
		// imagestring ( resource $image , int $font , int $x , int $y , string $s , int $col ) : bool
		
		$font = RPATH_SUPPORTS.DS."fonts".DS."song.ttf"; 
		if (!file_exists($font)) 
			$font = RPATH_SUPPORTS.DS."fonts".DS."CourierNew.ttf"; 
			
		$szdb = array(14, 12, 10, 8, 7);
		foreach ($szdb as $key=>$v) {
			$size = $v;
			
			$box   = imagettfbbox($size, $angle, $font, $text);
			if( !$box )
				return false;
			$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
			$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
			$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
			$max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
			
			$t_width  = ( $max_x - $min_x );
			$t_height = ( $max_y - $min_y );
			
			//rlog('size='.$size.',$t_width='.$t_width);
			
			if ($t_width > $width)
				continue;
			
			$x = ceil(($width - $t_width)/2);
			$y = ceil(($height)/2);
			
			break;
		}
		
		
		$res = imagettftext($im, $size, $angle, $x, $y, $textcolor, $font, $text);
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call imagettftext failed!");
			return false;
		}		
		
		$res = imagejpeg($im, $dst);
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call ImageColorAllocateAlpha failed!");
			return false;
		}
		imagedestroy($im);
		
		return $dst;
	}
	
	protected function getOtherFilePreview($fileinfo, $src, $dst, $width, $height, &$mimetype)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in getOtherFilePreview");
		
		$extname = s_fileext($src);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $extname);
		if ($fileinfo['type'] == FT_VIDEO) {
			$res =  $this->getVideoPreview($src, $dst, $width, $height, $mimetype);
			if ($res)
				return $res;
			else //failed
				return RPATH_STATIC.DS."img".DS."no.png";
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'isdir='.$fileinfo['isdir']);
		
		if ($fileinfo['isdir']) {
			$mimetype = "image/png";
			$dst = RPATH_STATIC.DS."img".DS."dir.png";
		}  else { // 生成一下扩展名预览
			$mimetype = "image/png";
			//$dst = RPATH_STATIC.DS."img".DS."nopic.png";
			$dst = $this->genOtherFilePreview($fileinfo, $dst, $width, $height);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT getOtherFilePreview");
		return $dst;
	}	
	
	/**
	 * 缩放图片
	 *
	 * 
	 * @param mixed $src This is a description
	 * @param mixed $dst This is a description
	 * @param mixed $width This is a description
	 * @param mixed $height This is a description
	 * @param mixed $params This is a description
	 * @param mixed $overwrite This is a description
	 * @return mixed This is the return value description
	 *
	 */

	public function resizeImage($src, $dst, $width, $height, &$params=array(), $overwrite = false, $adddstextname=false)
	{ 
		$params = array();
		
		$szs = @getimagesize($src);
		if (!$szs) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call getimagesize filed! szs=$szs, src='$src'");
			return false;
		}			
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__,$szs, $src);
		list($orig_width, $orig_height, $bigType) = $szs;
		//$mimetype = $szs['mime'];
		
		/*$extname = s_extname($dst);		
		
		switch ($bigType) {
			case 1: 
				$extname = "gif";
				break;	 
			case 2: 
				$extname = "jpg";
				break;	 
			case 3: 
				$extname = "png";
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown bigtype '$bigType'!");
				return false;
		}*/
		$extname = 'jpg';
		if ($adddstextname)
			$dst .= '.'.$extname;
		
		$orig_width = intval($orig_width);
		$orig_height = intval($orig_height);
		
		$params['orig_width'] = $orig_width;
		$params['orig_height'] = $orig_height;
		$params['dst'] = $dst;
		$params['extname'] = $extname;
		//mimetype
		$params['mimetype'] = 'image/'.$extname;
		
		if (file_exists($dst) && !$overwrite) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,__FUNCTION__, "WARNING: dst '$dst' exists!");
			return true;
		}
		
		if ($width >= $orig_width && $height >= $orig_height && !$overwrite) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: Not need create preview for '$dst' width=$width, orig_width=$orig_width, height=$height >= orig_height=$orig_height");
			$params['dst'] = $src;
			return true;
		}
		
		$new_width = intval($orig_width * $height/$orig_height); 
		$new_height = intval($orig_height * $width / $orig_width); 		
		if ($width > $new_width)
			$width = $new_width;
		if ($height > $new_height)
			$height = $new_height;
		
		
		// Select the format of the new image
		switch ( $bigType )
		{
			case 1: 
				$im = @imagecreatefromgif($src);
				break;			
			case 2: 
				$im = @imagecreatefromjpeg($src); 
				break;
			case 3:
				$im = @imagecreatefrompng($src); 
				break;
			default: 
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown bigType '$bigType'!");
				return false; 
		}
		if (!$im) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "unknown image '$src'!");
			return false;
		}
		
		$output = imagecreatetruecolor($width, $height);		
		@imagecopyresampled($output, $im, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
		
		/*switch ($bigType) {
			case 1: 
				$res = imagegif($output, $dst);
				break;	 
			case 2: 
				$res = imagejpeg($output, $dst);
				break;	 
			case 3: 
				$res = imagepng($output, $dst);
				break;
		}		*/
		
		$res = imagejpeg($output, $dst);
		@imagedestroy($output);
		
		
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "resize Image failed!res=$res,dst=$dst");
		}		
		return $res;
	}
	
	public function getFilePreview($fileinfo, $src, $dst, $width, $height, &$mimetype)
	{ 
		$previewinfo = array();
		
		if ($fileinfo['type'] == FT_IMAGE) {//图片类
			$res = $this->resizeImage($src, $dst, $width, $height, $previewinfo);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call resizeImage filed!src=$src");
				return false;
			}
			$mimetype = $previewinfo['mimetype'];
		} else {
						
			$dst = $this->getOtherFilePreview($fileinfo,$src, $dst, $width, $height, $mimetype);
			
			$previewinfo['orig_width'] = 0;
			$previewinfo['orig_height'] = 0;
			$previewinfo['dst'] = $dst;
		}
		return $previewinfo;
	}
	
	protected function getPreview($fileinfo, $width, $height, &$mimetype, $previewName='preview')
	{ 
		$fid = $fileinfo['id'];
		$sinfo = $this->getStorageInfo($fileinfo['sid']);
		if (!$sinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage of id '$sid'");
			return false;
		}
		
		$mountdir = $sinfo['mountdir'];
		$dstdir = $mountdir.DS.$previewName;
		if (!is_dir($dstdir))
			s_mkdir($dstdir);
			
		$name = $this->getPreviewFileName($fileinfo);		
		$dst = $dstdir.DS.$name;
		
		$previewinfo = $this->getFilePreview($fileinfo, $fileinfo['opath'], $dst, $width, $height, $mimetype);
		
		$orig_width = intval($previewinfo['orig_width']);
		$orig_height = intval($previewinfo['orig_height']);
		if (!$fileinfo['width'] || !$fileinfo['height']) {
			$_params = array();
			$_params['id'] = $fid;
			$_params['width'] = $orig_width;
			$_params['height'] = $orig_height;
			
			$this->update($_params);			
		}
		
		return $previewinfo['dst'];
	}
	
	public function previewPath($id, $width=72, $height=72, &$mimetype='', $previewName='preview')
	{
		if ($width <= 8) 
			$width = 8;
		if ($height <= 8) 
			$height = 8;
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no file id '$id'");
			return false;
		}
		
		
		$previewFile = $this->getPreview($fileinfo, $width, $height, $mimetype, $previewName);
		if (!$mimetype)
			$mimetype = $this->ext2mimetype($fileinfo['extname']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $previewFile);		
		return $previewFile;
	}
	
	
	protected function createFilePreview($id)
	{
		/*
		preview:640*360
		spreview:128*72
		lpreview: 1920*1080*/
		$this->previewPath($id, 640, 360, $mimetype);
		$this->previewPath($id, 128, 72, $mimetype, 'spreview');
		$this->previewPath($id, 1920, 1080, $mimetype, 'lpreview');
	}
	
	public function preview($id, $width=72, $height=72, $previewName='preview')
	{
		//生成preview
		$this->createFilePreview($id);
		
		$previewFile = $this->previewPath($id, $width, $height, $mimetype, $previewName);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $previewFile);
		
		if ($previewFile) {
			header("Content-Type: $mimetype");
			if (!file_exists($previewFile)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no previewFile=$previewFile=".$previewFile);
				exit;
			}
			
			$res = readfile($previewFile);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call readfile failed!$previewFile=".$previewFile);
			}
		} 
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		exit;
	}
	
	
	public function getReceiptInfo($id)
	{
		$finfo = $this->get($id);
		if (!$finfo) {
			return false;
		}
		if ($finfo['extname'] != 'pdf') {
			return false;
		}
		
		$m = Factory::GetReceipt();
		$rinfo = $m->getReceiptInfo($finfo['opath']);
		
		return $rinfo;
	}	
	
	/* ============================================================================
	 * DIR functions
	 * 
	 * ===========================================================================*/
	
	public function newDirectory($params, $options)
	{
		if (!$params)
			return false;
		
		$name = $params['name'];
		$pid = isset($params['pid'])?intval($params['pid']):0;
		if ($pid < 0)
			$pid = 0;
			
		if ($pid > 0) {
			$pinfo = $this->get($pid);	
			if (!$pinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid pid '$pid'");
				return false;
			}
		}
		
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];
		$oid = $userinfo['oid'];
		
		//重启名
		$fileid = $this->genFileIDName($pid, $name);		
						
		//find
		$_params = array('pid'=>$pid, 'name'=>$name, 'isdir'=>1, 'cid'=>$uid);
		$res = $this->getOne($_params);
		if ($res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "directory '$name' exists!");
			return false;
		}
		
		$params['name'] = $name;
		$params['filename'] = $name;
		$params['fileid'] = $fileid;
		$params['isdir'] = 1;
		$params['status'] = 1;
		$params['oid'] = $oid;
		$params['pid'] = $pid;
		
		$res = $this->set($params);
		
		return $res;
	}
	
	
	public function newTxtFile($params, $options)
	{
		if (!$params)
			return false;
		
		$name = $params['name'];
		$pid = intval($params['pid']);
		if ($pid < 0)
			$pid = 0;
			
		if ($pid > 0) {
			$pinfo = $this->get($pid);	
			if (!$pinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid pid '$pid'");
				return false;
			}
		}
		
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];
		$oid = $userinfo['oid'];
		
		//存储
		$m = Factory::GetModel('storage');
		$si = $m->getUserStorageInfo($uid);
		$sid = $si['id'];
		
		//target dir
		$tdir = $si['basedir'];
		if (!$tdir) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no storage for user '$uid'!");
			return false;
		}	
		
		$tdir = str_replace(DS, '/', $tdir);
		if (!is_dir($tdir)) {
			s_mkdir($tdir);
			if (!is_dir($tdir)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no dir '$tdir'!");
				return false;
			}
		}
		$extname = 'txt';
		$name .= '.'.$extname;
		
		$params = array('name'=>$name,'pid'=>$pid, 'size'=>0);
		$res = $this->initTmpFileInfo($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call initTmpFileInfo failed!");
			return false;
		}	
		
		//filename
		$tfileinfo = $params['fileinfo'];
		$fileid = $tfileinfo['fileid'];
		$id = $tfileinfo['id'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$name='.$name);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$filename='.$filename);		
		
		$fname = $id.'_'.$fileid.'.'.$extname;
		$dst = $tdir.DS.$fname;
		$data = "提醒：此文件为点击[创建文本文件]菜单项创建的新文件!!";
		file_put_contents($dst,$data);
		
		$size = s_filesize($dst);
		
		//$path
		$basepath = $si['basepath'];		
		$path = $basepath.'/'.$fname;		
		
				
		$params = array();
		$params['id'] = $id;
		$params['extname'] = $extname;
		$params['type'] = $this->ext2type($extname);
		$params['path'] = $path;
		$params['fileid'] = $fileid;
		$params['isdir'] = 0;
		$params['status'] = 1;
		$params['oid'] = $oid;
		$params['sid'] = $sid;
		$params['size'] = $size;
		
		
		$res = $this->update($params);
		
		return $res;
	}
	
	
	//createDirectory
	public function createDirectory($path, &$options=array())
	{
		if (!$path) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no path '$path'!", $options);
			return false;
		}
		
		$params = array();		
		$userinfo = get_userinfo();
		
		//所有者id
		$uid = $userinfo['id'];		
		$params = $this->getPathInfo($uid, $path, $options);		
		if (!$params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid uid '$uid' or path '$path'!");
			return false;
		}
		
		//加载文件信息	
		
		
		if ($params['exists']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "path '$path' exists!");
			return false;
		}
		
		
		$params['isdir'] = 1;		
		$res = $this->newDirectory($params, $options);
		
		
		//rlog($params);
		
		return $res;
	}
	
	
	/* ============================================================================
	 * Upload functions
	 * 
	 * ===========================================================================*/
	
	protected function genFileIDName($pid, &$filename, $suffix='')
	{
		$i = 1;
		
		$name = $filename;
		s_extname2($name, $extname);
		while ($this->getOne(array('pid'=>$pid, 'filename'=>$filename))) { //文件已经存
			$newname = $name.'-'.$i++;
			$_suffix = $suffix;
			if ($extname)
				$_suffix .= '.'.$extname;
					
			if ($extname) {
				$filename = $newname.$_suffix;
			} else {
				$filename = $newname.$_suffix;
			}
		}
		
		$fileid = $this->newUUID($pid.'_'.$filename);		
		
		return $fileid;
	}
	
	protected function getTmpFileInfo($params)
	{
		$params['name'] = $params['filename'];
		$params['ctime'] = time();
		$res = $this->set($params);
		if ($res)
			return $params;
		else
			return false;
	}
	
	protected function delTmpFileInfo($id)
	{
		return $this->del($id);
	}
	
	
	protected function initTmpFileInfo(&$params)
	{
		//已经存在
		if (!empty($params['fileinfo']))
			return true;
		
		$filename = $params['name'];
		$pid = $params['pid'];
		
		//检查上传
		if (isset($params['range']) && isset($params['range']['total_length'])) { //分片上传
			//检查文件是否存在,
			//$params['lsize'] = $params['range']['total_length'];
			$params['appendupload'] = true;
			$fileinfo = $this->getOne(array('pid'=>$pid, 'filename'=>$filename));
			if ($fileinfo) {
				$params['fileinfo']= $fileinfo;
				return true;
			} else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no filename '$filename'!", $params);
			}				
		} /*else {//整个文件
			$params['lsize'] = isset($params['size'])?$params['size']:$params['content_length'];
		}*/
			
		$fileid = $this->genFileIDName($pid, $filename);		
		//tmpfileinfo
		$fileinfo = $this->getTmpFileInfo(array('pid'=>$pid, 'filename'=>$filename, 'fileid'=>$fileid));
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!");
			return false;
		}	
		
		$fileinfo['fileid'] = $fileid;
		$params['fileinfo']= $fileinfo;
		
								
		return true;
	}
	
	
	/**
	 * This is method parseHttpContentDisposition
	 * 
	 * eg: attachment; filename="四季花公园的樱花.mp4"
	 * 
	 * @param mixed $val This is a description
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 * 
	 */
	protected function parseHttpContentDisposition($val, &$params)
	{
		$tdb = explode(';', $val);
		foreach ($tdb as $key=>$v) {
			$pos = strpos($v, '=');
			if ($pos !== false) {
				list($name, $v2) = explode('=', $v);					
				$name = trim($name);
			} else {
				$name = trim($v);
				$v2 = '';
				//eg: 'attachment; filename="20260422.mp4"'
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no found VAL for '$val'!");
			}
					
			switch($name) {
				case 'filename': // recv HTTP_CONTENT_DISPOSITION: attachment; filename="%E9%9D%92%E8%8A%B1%E7%93%B7.mp4"
					$filename = urldecode(trim($v2,"'\""));
					$params['name'] = $filename;
					break;
				default:
					break;
			}
		}
	}
	
	protected function checkUploadFileType($extname)
	{
		return !CFileType::isCode($extname);
	}
	
	
	protected function initUploadParams(&$params=array(), &$options=array())
	{
		//check HTTP HEADER
		
		// default content type if none given
		$params["content_type"] = "application/octet-stream";
		
		/* RFC 2616 2.6 says: "The recipient of the entity MUST NOT 
		 ignore any Content-* (e.g. Content-Range) headers that it 
		 does not understand or implement and MUST return a 501 
		 (Not Implemented) response in such cases."
		*/ 
		foreach ($_SERVER as $key => $val) {
			switch ($key) {
				case 'HTTP_CONTENT_ENCODING': // RFC 2616 14.11
					// TODO support this if ext/zlib filters are available
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__,__FUNCTION__, "WARNING: The service does not support '$val' content encoding");
					break;
				
				case 'HTTP_CONTENT_LANGUAGE': // RFC 2616 14.12
					// we assume it is not critical if this one is ignored
					// in the actual PUT implementation ...
					$params["content_language"] = $val;
					break;
				
				case 'CONTENT_LENGTH':
				case 'HTTP_CONTENT_LENGTH':
					// defined on IIS and has the same value as CONTENT_LENGTH
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,__FUNCTION__,"CONTENT_LENGTH key=$key,val=$val");
					$params['need_size'] = $params['content_length'] = $val; //检查可用空间时使用
					break;
				
				case 'HTTP_CONTENT_LOCATION': // RFC 2616 14.14
					/* The meaning of the Content-Location header in PUT 
					 or POST requests is undefined; servers are free 
					 to ignore it in those cases. */
					break;
				
				case 'HTTP_CONTENT_RANGE':    // RFC 2616 14.16
					// single byte range requests are supported
					// the header format is also specified in RFC 2616 14.16
					// TODO we have to ensure that implementations support this or send 501 instead
					if (!preg_match('@bytes\s+(\d+)-(\d+)/((\d+)|\*)@', $val, $matches)) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does only support single byte ranges");
						return false;
					}
					
					$range = array("start" => $matches[1], "end" => $matches[2]);
					if (is_numeric($matches[3])) {
						$range["total_length"] = $matches[3];						
					}					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $range);					
					$params["range"] = $range;
					
					break;
				
				case 'CONTENT_TYPE':
				case 'HTTP_CONTENT_TYPE':
					// defined on IIS and has the same value as CONTENT_TYPE
					// for now we do not support any sort of multipart requests
					if (!strncmp($val, "multipart/", 10)) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"WARNING: recv mulipart requests");
						//return false;
					}
					$params["content_type"] = $val;
					
					break;
				
				case 'HTTP_CONTENT_MD5':      // RFC 2616 14.15
					// TODO: maybe we can just pretend here?
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"WARNING: recv content MD5 checksum verification"); 
					break;
				case 'HTTP_CONTENT_DISPOSITION'://HTTP_CONTENT_DISPOSITION
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"recv HTTP_CONTENT_DISPOSITION", $val);
					//attachment; filename="四季花公园的樱花.mp4"
					$this->parseHttpContentDisposition($val, $params); 
					break;
				case 'HTTP_OVERWRITE':
					/*
					[HTTP_OVERWRITE] => T
					[HTTP_TRANSLATE] => f
					*/
					$params['overwrite'] = ($val == 'T')?true:false;
					break;						
				default: 
					// any other unknown Content-* headers
					break;
			}
		}
		
		//lsize
		if (isset($range["total_length"])) {
			$params['lsize'] = $range["total_length"];
		} else {
			$params['lsize'] = isset($params['size'])?$params['size']:$params['content_length'];
		}
		
		$userinfo = get_userinfo();		
		$uid = isset($userinfo['id'])?$userinfo['id']:0;
		$params['uid'] = $uid;		
		
		$pid = $params['pid'];		
		
		//存储
		$m = Factory::GetModel('storage');
		$si = $m->getUserStorageInfo($uid);
		
		//target dir
		$tdir = $si['basedir'];
		if (!$tdir) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no storage for user '$uid'!");
			return false;
		}	
		
		$tdir = str_replace(DS, '/', $tdir);
		if (!is_dir($tdir)) {
			s_mkdir($tdir);
			if (!is_dir($tdir)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no dir '$tdir'!");
				return false;
			}
		}
				
		//检查freespace
		$freespace = $si['free'];
		if ($freespace < $params['need_size']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no space for upload! need_size=$params[need_size], free=$freespace", $si);
			return false;
		}		
		
		//原始文件名				
		$name = $params['name'];
		$extnames = s_extnames($name);
		$extname = $extnames['extname'];
		//检查文件类型，是不是在禁止上传名单中
		if (!$this->checkUploadFileType($extname)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "forbidden upload '$extname' files failed!");
			return false;
		}
		//生成临时文件
		$res = $this->initTmpFileInfo($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call initTmpFileInfo failed!");
			return false;
		}	
		
		//filename
		$tfileinfo = $params['fileinfo'];
		
		$id = $tfileinfo['id'];
		$fileid = $tfileinfo['fileid'];
		$filename = $tfileinfo['filename'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$name='.$name);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$filename='.$filename);		
		
		$fname = $id.'_'.$fileid.'.'.$extname;
		$dst = $tdir.DS.$fname;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$dst='.$dst);		
		
		if (file_exists($dst)) {
			$size = s_filesize($dst);
			$mtime = filemtime($dst);		
		} else {			
			$size = 0;	
			$mtime = 0;	
		}
		
		//$path
		$basepath = $si['basepath'];		
		$path = $basepath.'/'.$fname;		
		$type =  $this->ext2type($extname);
		
		
		$params['id'] = $id;
		$params['name'] = $name;
		$params['path'] = $path;
		$params['extname'] = $extname;
		$params['type'] = $type;
		$params['size'] = $size;
		$params['sid'] = $si['id'];
		$params['oid'] = $si['oid'];		
		$params['status'] = FILE_S_TMP;
		
		$res = $this->update($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call update failed!", $params);
			return false;
		}
		
		$params['pid'] = $pid;
		$params['filename'] = $filename;
		$params['fileid'] = $fileid;
		$params['fullextname'] = $extnames['fullextname'];
		$params['extname2'] = $extnames['extname2'];
		$params['opath'] = $dst;
		$params['basepath'] = $si['basepath'];
		$params['dst'] = $dst;
		$params['mtime'] = $mtime;	
		$params['tdir'] = $tdir;
		
		$params['status'] = FILE_S_NORMAL;
		
		
		//rlog($params);
		
		return true;
	}	
	
	
	protected function doUpload(&$params, &$options=array(), $return_if_exists=false)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... ", $params);
		
		$tmpfile = $params['tmp_name'];		
		if (empty($params['md5id']) && !empty($params['tmp_name'])) {
			$md5id = md5_file($tmpfile);
			if ($return_if_exists) {
				$fileinfo = $this->getOne(array('md5id'=>$md5id));
				if ($fileinfo) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: file '$fileinfo[name]' exists!");
					$params = array_merge($params, $fileinfo);
					return $fileinfo;
				}		
			}
		}
		
		$pid = isset($options['pid'])?intval($options['pid']):0;
		if ($pid < 0) 
			$pid = 0;	
		
		$params['pid'] = $pid;
		$res = $this->initUploadParams($params, $options);
		if (!$res)  {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "init upload params failed!", $params);
			return false;
		}
				
		$dst = $params['dst'];	
		$lsize = isset($params['lsize'])?$params['lsize']:0;	
		
		//oldsz
		$oldsz = $params['size'];
		
		$updatemd5 = false;		
		if (isset($params['appendupload']) && $params['appendupload']) {			
			//检查 range
			//$content_length = $params["content_length"];
			$range = $params["range"];
			$start = $range['start'];
			$end = $range['end'];
			
			$tmpsize = !empty($params['putfile'])?$params['content_length']:s_filesize($tmpfile); //put文件名为空
			
			$res = true;			
			if ($end != $start + $tmpsize-1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: invalid end '$end', tmpsize '$tmpsize' range! skip!!", $range);
			} else {
				if ($start != $oldsz) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: invalid start '$start' of range! skip!!", $range);
				} else {
					if (!empty($params['putfile'])) {	
						$fp = fopen("php://input", "rb");					
					} else {
						$fp = fopen($tmpfile, 'rb');
					}
					$res = file_put_contents(
							$dst,
							$fp,
							FILE_APPEND
							);
					fclose($fp);		
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call file_put_contents append to file to '$dst' failed!");
						return false;
					}
					$updatemd5 = true;	
				}			
			}
		} else if (!empty($params['putfile'])) { //整个文件
			$res = touch($dst);
			$fp = fopen("php://input", "rb");
			if ($fp) {
				$res = file_put_contents(
						$dst,
						$fp,
						FILE_APPEND
						);
				fclose($fp);
				
				if (!$res &&  $lsize > 0) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call file_put_contents write to file to '$dst' failed!");
					//return false;
				}
			}
		} else  {			
			if (function_exists("move_uploaded_file")) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "move file to '$dst' ...");
				$res = move_uploaded_file($tmpfile, $dst);
			} else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "copy file to '$dst' ...");
				$res = copy($tmpfile, $dst);			
			}
			$updatemd5 = true;
		}
		
		if (!file_exists($dst)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no dst '$dst'!res=$res");
			return false;
		}
		
		$size = s_filesize($dst);
				
		$params['size'] = $size;
		$params['tmp_name'] = 0;
		
		if (isset($options['nodbuploadcallback']) && $options['nodbuploadcallback'] == 1 ) {
			return true;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  $options);
		if (isset($options['viewcontent']) && $options['viewcontent'] == 1 ) {
			if ($size < 1024*1024) {
				$params['content'] = base64_encode(s_read($dst));						
			} else {
				$params['content'] = '';
			}
			return true;
		}
		
		//video
		//需要转成mp4，无损，H5能直接播
		if ( $size == $params['lsize']) {
			$params['status'] = FILE_S_NORMAL;
			$this->checkFileVideoInfo($params);
			
			//只有文件内容变更时，才更新
			if ($updatemd5) {				
				$new_md5id = md5_file($dst);
				if ($new_md5id != $md5id) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: new_md5id($new_md5id) != md5id($md5id)!!");	
					$params['md5id'] = $new_md5id;		
				}
			}
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "file upload DONE!size=".$size);				
		} else {
			$params['status'] = FILE_S_UPLOADDING; //分片上传
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: file upload PART!size=".$size);				
		}
		
		if (isset($params['range']))
			header('Range: 0-'.($size - 1));
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);		
		$res = $this->update($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "update file failed!");
			return false;
		}
		
		//检查一下截图
		if (!empty($params['duration'])) {
			$res = $this->timerProcessSnapImageForVideoFile($params);
			if (!$res) {
				//更新原文件记录中snap_id字段
				$_params = array('id'=>$params['id'], 'snap_id'=>0);
				$this->update($_params);
			}
		}
		
		return true;
	}
	
	protected function getHttpPutUploadParams($options)
	{
		$params =  $_REQUEST;
		
		//$this->getPathInfo($params);
		
		//文件名
		$params['name'] = isset($params['lp'])?$params['lp']:end($options['vpath']);
		$params['putfile'] = true;
		$params['tmp_name'] = '';//php://input';
		
		return $params;
		
	}
	
	
	/**
	 * 上传 
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function upload(&$options=array(), $return_if_exists = false)
	{
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");		
		$fdb = array();
		if (!empty($_FILES)) {
			foreach ($_FILES as $key => $v) {
				
				if (is_array($v['name'])) {
					
					$nr = count($v['name']);
					
					for($i=0; $i<$nr; $i++) {
						$params = array();
						
						$params['name'] = $v['name'][$i];
						$params['type'] = $v['type'][$i];
						$params['tmp_name'] = $v['tmp_name'][$i];
						$params['error'] = $v['error'][$i];
						$params['size'] = $v['size'][$i];
						$params['need_size'] = $params['size'];
						
						if (!$this->doUpload($params, $options, $return_if_exists)) {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "do upload failed!");		
							return false;
						}				
						
						$fdb[] = $params;
					}
				} else {
					$params = $v;		
					$params['need_size'] = $params['size'];		
					if (!$this->doUpload($params, $options, $return_if_exists)) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "do upload failed!");		
						return false;
					}
					$fdb[] = $params;
				}
			}
		} else { //可能是PUT过来的，检查
			if ($options['method'] == 'PUT') {
				$params = $this->getHttpPutUploadParams($options);
				if (!$params) {//file
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call getHttpPutUploadParams failed!");
					return false;
				}
				
				if (!$this->doUpload($params, $options, $return_if_exists)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "do upload failed!", $params);		
					return false;
				}
				$fdb[] = $params;
			}			
		}
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no files!", $_FILES);
		}		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $fdb;
	}
	
	public function filecontent(&$options=array())
	{
		$tmpfile = '';
		$size = 0;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");		
		$fdb = array();		
		foreach ($_FILES as $key => $v) {
			
			if (is_array($v['name'])) {
				
				$nr = count($v['name']);
				
				for($i=0; $i<$nr; $i++) {
					$params = array();
					
					$params['name'] = $v['name'][$i];
					$params['type'] = $v['type'][$i];
					$params['tmp_name'] = $v['tmp_name'][$i];
					$params['error'] = $v['error'][$i];
					$params['size'] = $v['size'][$i];
					$params['need_size'] = $params['size'];
					
					
					$tmpfile = $v['tmp_name'][$i];
					$size = $v['size'][$i];
					
					if ($tmpfile && $size < 1024*1024) {
						$params['content']  = base64_encode(s_read($tmpfile));	
					}
					unset($params['tmp_name']);					
					$fdb[] = $params;
					
					break;
				}
			} else {
				$tmpfile = $v['tmp_name'];
				$size = $v['size'];
				
				$params = $v;		
				$params['need_size'] = $params['size'];	
				$params['content'] = '';				
				if ($tmpfile && $size < 1024*1024) {
					$params['content']  = base64_encode(s_read($tmpfile));	
				}
				unset($params['tmp_name']);
				$fdb[] = $params;	
			}
		}		
		
		
		return $fdb;
	}
	
	
	
	/* =========================================================================================
	 *
	 * 下载 functions
	 *
	 * =======================================================================================*/
	
	
	
	protected function getRanges(&$params) 
	{
		// process Range: header if present
		if (isset($_SERVER['HTTP_RANGE'])) {
			
			// we only support standard "bytes" range specifications for now
			if (preg_match('/bytes\s*=\s*(.+)/', $_SERVER['HTTP_RANGE'], $matches)) {
				$params["ranges"] = array();				
				// ranges are comma separated
				foreach (explode(",", $matches[1]) as $range) {
					// ranges are either from-to pairs or just end positions
					list($start, $end) = explode("-", $range);
					$params["ranges"][] = ($start==="") 
						? array("last"=>$end) 
						: array("start"=>$start, "end"=>$end);
				}
			}
		}
	}
	
	protected function _multipart_byterange_header($mimetype = false, 
		$from = false, $to=false, $total=false) 
	{
		if ($mimetype === false) {
			if (!isset($this->multipart_separator)) {
				// initial
				
				// a little naive, this sequence *might* be part of the content
				// but it's really not likely and rather expensive to check 
				$this->multipart_separator = "SEPARATOR_".md5(microtime());
				
				// generate HTTP header
				header("Content-type: multipart/byteranges; boundary=".$this->multipart_separator);
			} else {
				// final 
				
				// generate closing multipart sequence
				echo "\n--{$this->multipart_separator}--";
			}
		} else {
			// generate separator and header for next part
			echo "\n--{$this->multipart_separator}\n";
			echo "Content-type: $mimetype\n";
			echo "Content-range: $from-$to/". ($total === false ? "*" : $total);
			echo "\n\n";
		}
	}
	
	protected function readdir($id, $uid=0)
	{
		$id = $fileinfo['id'];
		if ($uid > 0) {
			$filter = array('pid'=>$id);
		} else {
			$filter = array('pid'=>$id, 'cuid'=>$uid);
		}
		$udb = $this->select($filter);
				
		//$sql = "update cms_file set hits = hits + 1 where id=$id";
		//$this->_db->exec($sql);
		CJson::encodedPrint($udb);		
		exit;
	}	
	
	
	protected function getStream($fileinfo)
	{
		return fopen($fileinfo['opath'], 'rb');
	}
	
	protected function httpStatus($status) 
	{
		// simplified success case
		if ($status === true) {
			$status = "200 OK";
		}
		
		// generate HTTP status response
		header("HTTP/1.1 $status");
		header("X-RC-Status: $status", true);
	}
	
	protected function bytes($str)
	{
		static $func_overload;				
		if (is_null($func_overload)) {
			$func_overload = @extension_loaded('mbstring') ? ini_get('mbstring.func_overload') : 0;
		}		
		return $func_overload & 2 ? mb_strlen($str,'ascii') : strlen($str);
	}
	
	protected function fread($fd, $size)
	{
		$recv_size = 0;
		
		while (!feof($fd)) {
			$buffer = fread($fd, 4096);
			$recv_size  += $this->bytes($buffer);
			echo $buffer;	
			
			if ($size !== -1 && $recv_size >= $size)
				break;
		}
	}
	
	
	protected function readfile($fileinfo) 
	{
		$mimetype = $this->ext2mimetype($fileinfo['extname']);	
		
		$filename = $fileinfo['filename'];
		$filesize = $fileinfo['size'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$mimetype='.$mimetype);
		
		$stream = $this->getStream($fileinfo);
		if (!$stream) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no stream");
			return false;
		}
		
		
		$params = Array();		
		$this->getRanges($params);
		
		if (!headers_sent()) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
			
			$status = "200 OK";			
			if (!isset($mimetype)) {
				$mimetype = "application/octet-stream";
			}
			header("Content-type: $mimetype");
			
			if (isset($fileinfo['ts'])) {
				header("Last-modified:".gmdate("D, d M Y H:i:s ", $fileinfo['ts'])."GMT");
			}
			
			// GET handler returned a stream
			if (!empty($params['ranges']) && (0===fseek($stream, 0, SEEK_SET))) {
				// partial request and stream is seekable 				
				if (count($params['ranges']) === 1) {
					$range = $params['ranges'][0];					
					if (isset($range['start'])) {
						$res = fseek($stream, $range['start'], SEEK_SET);
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "partial request and stream is seekable  ... res=$res", $range);
						
						if (feof($stream)) {
							$this->httpStatus("416 Requested range not satisfiable");
							return false;
						}
						
						if (isset($range['end']) && $range['end']) {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "load1 ....", $range, $filesize);
							
							$size = $range['end']-$range['start']+1;
							$this->httpStatus("206 partial");
							header("Content-Length: $size");
							header("Content-Range: bytes $range[start]-$range[end]/"
									. (isset($filesize) ? $filesize : "*"));
							
							header("ETag: $fileinfo[fileid]");
							
							$this->fread($stream, $size);
							/*		
							while ($size && !feof($stream)) {
								$buffer = fread($stream, 4096);
								$size  -= $this->bytes($buffer);
								echo $buffer;
							}*/
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "load1 ... size=$size", $range);
							
						} else {
							
							$start = $range['start'];
							$chunksize = 1024*1024*4;
							$size = $chunksize;
							
							$left = $filesize-$start;
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "load2 .... range=$range, filesize=$filesize, size=$size, left=$left", $range);
							
							$this->httpStatus("206 partial");
							
							if (isset($filesize)) {
								$end = $start + $size-1;
								if ($end >= $filesize-1) {
									$end = $filesize -1;
									$size = $end - $start + 1;
								} 
								
								header("Content-Length: ".$size);
								header("Content-Range: bytes ".$start."-".$end."/".$filesize);
								header("ETag: $fileinfo[fileid]");
							}
							$this->fread($stream, $size);
							//fpassthru($stream);
						}
					} else {
						
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "......... all ....", $params['ranges']);
						
						header("Content-length: ".$range['last']);
						fseek($stream, -$range['last'], SEEK_END);
						
						$this->fread($stream, -1);
						//fpassthru($stream);
					}
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "... _multipart_byterange_header !", $params['ranges']);
					$this->_multipart_byterange_header(); // init multipart
					foreach ($params['ranges'] as $range) {
						// TODO what if size unknown? 500?
						if (isset($range['start'])) {
							$from = $range['start'];	
							$to   = !empty($range['end']) ? $range['end'] : $filesize-1; 
						} else {
							$from = $filesize - $range['last']-1;
							$to   = $filesize -1;
						}
						$total = isset($filesize) ? $filesize : "*"; 
						$size  = $to - $from + 1;
						$this->_multipart_byterange_header($params['mimetype'], $from, $to, $total);
						
						fseek($stream, $from, SEEK_SET);						
						$this->fread($stream, $size);
						
						/*
						while ($size && !feof($stream)) {
							$buffer = fread($stream, 4096);
							$size  -= $this->bytes($buffer);
							echo $buffer;
						}*/
					}
					$this->_multipart_byterange_header(); // end multipart
				}
			} else {//chunk
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "normal request or stream isn't seekable, return full content!", $filesize);
				
				//Content-Range bytes 0-4864389766/4864389767
				$end = $filesize - 1;
				//$this->httpStatus("206 partial");
				header("Content-Length: $filesize");
				header("Content-Range: bytes 0-$end/"	. (isset($filesize) ? $filesize : "*"));
				//ETag
				//
				header("Accept-Ranges: bytes");
				header("ETag: $fileinfo[fileid]");
				
						
				// normal request or stream isn't seekable, return full content
			
				$this->fread($stream, -1);
				
				//fpassthru($stream);
				
			}
		}
		
		fclose($stream);
		return true;
	}
	
	
	protected function doRead($fileinfo, &$options=array(), $attachment=true)
	{		
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no fileinfo!");
			return false;
		}
		
		$id = $fileinfo['id'];
		
		//http://localhost/rc5/file/173/avi
		if ($fileinfo['isdir'] == 1) { //目录
			return $this->readdir($id, $options);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "read ... ", $fileinfo);
		if ($this->is_av($fileinfo)) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "is_av ...", $fileinfo);
			
			if ($fileinfo['status'] == FILE_S_CONVERTED && $fileinfo['convert_id'] > 0) { //被转码完成的VIDEO
				$convert_id = $fileinfo['convert_id'];
				$convertfileinfo = $this->get($convert_id);
				if ($convertfileinfo) {
					$fileinfo = $convertfileinfo;
				} 
			}
			
		} else if (!$this->is_image($fileinfo)) {
			//if ($fileinfo['type'] != FT_VIDEO && $fileinfo['type'] != FT_AUDIO && $fileinfo['type'] != FT_IMAGE ) {
			
			//累加下载次数
			$this->inc($id, 'downloads');
			//下载通知
			$m = Factory::GetModel('file2model');
			$m->trigger('download', $fileinfo);
			
			$filename= $fileinfo['filename'];			
			
			$contenttype=CFileType::getMimetype($fileinfo['extname']);
			header("Content-Type: $contenttype");
			
			//加上这条，PDF不能直接打开，且下载也是慢？？
			if ($attachment || $this->is_tar($fileinfo)) {
					$filename= urlencode($filename);
					//header("Content-Type: application/octet-stream");
					header("Content-Disposition:attachment;filename=$filename");			
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN0... is_tar or attachment=$attachment");
			}
		} else {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1...");
		}
		
		$this->readfile($fileinfo);	
		
		$fid = $fileinfo['id'];
		$this->inc($fid, 'hits');
		
		return true;
	}
	
	
	public function read($id, &$options=array(), $attachment=true)
	{		
		$fileinfo = $this->get($id);
		
		$res = $this->doRead($fileinfo, $options, $attachment);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call doRead failed!id=$id");
		}
				
		exit;
	}
	
	
	public function share($id, &$options=array(), $attachment=false)
	{
		$fileinfo = $this->get($id);
		
		if ($fileinfo['shared'] <= 0) {
			header("HTTP/1.1 403 NOT SHARED");
			echo "403 Not SHARED";
			exit;
		}
		
		$res = $this->doRead($fileinfo, $options, $attachment);
		if (!$res) {
			header("HTTP/1.1 404 Not found");
			echo "404 Not found";
			exit;
		}
	}
	
	function onoffShare($id)
	{
		$fileinfo = $this->get($id);
		if (!$fileinfo)
			return false;			
		
		$shared = $fileinfo['shared'];
		
		$shared = $shared?0:1;
		
		$_params = array();
		$_params['id'] = $id;
		$_params['shared'] = $shared;
		
		$res = $this->update($_params);
		
		
		return $res;
	}
	
	/**
	 * 下载
	 *
	 * @param mixed $id This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function download($id, $options, $shared=true)
	{
		
		$fileinfo = $this->getForView($id, $options);
		if (!$fileinfo) {
			header("HTTP/1.1 404 Not found");
			echo "404 Not found $id";
			exit;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN", $fileinfo);exit;
		
		if ($shared) { //检查是否为分享状态
			if ($fileinfo['shared'] <= 0) {
				header("HTTP/1.1 403 Not Shared");
				echo "403 Not Shared $id";
				exit;
			}
		}
		
		$fid = $fileinfo['id'];
		
		//累加下载次数
		$this->inc($fid, 'downloads');
		
		//下载通知
		$m = Factory::GetModel('file2model');		
		$m->trigger('download', $fileinfo);
		
		if ($fileinfo['status'] == FILE_S_LINK) {
			
			redirect($fileinfo['downloadUrl']);
			exit;
		}
		/*if ($fileinfo['size'] > 1024*1024*256) { //超过256M
			redirect($fileinfo['downloadUrl']);			
		} else {*/
		$filename = $fileinfo['filename'];
		header("Content-Disposition:attachment;filename=\"".$filename."\"");
		//header("Content-Type: $mimetype");
		//header("Accept-ranges:bytes");
		//header("Accept-length:".$filesize);			
		$this->readFile($fileinfo);		
		//}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		exit;
	}
	
	
	public function downloadshare($id, &$options=array())
	{
		$fileinfo = $this->get($id);
		
		if ($fileinfo['status'] != FILE_S_LINK && $fileinfo['status'] != FILE_S_DOWNLOAD) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no LINK file '$id'!");
			return false;
		}
		
		$params = array();		
		$params['id'] = $id;
		$params['status'] = FILE_S_DOWNLOAD;
		$res = $this->update($params);
		
		if ($fileinfo['status'] == FILE_S_DOWNLOAD) {//启开下载中
			$this->downloadOne($fileinfo);
		}
		
		return $res;		
	}
	
	/* =================================================================================
	 * HTTP functions for WEBDAV
	 * ================================================================================*/
	
	
	protected function getPathInfo($uid, $_path, &$options = array())
	{
		$params = array();
		//查询
		$path = s_urldecode($_path);
		//to UTF8
		$path = safeEncoding($path, PHP_CHARSET, true);
		$pathinfo = array();			
		
		if (!$path || $path == '/') {
			$pid = 0;
			$name = 'root';
			$fileinfo['name'] = $name;
			$fileinfo['path'] = '/';
			$fileinfo['ctime'] = time();
			$fileinfo['ts'] = time();
			$fileinfo['isdir'] = 1;
			$fileinfo['isroot'] = true;
			$fileinfo['id'] = 0;
			
			$pathinfo[] = $fileinfo;
			
			$exists = true;
			
		} else {
			$udb = explode('/', $path);		
			$vpath = array();
			foreach ($udb as $key=>$v) {
				$v = trim($v);
				if (!$v)
					continue;
				$vpath[] = $v;
			}
			
			$pid = (isset($options['pid']) &&$options['pid'] >=0) ?$options['pid']:0;	
			
			$exists = false;	
			
			$nr = count($vpath);
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vpath);
			
			for($i=0; $i<$nr; $i++) {			
				$name = $vpath[$i];
				$fileinfo = $this->getOne(array('cuid'=>$uid, 'pid'=>$pid, 'name'=>$name));
				if ($fileinfo) {
					if ($fileinfo['isdir'])				
						$pid = $fileinfo['id'];				
					$exists = true;					
				} else {
					$exists = false;
				}
				$pathinfo[] = array('name'=>$name, 'fileinfo'=>$fileinfo, 'exists'=>$exists);
			}
		}
				
		$params['path'] = $path;
		$params['name'] = $name;
		$params['uid'] = $uid;
		$params['pid'] = $pid;
		$params['exists'] = $exists;
		$params['fileinfo'] = $fileinfo;		
		$params['pathinfo'] = $pathinfo;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		return $params;
	}
	
	protected function http_HEAD($params, &$options=array())
	{
		//rlog($options);
		//HEAD /admin.php/my_file/bigupload/2%2Emp4/2%2Emp4?tt=1&ssid=br9Ouua%2BA0z%2FYPxAVQokwlOnQUSaod5DWg3ZsooUTjw%3D&pid=0&lp=2.mp4
		$fileinfo = $params['fileinfo'];
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $fileinfo);
		
		$mimetype = '';
		if ($fileinfo) {	
			//dst and size
			if ($fileinfo['status'] == FILE_S_TMP) {
				//查询一下文件dst and size
				$sinfo = $this->getStorageInfo($fileinfo['sid']);
				$dst = $sinfo['mountdir'].DS.$fileinfo['path'];
				$fileinfo['size'] = file_exists($dst)?s_filesize($dst):0;
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $dst, $fileinfo['size']);
				
			}
						
			// detect resource type
			$mimetype = CFileType::getMimetype($fileinfo['extname']);
			// detect modification time
			// see rfc2518, section 13.7
			// some clients seem to treat this as a reverse rule
			// requiering a Last-Modified header if the getlastmodified header was set
			$mtime = $fileinfo['ts'];
			
			// detect resource size
			$size = $fileinfo['size'];
			header("HTTP/1.1 200 OK");
			header("Content-type: $mimetype");
			
			header("Last-modified:".gmdate("D, d M Y H:i:s ", $mtime)."GMT");
			header("Content-length: ".$fileinfo['size']);
		} else {
			header("HTTP/1.1 404 Not found");
		}
		
		
		$post_max_size =	nformat_get_human_file_size(ini_get('post_max_size'));
		header("post_max_size: $post_max_size");
		
		exit;
	}
	
	protected function http_GET($params, &$options=array())
	{
		$fileinfo = $params['fileinfo'];
		
		//rlog($fileinfo);		
		
		if ($fileinfo['isdir']) {
			$res = $this->readdir($fileinfo['id'], $params['uid']);
		} else {
			$res = $this->read($fileinfo['id']);	
		}
		
		return $res;
	}
	
	
	protected function preparePutFile(&$params=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN preparePutFile");
		
		$startpos = 0;
		$endpos = 0;
		$dstfile = $params['dst'];		
		if ($params['size'] == 0) {
			$fd = fopen($dstfile, 'wb');				
		} else {
			$fd = fopen($dstfile, 'ab+');			
			$range = $params['range'];
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $range);
			if ($range) {
				$startpos = $range['start'];
				$endpos = $range['end'];
				fseek($fd, $startpos, SEEK_SET);
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING : no range");
				$endpos = $params['content_length'];
			}						
		}
		$params['startpos'] = $startpos;
		$params['endpos'] = $endpos;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT preparePutFile", $startpos, $endpos, $range);
		return $fd;		
	}
	
		
	
	
	protected function postPutFile(&$params, $total)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN postPutFile");
		$tmpfile = $params['dst'];
		$size = s_filesize($tmpfile);
		//当前片
		$lsize = $params['content_length'] + $params['startpos'];
		
		$try = 100;
		while($size != $lsize) {
			usleep(100000);
			system('sync');
			$size = s_filesize($tmpfile);
			if ($try-- <= 0)
				break;						
		}
		
		if ($total != $lsize || $size != $total ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "big file upload interrupt! size=$size, lsize=$lsize, total=$total", $params);
			return false;
		}
		
		
		$params['size'] = $size;
		$params['opath'] = $params['dst'];
		$params['status'] = FILE_S_NORMAL;
		$this->checkFileVideoInfo($params);
			
		
		$params['md5id'] = md5_file($params['dst']);

		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call attach set failed!");
			$this->delTmpFileInfo($params['id']);
			return false;
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "OUT postPutFile...", $params);		
		return $res;
		
	}
	
	protected function doPutFile(&$params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "IN doPutFile ... ", $params);
		
		$upload_size = 0;				
		$infd = $params["fp"];
		$outfd = $this->preparePutFile($params);		
		
		while (!feof($infd)) {
			$buf = fread($infd, 8192);
			if ($buf === false) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fread failed!");
				break;
			}
			$upload_size += strlen($buf);
			$res = fwrite($outfd, $buf);
			if ($res < 0) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call fwrite failed! res=".$res);
				break;
			}
		}
		fclose($outfd);
		
		$endpos = $params['endpos'];
		$startpos = $params['startpos'];
		$total_size = $startpos + $upload_size;
		
		if ($res >= 0) {
			system('sync');
			$this->postPutFile($params, $total_size);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT doUploadBigFile, endpos=$endpos, startpos=$startpos, total_size=$total_size, upload_size=$upload_size, res=$res");
		return true;
	}
	
	
	protected function initHttpPutParams(&$params, &$options=array())
	{
		$res = $this->initUploadParams($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call initUploadParams failed failed!", $params);
			return false;
		}
		
		return $res;
	}
	
	/**
	 * 处理 HTTP PUT 命令
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function http_PUT(&$params, &$options=array())
	{
		/*
		[HTTP_OVERWRITE] => T
		[HTTP_TRANSLATE] => f
		*/
		$overwrite = (isset($_SERVER['HTTP_OVERWRITE']) &&  $_SERVER['HTTP_OVERWRITE']== 'T')?true:false;
		if ($params['exists']) {
			$fileinfo = $params['fileinfo'];
			if (!$overwrite && $fileinfo['status'] != FILE_S_TMP) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "file exists!", $params);
				return false;
			}
			$params['id'] = $fileinfo['id'];
		} 
		
		$params['content_length'] = $_SERVER["CONTENT_LENGTH"];
		$params['need_size'] = $params['content_length'] ; //检查可用空间时使用
		
		// get the Content-type 
		if (isset($_SERVER["CONTENT_TYPE"])) {
			// for now we do not support any sort of multipart requests
			if (!strncmp($_SERVER["CONTENT_TYPE"], "multipart/", 10)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support mulipart PUT requests");
				return false;
			}
			$params["content_type"] = $_SERVER["CONTENT_TYPE"];
		} else {
			// default content type if none given
			$params["content_type"] = "application/octet-stream";
		}
		
		/* RFC 2616 2.6 says: "The recipient of the entity MUST NOT 
		 ignore any Content-* (e.g. Content-Range) headers that it 
		 does not understand or implement and MUST return a 501 
		 (Not Implemented) response in such cases."
		*/ 
		foreach ($_SERVER as $key => $val) {
			if (strncmp($key, "HTTP_CONTENT", 11)) 
				continue;
			switch ($key) {
				case 'HTTP_CONTENT_ENCODING': // RFC 2616 14.11
					// TODO support this if ext/zlib filters are available
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "The service does not support '$val' content encoding");
					return false;
				
				case 'HTTP_CONTENT_LANGUAGE': // RFC 2616 14.12
					// we assume it is not critical if this one is ignored
					// in the actual PUT implementation ...
					$params["content_language"] = $val;
					break;
				
				case 'HTTP_CONTENT_LENGTH':
					// defined on IIS and has the same value as CONTENT_LENGTH
					break;
				
				case 'HTTP_CONTENT_LOCATION': // RFC 2616 14.14
					/* The meaning of the Content-Location header in PUT 
					 or POST requests is undefined; servers are free 
					 to ignore it in those cases. */
					break;
				
				case 'HTTP_CONTENT_RANGE':    // RFC 2616 14.16
					// single byte range requests are supported
					// the header format is also specified in RFC 2616 14.16
					// TODO we have to ensure that implementations support this or send 501 instead
					if (!preg_match('@bytes\s+(\d+)-(\d+)/((\d+)|\*)@', $val, $matches)) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does only support single byte ranges");
						return false;
					}
					
					$range = array("start" => $matches[1], "end" => $matches[2]);
					if (is_numeric($matches[3])) {
						$range["total_length"] = $matches[3];
					}
					
					$params["range"] = $range;
					
					break;
				
				case 'HTTP_CONTENT_TYPE':
					// defined on IIS and has the same value as CONTENT_TYPE
					break;
				
				case 'HTTP_CONTENT_MD5':      // RFC 2616 14.15
					// TODO: maybe we can just pretend here?
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support content MD5 checksum verification"); 
					return false;
				
				default: 
					// any other unknown Content-* headers
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support '$key'"); 
					return false;
			}
		}
		
		$res = $this->initHttpPutParams($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid upload params failed!", $params);
			return false;
		}
						
		// 保存
		$params["fp"] = fopen("php://input", "rb");
		$res = $this->doPutFile($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call doUploadBigFile file!");
			return false;
		}
		
		return true;
	}
	
	protected function initHttpParams(&$params, $options=array())
	{
		$userinfo = get_userinfo();
		
		//所有者id
		$uid = $userinfo['id'];		
		$path = $options['_path'];
		$params = $this->getPathInfo($uid, $path, $options);		
		if (!$params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid uid '$uid' or path '$path'!");
			return false;
		}
		
		
		return true;		
	}
	
	
	public function http(&$options=array())
	{		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		$params = array();		
		$res = $this->initHttpParams($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid upload params failed!", $params);
			return false;
		}
		
		
		$method = 'http_'.$options['method'];	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $method);
		$res = false;
		if (method_exists($this, $method)) {
			$res = $this->$method($params, $options); 
		}  else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no method '$method'!");
			return false;
		}
		
		if (!$res) {			
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call $method failed!", $params);			
			return false;
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $params;
	}
	
	/* ======================================================================
	 * timerProcess
	 * ====================================================================*/
	protected function lock($name='file')
	{
		//转马非常费时，创建一个临时文件锁
		$tagfile = RPATH_CACHE.DS."$name.locked";
		if (file_exists($tagfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "file lock tag '$tagfile' exists!");
			return false;
		}
		touch($tagfile);
		return true;
	}
	
	protected function unlock($name='file')
	{
		$tagfile = RPATH_CACHE.DS."$name.locked";
		@unlink($tagfile);
	}
	
	protected function lockActiveTime($name='file')
	{
		$tagfile = RPATH_CACHE.DS."$name.locked";
		if (!file_exists($tagfile))
			return false;
		return filemtime($tagfile);
	}
	
	protected function lockActive($name='file')
	{
		$tagfile = RPATH_CACHE.DS."$name.locked";
		touch($tagfile);
	}
	
	protected function lockFile($name='file')
	{
		return RPATH_CACHE.DS."$name.locked";
	}
	
	protected function write_tmpinfo_to_lock($tmpinfo)
	{
		$tagfile = $this->lockFile();
		s_write($tagfile, $tmpinfo);
	}
	
	protected function timerProcessCheckConvert()
	{
		$tagfile = $this->lockFile();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		if (file_exists($tagfile)) {
			$res = s_read($tagfile);
			$udb = explode('|', $res);
			$id = $udb[0];
			$outfile = $udb[1];
			$mtime = filemtime($outfile);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "############# mtime=".tformat($mtime));
			$ts = time();
			
			if (file_exists($outfile)) {
				$sz = s_filesize($outfile);
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'sz='.$sz);
				
				if ($sz > 0) {//WIN32 PHP 文件不能超过2G
					$params = array('id'=>$id,'size'=>$sz, 'ts'=>$ts);
					$res = $this->update($params);	
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update file failed!", $params);					
					}
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "update file size=$sz and mtime=".tformat($mtime), $params);
				} else {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'WARNING: get size failed!outfile='.$outfile);
					return false;
				}	
				
				//检查文件最后一次变更时间
				$delta = $ts - filemtime($outfile);					
				
				if ($delta > 30) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "last modified before {$delta}s, id=$id");
					
					$vinfo = get_video_info($outfile);
					if (!$vinfo) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Unknown video info!", $outfile);
						return false;
					}
					
				
					$fileinfo = $this->get($id);
					if ($fileinfo) {
						
						$org_fid = intval($fileinfo['convert_id']); //临时用
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fileinfo);
						
						//更新	
						$params = array('id'=>$id,'status'=>1, 'convert_id'=>0, 'md5id'=>md5_file($outfile));						
						
						$fileinfo['opath'] = $outfile;
						if ($this->checkFileVideoInfo($fileinfo)) {//读取VIDO相关信息
							$params['width'] = $fileinfo['width'];
							$params['height'] = $fileinfo['height'];
							$params['description'] = $fileinfo['description'];
						}
						
						$res = $this->update($params);	
						if (!$res) {
							rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update file failed!", $params);
						}
						
						//转码完毕		
						$params = array('id'=>$org_fid,'status'=>FILE_S_CONVERTED);
						$res = $this->update($params);	
						if (!$res) {
							rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update file failed!", $params);
						}
												
						//$this->checkSyncForDir($id);
						
						//clean .lock
						$this->unlock();
						
						rlog(RC_LOG_INFO, __FILE__, __LINE__, "file convert from id '$org_fid' to '$id' done.");						
					}
					
				}
			} else { //文不存在
				$delta2 = $ts - filemtime($tagfile);	
				if ($delta2 > 180) {//记录的文件不存，TAG超时，判定为无效
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid tag '$tagfile'!");
					$this->unlock();
				} else {				
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: no file '$tagfile'!delta2=".$delta2);
				}
			}
		}		
		return true;
	}
	
	
	/**
	 * 定时器自动转码
	 *
	 * @param mixed $fileinfo This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function timerProcessConvertVideo($fileinfo)
	{
		//存储
		$si = $this->getFileStorageInfo($fileinfo);
		$infile = $si['mountdir'].DS.$fileinfo['path'];
		$basedir = $si['basedir'];
		$basepath = $si['basepath'];
		
		//rlog($infile);		
		$name = $fileinfo['name'].'(转码)';
		$extname = 'mp4'; 
		$filename = $name.'.'.$extname;
		
		//filename
		$fileid = $this->genFileIDName($fileinfo['pid'], $filename);		
		$params = array('filename'=>$filename, 'cuid'=>$fileinfo['cuid'], 'uid'=>$fileinfo['uid'], 
				'pid'=>$fileinfo['pid'], 'extname'=>$extname, 'fileid'=>$fileid);
		$tfileinfo = $this->getTmpFileInfo($params);
		if (!$tfileinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!", $params);
			return false;
		}
		
		$id = $tfileinfo['id'];
	
		$newfilename = $id.'_'.$fileid.'.'.$extname;
		$newpath = $basepath.'/'.$newfilename;
		$outfile = $basedir.DS.$newfilename;
		
		if (file_exists($outfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING file '$outfile' exists!");
			$this->delTmpFileInfo($id);
			return false;
		}
		
		//临时文件路径，写入lock
		$cacheinfo = array();
		$cacheinfo['id'] = $id;
		$cacheinfo['ts'] = time();
		
		$tmpinfo = "$id|$outfile";		
		$this->write_tmpinfo_to_lock($tmpinfo);				
		
		//转码费时，转码之间把状态置为：转码中
		$_params = array();
		$_params['id'] = $fileinfo['id'];
		$_params['convert_id'] = $id;
		$_params['status'] = FILE_S_CONVERTING;		
		$res = $this->update($_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update file failed!", $_params);
			$this->delTmpFileInfo($id);
			return false;
		}
		
		$type = CFileType::ext2type($extname);
		
		//更新		
		$params = array();
		$params['id'] = $id;
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['extname'] = $extname;
		$params['type'] = $type;
		$params['path'] = $newpath;
		$params['status'] = 0; //正常		
		$params['size'] = 0;
		$params['convert_id'] = $fileinfo['id'];
		
		$params['uid'] = $fileinfo['uid'];
		$params['cuid'] = $fileinfo['cuid'];
		$params['sid'] = $fileinfo['sid'];
		$params['oid'] = $fileinfo['oid'];
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set file failed!", $params);
			return false;
		}
		//转码
		//$cacheffmpeglog = str_replace(DS, '/', RPATH_CACHE.DS.'cacheffmpeglog.'.$id);
		
		$cmd = 	"ffmpeg -v quiet -i \"$infile\"  -c:v libx264 -c:a aac -r 25 -strict -2 -y \"$outfile\"";	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $cmd.' ...');
		
		$res = run($cmd, true);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'res='.$res);
		
		return true;	
		
	}	
	
	
	/**
	 * timerProcessSnapImageForVideoFile 定时器任务自动把视频截图 
	 * 
	 * 本方法由：$this->timerProcess 调用
	 *
	 * @param mixed $fileinfo 文件记录
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	protected function timerProcessSnapImageForVideoFile($fileinfo)
	{
		//存储
		$si = $this->getFileStorageInfo($fileinfo);
		$infile = $si['mountdir'].DS.$fileinfo['path'];
		
		$basedir = $si['basedir'];
		$basepath = $si['basepath'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $infile);
		$name = $fileinfo['name'].'(截图)';
		$extname = 'jpg';
		$filename = $name.'.'.$extname;
		
		//生成临时文件
		$fileid = $this->genFileIDName($fileinfo['pid'], $filename);		
		$params = array('filename'=>$filename, 'cuid'=>$fileinfo['cuid'], 'uid'=>$fileinfo['uid'], 
				'pid'=>$fileinfo['pid'], 'extname'=>$extname, 'fileid'=>$fileid);			
		$tfileinfo = $this->getTmpFileInfo($params);
		if (!$tfileinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!", $params);
			return false;
		}
		//update snap_id		
		//更新原文件记录中snap_id字段
		$id = $tfileinfo['id'];
		$_params = array('id'=>$fileinfo['id'], 'snap_id'=>$id);
		$this->update($_params);
		
		$newfilename = $id.'_'.$fileid.'.'.$extname;
		$newpath = $basepath.'/'.$newfilename;
		$outfile = $basedir.DS.$newfilename;
		
		if (file_exists($outfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING file '$outfile' exists!");
			$this->delTmpFileInfo($id);			
			return false;
		}
		
		//截图
		//ffmpeg -v quiet -i \"$infile\" -y -f image2 -ss 1 -t 1 -s $width".'x'."$height $dst		
		$cmd = 	"ffmpeg -v quiet -i \"$infile\"  -y  -ss 1 -t 1  -f image2 -frames:v 1 \"$outfile\"";	
		$res = run($cmd);
		//rlog($cmd.', res='.$res);
		if (!$res || !file_exists($outfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call run failed!cmd=$cmd", $fileinfo);
			$this->delTmpFileInfo($id);
			return false;
		} 
		
		$type =  CFileType::ext2type($extname);
		$size = filesize($outfile);
		
		$params = array();
		
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['extname'] = $extname;
		$params['type'] = $type;
		$params['size'] = $size;
		$params['path'] = $newpath;
		$params['md5id'] = md5_file($outfile);
		
		$params['id'] = $id;
		$params['pid'] = $fileinfo['pid'] ;
		$params['snap_id'] = $fileinfo['id'] ; //截图出自
		$params['status'] = FILE_S_NORMAL;
		
		$params['uid'] = $fileinfo['uid'];
		$params['cuid'] = $fileinfo['cuid'];
		
		$params['sid'] = $fileinfo['sid'];
		$params['oid'] = $fileinfo['oid'];
		
		//rlog($params);
		
		$res = $this->update($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set snap file failed!", $params);
			return false;
		}
		
		return $res;
	}
	
	
	protected function doFile2Org($f2oinfo)
	{
		/* not use WEBDAV*/
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: NOT use WEBDAV!!!!!");
		return false;
		
		$id = $f2oinfo['id'];
		
		$m = Factory::GetModel('file2org');		
		$m->setStatus($id, 2);
		
		$oid = $f2oinfo['oid'];
		$fid = $f2oinfo['fid'];
		$fileinfo = $this->get($fid);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no file of id '$fid'");
			$m->del($id);
			return false;
		}
		
		$src = $fileinfo['opath'];
		
		
		//dst
		$sid = $f2oinfo['sid'];
		$m2 = Factory::GetModel('storage');
		$dststorageinfo = $m2->get($sid);
		if (!$dststorageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage of id '$sid'");
			$m->del($id);
			return false;
		}
		
		$mountdir = $dststorageinfo['mountdir'];
		$dst = $mountdir.DS.$fileinfo['path'];
		//rlog('$src='.$src.', $dst='.$dst);			
		
		if (!file_exists($dst)) {
			$dstdir = dirname($dst);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'dir='.$dstdir);	
			if (!is_dir($dstdir)) { //检查文件是否存在
				if (!s_mkdir($dstdir)) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call mkdir '$dstdir' failed!");
					return false;
				}			
			}
			/* not use WEBDAV mount path 
			$res = copy($src, $dst);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call copy failed! src=$src,dst=$dst");
				$m->del($id);
				return false;
			}*/
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: NOT use WEBDAV!!!!!");
			return false;
		}
		if (!file_exists($dst)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no dst! dst=$dst");
			$m->del($id);
			return false;
		}
		
		//更新发布: playUrl
		//$playUrl = $dststorageinfo['vodrooturl'].'/'.$fileinfo['path'];
		$playUrl = $dststorageinfo['lanvodrooturl'].'/'.$fileinfo['path'];
		
		$m3 = Factory::GetModel('pub2org');
		$m3->updatePlayUrlByFidOid($fid, $oid, $playUrl);					
		
		//更新状态				
		$m->setStatus($id, 3);
	}
	
	protected function timerProcessFile2Org()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$m = Factory::GetModel('file2org');
		//查找一条需要待镜像的文件
		$f2oinfo = $m->getOne(array('status'=>1));
		if ($f2oinfo) {
			if ($this->lock()) {
				$res = $this->doFile2Org($f2oinfo);			
				$this->unlock();
			}
		}	
		
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
	}
	
	protected function timerProcessFileDeleted()
	{
		$res = false;
		$params = array('status'=>FILE_S_DELETING);
		$udb = $this->select($params);
		foreach ($udb as $key=>$v) {
			$res = $this->del($v['id']);
		}	
		return $res;	
	}
	
	
	
	protected function downloadOne($fileinfo)
	{
		$filename = $fileinfo['filename'];
		
		$storageinfo = $this->getStorageInfo($fileinfo['sid']);
		if ($storageinfo['type'] == ST_LOCAL) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "local file not need download!");			
			return false;
		}
		
		if (!empty($fileinfo['fromurl'])) {
			$url = $fileinfo['fromurl'];		
		} else {
			$url = $storageinfo['download_prefix'].'/'.$fileinfo['path'];		
		}
		
		$localfile = $storageinfo['mountdir'].DS.$fileinfo['path'];
		$dirname = dirname($localfile);
		if (!is_dir($dirname)) {
			s_mkdir($dirname);
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "will download file '$filename' from '$url' save to '$localfile'");
		
		$cmd = "nget -o $localfile $url";
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "cmd=$cmd");
		
		$res = run($cmd, true);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "res=$res");
		if ($res) {
			//更新状态为 FILE_S_DOWNLOADING
			$_params = array();
			$_params['id'] = $fileinfo['id'];
			$_params['status'] = FILE_S_DOWNLOADING;
			$_params['ts'] = time();
			$_params['lsize'] = file_exists($localfile)?s_filesize($localfile):0;
			$res2 = $this->update($_params);
		}		
				
		return $res;
	}
	
	protected function lockDownload()
	{
		return $this->lock('download');
	}
	
	protected function unlockDownload()
	{
		return $this->unlock('download');
	}
	
	protected function timerProcessFileDownload()
	{
		$res = false;
		$params = array('status'=>FILE_S_DOWNLOAD);
		$udb = $this->select($params);
		foreach ($udb as $key=>$v) {
			if ($this->lockDownload()) {
				$res = $this->downloadOne($v);
				if (!$res) {
					$this->unlockDownload();
				}
			}
		}	
		return $res;	
	}	
	
	
	protected function checkDownloadingOne($fileinfo)
	{
		$filename = $fileinfo['filename'];
		
		$storageinfo = $this->getStorageInfo($fileinfo['sid']);
		if ($storageinfo['type'] == ST_LOCAL) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "local file not need download!");			
			return false;
		}
		$url = $storageinfo['download_prefix'].'/'.$fileinfo['path'];		
		
		$localfile = $storageinfo['mountdir'].DS.$fileinfo['path'];
		$dirname = dirname($localfile);
		if (!is_dir($dirname)) {
			s_mkdir($dirname);
		}
		
		$localfile = str_replace('/', DS, $localfile);
		$lsize = file_exists($localfile)?s_filesize($localfile):0;
		
		$last_ts = $fileinfo['ts'];
		$last_lsize = $fileinfo['lsize'];
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "check downloading '$localfile' lsize=$lsize");
		
		//更新状态为 FILE_S_DOWNLOADING
		$_params = array();
		$_params['id'] = $fileinfo['id'];
		$_params['lsize'] = $lsize;
		
		$this->lockActive('download');		
		
		if ($last_lsize != $lsize) {
			$_params['ts'] = time();
		} else if (time(0) - $last_ts > 30) { //30以上不变化，直接认为是挂了，重新下载
			$_params['status'] = FILE_S_DOWNLOAD;
			$this->unlockDownload();				
		}
		
		if ($lsize == $fileinfo['size']) {
			$_params['status'] = FILE_S_SHARED;
			$this->unlockDownload();			
		}		
		
		$res = $this->update($_params);
				
		
		return $res;
	}
		
	protected function timerProcessFileDownloading()
	{
		$res = false;
		$params = array('status'=>FILE_S_DOWNLOADING);
		$udb = $this->select($params);
		foreach ($udb as $key=>$v) {
			$this->checkDownloadingOne($v);			
		}	
		
		if (count($udb) == 0) {
			$mtime = $this->lockActiveTime('download');
			if ($mtime && (time() - $mtime > 60)) {
				$this->unlockDownload();				
			}
		}
		return $res;	
	}
	
	
	public function timerProcess()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		//转码
		$filter = array('status'=>FILE_S_CONVERT);
		$fdb = $this->select($filter); //待转码
		foreach ($fdb as $key=>$v) {
			if ($this->lock()) {
				$res = $this->timerProcessConvertVideo($v);
				if (!$res)			
					$this->unlock();
			}
		}
		
		//video 截图
		$filter = array('type'=>1,'status'=>FILE_S_NORMAL, 'snap_id'=>0);
		$fdb = $this->select($filter);		
		foreach ($fdb as $key=>$v) {
			if ($this->lock()) {
				$this->timerProcessSnapImageForVideoFile($v);
				$this->unlock();
			}
		}
		
		//更新转码服务是否正常并更新转码字节
		$res = $this->timerProcessCheckConvert();
		
		//下载
		$this->timerProcessFileDownload();
		
		//下载中检查状态
		$this->timerProcessFileDownloading();
		
		
		//同步
		//$res = $this->timerProcessFile2Org();
		
		//检查正在删除的文件
		$res = $this->timerProcessFileDeleted();
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "OUT");
		
		return $res;
		
	}	
	
	public function setNumDelta($id, $delta)
	{
		return $this->addN($id, 'used', $delta);
	}
		
	public function importFile($opath, $move=false, $_name='')
	{
		if (!file_exists($opath))	{
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no '$opath' failed!");
			return false;
		}

		$name = s_filename($opath);	
		
		$params = array();
		$params['pid'] = 0;
		$params['opath'] = $opath;
		$params['name'] = $name;
		$params['size'] = filesize($opath);
		
		$res = $this->initUploadParams($params);
		if (!$res)  {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "init upload params failed!", $params);
			return false;
		}
		!empty($_name) && $params['name'] = $_name;
		
		
		$dst = $params['dst'];		
		$tmpfile = $opath;		
		if (function_exists("move_uploaded_file") && $move) {
			$res = copy($tmpfile, $dst);
			if (!file_exists($dst)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "move file '$tmpfile' to '$dst' failed!");
				return false;
			}
			@unlink($tmpfile);
		} else {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "copy file to '$dst' ...");
			$res = copy($tmpfile, $dst);
			if (!file_exists($dst)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "copy file to '$dst' failed!");
				return false;
			}			
		}
		
		$size = filesize($dst);
		
		$params['size'] = $size;
		$params['tmp_name'] = 0;
		
		//video
		//需要转成mp4，无损，H5能直接播
		$this->checkFileVideoInfo($params);
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		$params['md5id'] = md5_file($dst);
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "set file failed!");
			return false;
		}
		
		return $params;
	}
	
	protected function checkFileType($src)
	{
		$extname = '';
		
		$szs = @getimagesize($src);
		
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $src, $szs);
		
		if ($szs) { //源文件是否存在
			/*
			(
			[0] => 800
			[1] => 450
			[2] => 3
			[3] => width="800" height="450"
			[bits] => 8
			[mime] => image/png
		)
		*/
			
			list($orig_width, $orig_height, $bigType) = $szs;
			$mimetype = $szs['mime'];
			
			
			switch ($bigType) {
				case 1: 
					$extname = "gif";
					break;	 
				case 2: 
					$extname = "jpg";
					break;	 
				case 3: 
					$extname = "png";
					break;
				default:
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Unkown cropping image type '$bigType', mimetype=$mimetype!");
					break;
			}
		}
		return $extname;
	}
	
	
	public function importUrl($url, $_name='', $options=array())
	{
		if (!$url) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid url '$url'!");
			return false;
		}
			
		if (is_start_slash($url))
			$url = $options['_rooturl'].$url;
		//640?wx_fmt=jpeg&amp;tp=webp&amp;wxfrom=5&amp;wx_lazy=1&amp;wx_co=1
		
		//解析url
		$filename = !empty($_name)?$_name:s_url2filename($url); //md5($url);
		
		//通过
		$res = $this->getOne(array('fromurl'=>$url));
		if (!$res) {
			
			$opath = RPATH_CACHE.DS.$filename;		
			$data = curlGET($url);
			if (!$data) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call curlGET from '$url' failed!");
				return false;
			}
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $data);
			//检查data模式
			
			$res = s_write($opath, $data);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call s_write failed!opath=$opath", $url);
				return false;
			}
			
			$extname1 = s_extname($filename);
			
			//检查文件类型
			$extname = $this->checkFileType($opath);
			if ($extname && $extname != $extname1) {
				$filename .= '.'.$extname;
				$newpath = RPATH_CACHE.DS.$filename;
				$res = s_rename($opath, $newpath);
				if ($res) {
					$opath = $newpath;
				}
			}
			
			!$_name && $_name = $filename;
			
			//再导入
			$res = $this->importFile($opath, true, $_name);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call importFile failed!opath=$opath");
				return false;
			}
			
			//更新 fromurl
			$_params = array();
			$_params['id'] = $res['id'];
			$_params['fromurl'] = $url;
			$this->update($_params);
		}		
		
		$this->formatForView($res, $options);
					
		return $res;
	}
		
	protected function doMoveTo($id, $new_pid)
	{
		$fileinfo = $this->get($id);	
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid id '$id'");
			return false;
		}
		
		if ($new_pid  < 0)
			$new_pid = 0;
		
		if ($new_pid  == $id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "parent is self!");
			return false;			
		}
		
		if ($new_pid  == $fileinfo['pid']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "new parent '$new_pid' is old '$fileinfo[pid]'!");
			return false;			
		}
		
		if ($new_pid > 0) {
			$res = $this->get($new_pid);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "new parent '$new_pid' not exists!");
				return false;
			}
			if ($res['isdir'] != 1) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "new parent '$new_pid' is not directory!");
				return false;
			}
		}
		
		//变更父目录
		$params = array();		
		$params['id'] = $id;
		$params['pid'] = $new_pid;
		$res = $this->update($params);
		
		return $res;
	}
	
	public function moveto($params, &$options=array())
	{
		if (!$params)
			return false;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$id = $params['id'];
		$new_pid = intval($params['new_pid']);
		
		if (is_array($id)) {
			foreach ($id as $key => $v) {
				$res = $this->doMoveTo($v, $new_pid);
			}
		} else {
			$res = $this->doMoveTo($id, $new_pid);
		}
		
		return $res;
	}
	
	
	
	protected function doCopyTo($id, $new_pid)
	{
		$fileinfo = $this->get($id);	
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid id '$id'");
			return false;
		}
		
		if ($new_pid  < 0)
			$new_pid = 0;
		
		if ($new_pid  == $id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "parent is self!");
			return false;			
		}
		
		if ($new_pid > 0) {
			$res = $this->get($new_pid);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "new parent '$new_pid' not exists!");
				return false;
			}
			if ($res['isdir'] != 1) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "new parent '$new_pid' is not directory!");
				return false;
			}
		}
		
		
		//查询一下当前目录下是否有此文件
		$filename = $fileinfo['filename'];
		$fileid = $this->genFileIDName($new_pid, $filename, '副本');
		
		//变更父目录
		$params = $fileinfo;	
		$params['fileid'] = $fileid;
		$params['pid'] = $new_pid;
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['status'] = FILE_S_SYMLINK;
		$params['target_id'] = $id;
		
		
		unset($params['id']);		
		$res = $this->set($params);
		
		//update
		if ($res) {
			$this->inc($id, 'used');
		}		
		
		return $res;
	}

	
	public function copyto($params, &$options=array())
	{
		if (!$params)
			return false;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$id = $params['id'];
		$new_pid = intval($params['target_id']);
		
		if (is_array($id)) {
			foreach ($id as $key => $v) {
				$res = $this->doCopyTo($v, $new_pid);
			}
		} else {
			$res = $this->doCopyTo($id, $new_pid);
		}
		
		return $res;
	}
	
	
	/**
	 * This is method rename
	 * 重命名
	 * 变更上级目录与文件名
	 *
	 * @param mixed $params This is a description
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function rename($params, &$options=array())
	{
		if (!$params)
			return false;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$id = $params['id'];
		$new_name= $params['new_name'];
		$new_pid = intval($params['new_pid']);
		
		
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		if (!$new_name){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no newname '$new_name'!", $params);
			return false;
		}
			
		$_params = array();
		$_params['id'] = $id;
		$_params['pid'] = $new_pid;
		$_params['name'] = $new_name;
		$_params['filename'] = $new_name;
		
		if (isset($params['filename']))
			$_params['filename'] = $params['filename'];

		$res = $this->update($_params);	
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call update failed!", $_params);
		}	
		
		return $res;
	}
	
	
	public function canUseFile($fileinfo)
	{
		if ($fileinfo['status'] == FILE_S_NORMAL 
				|| $fileinfo['status'] == FILE_S_LINK
				|| $fileinfo['status'] == FILE_S_SYMLINK
				|| $fileinfo['status'] == FILE_S_SHARED) {
			return true;
		}
		
		return false;
	}
	
	public function getForViewForUse($id, $options=array(), $ish5=true)
	{
		if (!$id)
			return false;
			
		$fileinfo = $this->getForView($id, $options);
		if (!$fileinfo) {
			//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;			
		}
		
		//状态
		if ($fileinfo['status'] != FILE_S_NORMAL 
				&& $fileinfo['status'] != FILE_S_LINK
				&& $fileinfo['status'] != FILE_S_SYMLINK
				&& $fileinfo['status'] != FILE_S_SHARED
				&& $fileinfo['status'] !== FILE_S_CONVERTED	) {
			//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid status '{$fileinfo['status']}'!");
			return false;
		}
		
		if ($fileinfo['status'] == FILE_S_CONVERTED && $ish5) {//已转码
			$fileinfo = $this->getForView($fileinfo['convert_id'], $options);
		}
		
		return $fileinfo;
	}
	
	
	public function shareFile($fid, $shared=true)
	{
		$fileinfo = $this->get($fid);
		if (!$fileinfo) {
			return false;
		}
		
		if ($shared) {
			$res = $this->inc($fid, 'shared');
		} else {
			$res = $this->dec($fid, 'shared');
		}
		
		if ($fileinfo['isdir']) {//目录
			$cdb = $this->getAllChildren($fid);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $cdb);
			
			foreach ($cdb as $key=>$v) {
				if ($shared) {
					$res1 = $this->inc($v['id'], 'shared');
				} else {
					$res1 = $this->dec($v['id'], 'shared');
				}
				
				if (!$res1) {
					$fun = ($shared)?"inc":"dec";
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call $fun(shared)  failed!");
				} else {
					rlog(RC_LOG_INFO, __FILE__, __LINE__, __FUNCTION__, "call $fun(shared) OK. id=".$v['id']);
				}
			}			
		}
		
		return $res;
	}
	
}