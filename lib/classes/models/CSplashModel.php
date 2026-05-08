<?php

/**
 * @file
 *
 * @brief 
 * 
 * 启动动画
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define('SPM_SPLASH', 1);
define('SPM_BACKGROUND', 2);

define('SPM_CTYPE_DEFAULT', 0);
define('SPM_CTYPE_IMAGE',   1);
define('SPM_CTYPE_VIDEO',   2);

class CSplashModel extends CTableModel
{
	
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'spm_splash';
		
		parent::__construct($name, $options);
	}
		
	public function CSplashModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f );
		
		switch ($f['name']) {
			case 'status':
				$f['edit'] = false;	
				$f['input_type'] = 'onoff';	
				break;		
			case 'type':
			case 'ctype':
				$f['input_type'] = 'selector';
				break;
			case 'imageurl':
				$f['input_type'] = 'image';
				break;				
			case 'videourl':
				$f['input_type'] = 'video';
				break;
			case 'aids':
				$f['input_type'] = 'GALLERY';
				break;			
			default:
				break;
		}

		return true;
	}

	public function formatForView(&$row, &$options=array())
	{
		parent::formatForView($row, $options);

		//$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		if ($row['ctype'] == SPM_CTYPE_IMAGE) {
			$imageurl = $row['imageurl'];
			if ($imageurl) {
				$row['_imageurl'] = "<img src='$imageurl' width='200'>";
			}
		} else {
			$row['_imageurl'] = "";
		}
		
		if ($row['ctype'] == SPM_CTYPE_VIDEO) {
			$videourl = $row['videourl'];
			if ($videourl) {
				$row['_videourl'] = "<video src='$videourl' width='200' controls />";
			}
		} else {
			$row['_videourl'] = "";
		}
		
		
	}

	public function getSplash($tid='', $options=array()) 
	{
		$splashinfo = $this->getOne(array('status'=>1)); 

		if ($splashinfo) {

			if ($splashinfo['type'] == 1) {
				$imageurl = $splashinfo['imageurl'];
				if (is_url($imageurl)) {
					$splashinfo['url'] = $imageurl;
				} else {
					$splashinfo['url'] = $options['_rooturl'].s_hslash($imageurl);
				}
			} else {
				$videourl = $splashinfo['videourl'];
				if (is_url($videourl)) {
					$splashinfo['url'] = $videourl;
				} else {
					$splashinfo['url'] = $options['_rooturl'].s_hslash($videourl);
				}
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $splashinfo);

		return $splashinfo;		
	}
	
	
	public function getDesktopBackground($params, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ..", $params);
		
		$bdb = array();		
		$bginfo = $this->getOne(array('type'=>SPM_BACKGROUND));
		if ($bginfo) {
			$name = '';
			$fdb = $this->getGalleryForSelected($bginfo['id'], 'aids', '', $options);
			
			foreach($fdb as $key=>$v) { 
				$item = array();
				$item['id'] = $v['id'];
				$item['url'] = $options['_rooturl'].$v['downloadUrl'];
				$item['mimetype'] = $v['mimetype'];
				
				$bdb[] = $item;
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $bdb);
		
		return $bdb;		
	}
}