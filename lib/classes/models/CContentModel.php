<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

/*
'sel_content_status' => array(
			'0'=>'默认', 
			'1'=>'发布', 
			'2'=>'撤销')*/
			
define ('CS_DEFAULT',	0);
define ('CS_RELEASE',	1);
define ('CS_CANCEL',	2);
define ('CS_PUBING',	3);


define ('CF_CHECKED',	0x1);
define ('CF_RELEASE',	0x2);
define ('CF_READONLY',  0x4);
define ('CF_SHARE',		0x8);
define ('CF_FLOWER',	0x10);
define ('CF_COMMENT',	0x20);

define ('CF_USER1',	0x40);
define ('CF_USER2',	0x80);
define ('CF_USER3',	0x100);

define ('CF_USER10',	1<<10); //头条
define ('CF_USER11',	1<<11); //站点推荐
define ('CF_USER12',	1<<12); //目录推荐
define ('CF_USER13',	1<<13); //特别推荐
	

define ('CF_MYFLAGS', ~(CF_CHECKED|CF_RELEASE|CF_READONLY));


class CContentModel extends CPubModel
{
	protected $_scf = array();
	protected $_catalogdb = array();

	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {			
			case 'id':
				$f['searchable'] = true;	
				break;		
			case 'icon':
			case 'photo':
				$f['input_type'] = 'image';
				$f['show'] = false;		
				break;
			case 'video':
				$f['input_type'] = 'video';
				$f['show'] = false;	
				break;
			case 'cid':
				$f['treemodel'] = 'catalog';	
				$f['searchable'] = 2;	
				$f['default'] = true;	
				break;
			case 'mid':
				$f['input_type'] = 'model';
				$f['model'] = 'model';
				$f['default'] = true;	
				$f['show'] = false;	
				break;	
			case 'oid':
				$f['input_type'] = 'model';
				$f['model'] = 'org';
				$f['show'] = false;	
				$f['edit'] = false;	
				//$f['searchable'] = 2;	
				break;	
			case 'author':				
			case 'editor':				
			case 'refer':				
				$f['show'] = false;	
				$f['input_type'] = 'varvalselector';
				$f['addon'] = true;
				break;			
			case 'content':
				$f['input_type'] = 'ckeditor';
				$f['show'] = false;	
				break;
				
			case 'tpl_content':
				$f['edit'] = false;				 
			case 'description':
			case 'summary':
				$f['show'] = false;		
				break;
			case 'aids':
				$f['input_type'] = 'gallery';
				$f['show'] = false;	
				$f['noclickedit'] = true;	
				
				break;
			case 'link':
				$f['input_type'] = 'link';
				$f['show'] = false;
				break;
			case 'comments':
			case 'cached':
			case 'cmts':
				$f['show'] = false;
			case 'hits':
				$f['edit'] = false;
				break;
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;
				$f['edit'] = false;				
				$f['show'] = false;
				
				break;
			case 'uid':
				$f['input_type'] = 'UID';
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly'] = true;	
				$f['show'] = false;			
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'status':
				$f['input_type'] = 'selector';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				//$f['sortable'] = true;
				$f['show'] = false;
				break;			
			case 'taxis':
				$f['input_type'] = "sort";
				$f['sortable'] = true;
				$f['edit'] = false;
				$f['clickedit'] = true;
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
		
		return $id;
	}
	
	public function getCount($params=array())
	{
		return $this->count('id',$params);
	}
	
	public function getSumHits($params=array())
	{
		return $this->sum('hits',$params);
	}
	
	
	protected function parseFilterParams($params, $strict=false)
	{
		//cid
		if (isset($params['cid']) && $params['cid'] == 0)
			unset($params['cid']);
		
		$res = parent::parseFilterParams($params, $strict);
		return $res;
	}
	
	
	protected function formatContentUrl(&$row, &$options = array())
	{
		if (!empty($row['link'])) { //链接
			$row['url'] = $row['link'];
			$row['target'] = "_blank";
		} else {
			$row['url'] = $options['_webroot'].'/content/'.$row['id'];
		}
	}
	
	protected function getFileModel()
	{
		return Factory::GetModel('file');
	}
	
	public function formatForView(&$row, &$options = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $row['id'], $row['name'], $row['photo']);
		parent::formatForView($row, $options);
		
		$id = $row['id'];
		//status
		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		//$row['_taxis'] = "<input type='text' name='params[taxis][$id]' value='$row[taxis]' class='form-control input-xsmall' />";
		
		$__ctype = 0;

		$extinfo = '';		
		
		
		$this->formatContentUrl($row, $options);
		$url = $row['url'];		
		
		//photo
		$photo = trim($row['photo']);
		if (!$photo) {
			$photo = $options['_dstroot'].'/img/nopic.png';
		} else {
			$__ctype |= FT_IMAGE;
			$extinfo .= " <a href='$row[photo]' target=_blank class='gallery-img' data-gallery1='$row[photo]' data-id='$id' data-noabar=1 data-norequest=1> <i class='fa fa-image'></i></a>";	
		}
		
		if (is_url($photo)) {
			$row['photoUrl'] = $photo;
		} else {
			$row['photoUrl'] = $options['_rooturl'].s_hslash($photo);
		}	
		$row['_photo'] = "<img src='$row[photoUrl]' style='width:100%;'/>";
		
		//previewUrl for listview
		if (isset($row['photo__previewUrl'])) { 
			$row['previewUrl'] = $row['photo__previewUrl']; 
		} else {
			$row['previewUrl'] = $row['photoUrl'];
		}
		
		
		//video
		$videoUrl = trim($row['video']);
		if ($videoUrl) {
			if (is_url($videoUrl)) {
				$row['videoUrl'] = $videoUrl;
			} else {
				$row['videoUrl'] = $options['_rooturl'].s_hslash($videoUrl);	
			}
			$row['_video'] = $row['videoUrl'];
			$row['playurl'] = $row['videoUrl'];
			$row['previewLargeUrl'] = $row['previewUrl']; //视频封面
			$__ctype |= FT_VIDEO;
			
			$extinfo .= " <a href='$url' target=_blank data-url='$videoUrl' class='videobox' data-id='$id'><i class='fa fa-film'></i></a>";		
		}


		$row['_extinfo'] = $extinfo;
		$row['__ctype'] = $__ctype;
		
		//$cid = $row['cid'];
		//$row['cid'] = $this->formatModelForList($row, $fields['cid'], $options);
		//$row['_cid'] = $cid;

				
		$name = $row['name'];
		
		$row['_name'] = " <a href='$url' target='_blank'> $name </a>";
		$row['title'] = $name;
		
		
		
	}
		
	protected function processDelContent($old)
	{
		$id = $old['id'];
		
		//解除模块发布
		$m0 = Factory::GetModel('content2module');
		$m0->delete(array('cid'=>$id));
		
		
		//解除模型
		$m = Factory::GetModel('content2model');
		$m->delete(array('cid'=>$id));		
		
	}
	
	public function del($id, &$options=array())
	{
		$old = parent::del($id, $options);
		if ($old) {
			//查询引用的图片，解除引用
			$this->processDelContent($old);
		}		
		return $old;
	}
	
	
	
	/*
	1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
	*/
	protected function get_fid_by_url($url, &$fileinfo=array())
	{
		$m = $this->getFileModel();		
		$fileinfo = $m->getFileInfoByUrl($url);
		if ($fileinfo) {
			return $fileinfo['id'];
		}	
		return 0;
	}
	
	public function getWebPlayUrl($url, &$fileinfo=array())
	{
		if (is_url($url)) 
			return $url;
		$fid = 	$this->get_fid_by_url($url, $fileinfo);
		if (!$fid) //unknown url
			return $url;
		$m = $this->getFileModel();		
		
		$playurl =  $m->getPlayUrl($fileinfo);
		
		return $playurl;
	}

	protected function parseInputPostParamsForRichEdit($field, &$params, &$options=array())
	{
		$fdb = parent::parseInputPostParamsForRichEdit($field, $params, $options);
		
		if (!$fdb) 
			return false;
		
		$selectimage = $params['selectimage'];
		$firstimg = '';
		$firstvideo = '';
				
		$m = $this->getFileModel();
		foreach ($fdb as $key=>$v) {
			$fileinfo = $v;
			if ($m->is_image($fileinfo)) { //图片
				if ($selectimage && !$firstimg) {
					$firstimg = $fileinfo['url'];
				};
			}
			
			if ($m->is_av($fileinfo)) { //音视频
				$firstvideo = $fileinfo['playurl'];						
			}
		}
		
		$photo = $params['photo'];
		$id = $params['id'];
		
		if ($firstimg && !$photo) {
			$_params['id'] = $id;
			$_params['photo'] = $firstimg;
			$this->update($_params);
		}
		
		//$firstvideo
		if ($firstvideo && !$video) {
			$_params['id'] = $id;
			$_params['video'] = $firstvideo;			
			$this->update($_params);
		}
		
		return $fdb;
		
	}
			
	
	//管理文件引用
	protected function processPostContent__unused($params, &$options=array())
	{
		$id = $params['id'];
		$selectimage = $params['selectimage'];
		
		$content = $params['content'];
		$photo = $params['photo'];
		$firstimg = '';
		$firstvideo = '';
		
		$m = $this->getFileModel();
		$nums = array();
		
		$_content = stripslashes($content);
		$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_content, $urls);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $urls);		
		if ($res && count($urls[1]) > 0) {
			
			foreach ($urls[1] as $key=>$v) {
				//fid
				$fileinfo = $m->getFileInfoForViewByUrl($v, $options);
			
				if ($fileinfo) {
					$fid = $fileinfo['id'];
					
					if (isset($nums[$fid]))
						$nums[$fid] ++;
					else 
						$nums[$fid] = 1;
						
					if ($m->is_image($fileinfo)) { //图片
						if ($selectimage && !$firstimg) {
							$firstimg = $fileinfo['url'];
						};
					}
					if ($m->is_av($fileinfo)) { //音视频
						$firstvideo = $fileinfo['playurl'];						
					}
				}
				
			}
		}
		
		if ($firstimg && !$photo) {
			$_params['id'] = $id;
			$_params['photo'] = $firstimg;
			$this->update($_params);
		}
		
		$m2 = Factory::GetModel('file2model');
		
		if ($photo) {
			$fid = $this->get_fid_by_url($photo);
			if ($fid) {
				$m2->setFile2Model($fid, 'photo', $this->_name, $id); 
			}			
		}
		
		//$firstvideo
		if ($firstvideo && !$video) {
			$_params['id'] = $id;
			$_params['video'] = $firstvideo;			
			$this->update($_params);
		}
		
		if ($video) {
			$fid = $this->get_fid_by_url($video);
			if ($fid) {
				$m2->setFile2Model($fid, 'video', $this->_name, $id); 
			}			
		}
						
		//查询附件引用
		$pattern = "/\[attach\](\d+)\[\/attach\]/i";		
		$res = preg_match_all($pattern, $_content, $attachs);
		//rlog($attachs); 
		if ($res && count($attachs[1]) > 0) {
			foreach ($attachs[1] as $key=>$fid) {
				if (isset($nums[$fid]))
					$nums[$fid] ++;
				else 
					$nums[$fid] = 1;
			}
		}
		
		//
		$pattern = "/\[attach\]([0-9a-fA-F]{32})\[\/attach\]/i";		
		$res = preg_match_all($pattern, $_content, $attachs);
		//rlog($attachs); 
		if ($res && count($attachs[1]) > 0) {
			foreach ($attachs[1] as $key=>$fileid) {
				$finfo = $m->getFileInfoByFileID($fileid);
				if ($finfo) {
					$fid = $finfo['id'];
					
					if (isset($nums[$fid]))
						$nums[$fid] ++;
					else 
						$nums[$fid] = 1;
				}
			}
		}
		
		//content
		$m2->delFile2ModelByModelField('content', $this->_name, $id);		
		foreach ($nums as $fid=>$num) {
			$m2->setFile2Model($fid, 'content', $this->_name, $id); 
		}
		
		//aids
		$aids = $params['aids'];
		$adb = explode(',', $aids);
		foreach ($adb as $key=>$fileid) {
			$finfo = $m->getFileInfoByFileID($fileid);
			if ($finfo) {
				$m2->setFile2Model( $finfo['id'], 'aids', $this->_name, $id); 
			}
		}
		
		
		//$options['data'] = array('autobackurl'=>$options['_base'].'?id='.$params['cid']);
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  __FUNCTION__, $nums, $photo);
				
		
		return true;		
	}

	//图片本地化
	public function image2local($content, &$options=array(), $with_prefix='')
	{
		// change '\\' to '\'
		$_content = stripslashes($content);
		//preg_match_all("/<img[^>]*src=[\s]*\"(http:\/\/.+\.(jpg|gif|bmp|bnp))[\s]*\"/i", $data, $images);
		//preg_match_all("/<img[^>].*src=.*(http[s]?:\/\/.+\.(jpg|gif|bmp|png))/i", $content, $images);
		preg_match_all("/<img[^>]*src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_content, $images);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $images);
		
		$src = array();
		$new = array ();
		
		$m = $this->getFileModel();
		
		$firstimg = '';
				
		foreach($images[1] as $key =>$v)
		{
			$srcurl = trim($v);
			/*//http https
			if (!is_url($srcurl)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "src not url '$srcurl'!");
				continue;			
			}*/
			
			//检查是否为本地图片
			$finfo = $m->getFileInfoByUrl($srcurl);			
			if ($finfo) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "skip local image '$srcurl' ");
				continue;
			}
			
			$finfo = $m->importUrl($srcurl, '',  $options);	
			if (!$finfo) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call importUrl  '$srcurl' failed! ");
				continue;
			}	
			
			$src[] = $srcurl;			
			$new[] = $with_prefix.$finfo['url'];
			
			if (!$firstimg) 
				$firstimg = $with_prefix.$finfo['url'];
		}
		if ($new) {
			$_content = str_replace($src, $new, $content); //再把内容中图片地址更换成对应的本地图片地址
			if ($firstimg)			
				$options['firstimg'] = $firstimg;
		}
		
		return $_content;		
	}

	public function getFieldsForInput($params=array(), &$options=array())
	{
		//初始化
		if (isset($params['modname']) && empty($params['id'])) {
			$m = Factory::GetModel($params['modname']);
			$info = $m->get($params['mid']);
			$params = array_merge($params, $info);
			//title特别处理
			if (isset($info['title']))
				$params['name'] = $info['title'];
		}
		
		$fdb = parent::getFieldsForInput($params, $options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
				
		
		return $fdb;
	}
	
	
	public function getModelFieldsForInput($params, &$options=array())
	{
		$modname = $params['modname'];
		$mid = $params['mid'];
		
		$m = Factory::GetModel($modname);		
		$_params = $m->get($mid);
		
		$udb =  $m->getFieldsForInput($_params, $options);		
		$fdb = array();
		foreach ($udb as $key=>$v) {
			if (!isset($this->_fields[$key]) && $v['edit']) {
				$fdb[] = $v;
			}
		}				
		return $fdb;
	}
	
	public function getModelFieldsForDetail($params, &$options=array())
	{
		if (empty($params['modname']))
			return false;
		$modname = $params['modname'];
		$mid = $params['mid'];
		
		$m = Factory::GetModel($modname);		
		$_params = $m->get($mid);
		
		$udb =  $m->getFieldsForDetail($_params, $options);		
		$fdb = array();
		foreach ($udb as $key=>$v) {
			if (!isset($this->_fields[$key]) && $v['detail']) {
				$fdb[] = $v;
			}
		}				
		return $fdb;
	}
	
	
	
	
	protected function initAddParams(&$params=array(), &$options=array())
	{
		$params['taxis'] = 100;
	}
	
	protected function checkParams(&$params, &$options=array())
	{
		//处理富文本框图片
		$imagetolocal = isset($params['imagetolocal'])?1:0;
		$selectimage = isset($params['selectimage'])?1:0;
		
		if ($imagetolocal) { //图片本地化
			$res = $this->image2local($params['content'], $options);
			if ($res) {
				$params['content'] = $res;
				if ($selectimage && isset($options['firstimg'])) {
					$params['photo'] = $options['firstimg'];
				}
			}
		}
		
		//cid
		if (isset($params['cid']) && !is_numeric($params['cid'])) {
			$m = Factory::GetModel('catalog');			
			$res = $m->getByName($params['cid']); 
			if ($res) {
				$params['cid'] = $res['id'];
			} 
		}
		$res = parent::checkParams($params, $options);		
		
		return $res;
	}
	 
	protected function setContent2model($params, $options)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...", $params);
		
		if (empty($params['modname'])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no modname!");
			return false;
		}
			
		$modname = $params['modname'];		
		$mid = $params['mid'];	
		$cid = $params['id'];		
		
		$m = Factory::GetModel('content2model');
		$m2cinfo = $m->getOne(array('cid'=>$cid));
		if (!$m2cinfo) {			
			//关联
			$_params = array();
			$_params['modname'] = $params['modname'];
			$_params['mid'] = $mid;
			$_params['cid'] = $cid;			
			$res = $m->set($_params);				
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set content2model failed!", $_params);
				return false;
			}
		}
		
		//冲突字段不更新
		foreach($this->_fields as $key=>$v) {
			if (isset($params[$key]))
				unset($params[$key]);
		}
		
		$m2 = Factory::GetModel($modname);
		$params['id'] = $mid;
		$res = $m2->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set model '$modname' failed!");
			return false;
		}
		return $res;
	}
	
	
	
	
	public function set(&$params, &$options=array())
	{
		
		$res = parent::set($params, $options);
		
		if ($res) { //模型
			/*if (isset($params['mid']) && $params['mid'] > 0) {
				$this->setContent2model($params, $options);				
			}	*/		
			//if (isset($params['content']))		
			//	$this->processPostContent($params, $options);

			$this->triggerContent($params['id'], true);
		}	
			
		return $res;
	}

	
	public function getContentListBy($where, $nr=0, &$options=array())
	{
		$udb = $this->gets($where, $nr);
		
		$fields = $this->getFields();
		foreach($udb as $key=>&$v) {
			//$this->prev_content_single($v, $fields, $options);
			$this->filterForListForFrontend($v, $fields, $options);			
		}
		
		return $udb;
	}

	/*public function selectForListview(&$params, &$options=array())
	{
		//filterfieldcfg
		$fields = array();
		$ffcfg = isset($params['filterfieldcfg'])?$params['filterfieldcfg']:array(
			'id'=>array(), 
			'title'=>array());

		if ($ffcfg) {
			foreach ($this->_fields as $key=>$v) {
				if (array_key_exists($key, $ffcfg)) {
					$v['sortable'] = $v['sortable']?true:false;
					$fields[$key] = $v;
				}
			}
		} else {
			$fields = $this->_fields;
		}
		
		//where
		if (isset($_REQUEST['params']))
			$params = array_merge($params, $_REQUEST['params']);
		
		//filter rows 
		$org_rows = $this->selectForView($params, $options);
		$rows = $params['rows'];
		
		foreach ($rows as $key=>&$v) {
			//$v['name'] = $v['title'];
			$v['time'] = tformat_timelong($v['ts']);
			
			//previewUrl
			$previewUrl = $v['photo'];
			if (!$previewUrl) { //nopic
				$previewUrl = $options['_dstroot'].'/img/nopic.png';
			} else {				
				$pattern = "/f\//i";
				$replacement = '${0}/preview/';
				$previewUrl = preg_replace($pattern, $replacement, $previewUrl);
			}
			$v['previewUrl'] = $previewUrl;			
		}
		
			
		$data = array(
				'listview'=> array(
					'name'=>$this->_name,
					'fields'=>$fields,
					'total'=>$params['total'],
					'page'=>$params['page'],
					'pages'=>$params['pages'],
					'page_size'=>$params['page_size'],
					'num'=>$params['num'],
					'sort'=>$params['sort'],
					'order'=>$params['order'],
					'rows'=>$rows
					)
				);
		return $data;		
		
	}*/
	
	

	public function getForView($id, &$options = array())
	{
		$res = parent::getForView($id, $options);
		return $res;
	}
	
	
	
	public function getForWebview($id)
	{
		$cinfo = $this->get($id);
		if (!$cinfo)
			return false;
		
		//filter video tag
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO filter video tag");
		
		return $cinfo;
	}
	


	protected function is_mycontent($cinfo)
	{
		$myinfo = get_userinfo();
		return $myinfo['uid'] == $cinfo['cuid'];
	}



	public function setMyFlags($id, $flagsMask)
	{
		$cinfo  = $this->get($id);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no content id '$id'");
			return false;
		}

		if (!$this->is_mycontent($cinfo)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not my content!");
			return false;
		}

		$old = $cinfo['flags'];
		if ($old & CF_CHECKED) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid old '$old'!");
			return false;
		}
		if ($flagsMask & ~CF_MYFLAGS) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "cannot set CF_MYFLAGS for self!");
			return false;
		}

		$new = $old ^ $flagsMask; //指定位取反
		$new &= ~CF_CHECKED;

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id, statusMask=$flagsMask, oldstatus=$old, newstatus=$new");

		$params = array();
		$params['id'] = $id;
		$params['flags'] = $new;
		$res = $this->update($params);
		

		return $res;
	}
	
	
	protected function changeFlagsForUpdateStatus($cinfo, $edit=false)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...");
		$flags = intval($cinfo['flags']);
		
		$status =  $cinfo['status'];
		if (($flags & (CF_CHECKED|CF_RELEASE)) == (CF_CHECKED|CF_RELEASE)) {
			$status = 1;
		} else {
			if ($cinfo['status'] == 1) { //原先是发布，改为撤销
				$status = 2;
			} 
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "flags=$flags, IN2 status=$status, cinfo[status]=$cinfo[status].");
		
		$res = true;
		
		if ($status != $cinfo['status']) {
			$_params = array();
			$_params['id'] = $cinfo['id'];
			$_params['status'] = $status;	
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params);
				
			$res1 = $this->update($_params);
			if (!$res1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call update failed!", $_params);
			}
			
			//更新引用分享
			$m = Factory::GetModel('file2model');
			$res2 = $m->shareFile2Model($status == 1, $this->_modname, $cinfo['id']);
			
			$res = $res1 || $res2;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.res=$res");
		
		return $res;
	}
	
	protected function triggerContent($id, $edit=false)
	{
		$info = $this->get($id);	
		if ($info) { 
			$m = Factory::GetModel('content2module');
			$m->trigger('change', $info);			
			
			//更新status
			$this->changeFlagsForUpdateStatus($info, $edit);			
		}	
	}

	public function mck($id, $flagsMask, $fieldname='', $options=array())
	{
		$res = parent::mck($id, $flagsMask, $fieldname, $options);
		if ($res) {//flags变动
			$this->triggerContent($id);
		}
		
		return $res;
	}
	
	
	public function getAidsdbByInfo($cinfo, &$options=array())
	{
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return array();
		}
		
		$fdb = array();
		$aids = $cinfo['aids'];
		
		
		if ($aids) {
			$m = $this->getFileModel();
			$aidsdb = explode(',', $aids);
			
			$udb = array();			
			foreach ($aidsdb as $aid) {
				if (is_md5($aid)) {
					$fileinfo = $m->getFileInfoByFileIDForView($aid, $options);
				} else {
					$fileinfo = $m->getForView($aid, $options);
				}
				
				$udb[] = $fileinfo;
			}			
			if (!$udb) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: invalid aids '$aids'!");
				return false;
			}
			
			
			foreach($udb as $key=>$v) {
				if ($v['isdir']) { //目录	
					$udb2 = $m->gets(array('pid'=>$v['id'],'type'=>1, 'status'=>1)); //"where pid=$pid and type=1 and status=1", $options);
					foreach ($udb2 as $k2=>$v2) {
						$m->formatForView($v2, $options);
						$fdb[] = $v2;
					}
				} else if ($v['type'] == 1 && $v['status'] == 1){
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

	public function getAidsdb($id, &$options=array())
	{
		$cinfo = $this->get($id);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return array();
		}

		$fdb = $this->getAidsdbByInfo($cinfo, $options);
		return $fdb;
	}
	
	
	public function getListFromContent2Module($params=array(), $nr=0, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ...", $params);	
		
		$m = Factory::GetModel('module');
		$mid = $params['mid'];
		$minfo = $m->getOne(array('mid'=>$mid));
		if (!$minfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no MID '$mid'!");
			return array();
		}
		
		$_c2m_id = $minfo['id'];
		$m2 = Factory::GetModel('content2module');
		
		$_params = array('mid'=>$_c2m_id);
		if ($nr > 0)		
			$_params['limit'] = $nr;
		
		$udb = $m2->select($_params);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $_params, $udb);	
		
		$m3 = Factory::GetModel('catalog');
		
		$cdb = array();
		foreach($udb as $key=>$v) {
			$content_id = $v['cid'];
			$info = $this->get($content_id);
			if (!$info) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown content_id '$content_id'!");
				continue;
			}
			
			//按CATALOG_ID过滤
			if (isset($params['cid']) && $params['cid'] > 0 ) {
				//指定模块参数				
				if ($minfo['cid'] > 0 && $minfo['cid'] != $params['cid']) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "minfo[cid]($minfo[cid]) != params[cid]($params[cid]), skip content_id '$content_id'!");
					continue;
				} else if ($minfo['cid'] == 0 && $params['cid'] != $info['cid']) { //模块未指定，内容过滤
					//查一下当前cid是不是父目录
						$found = false;
						$pdb = array();
						$m3->getParents($info['cid'], $pdb);
						foreach ($pdb as $k2=>$v2) {
							if ($v2['id'] == $params['cid']) {
								$found = true;								
							}
						}
						if (!$found) {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "info[cid]($info[cid]) != params[cid]($params[cid]), skip content_id '$content_id'!");
							continue;
						}
				}			
			}
			
			$cdb[$v['cid']] = $info;
		}

		array_sort_by_field($cdb, 'taxis', true);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ...", $cdb);	
		
		return $cdb;		
	}
	
	public function getList($params=array(), $nr=0, &$options=array())
	{
		$params['status'] = 1; //发布
		
		$udb = array();
		if (isset($params['mid'])) {
			$udb = $this->getListFromContent2Module($params, $nr, $options);	
		} else {
			$udb =  parent::getList($params, $nr, $options);
		}

		foreach($udb as $key=>&$v) {
			$this->formatForView($v, $options);
		}
		
		return $udb;
	}
	
	public function getListForFrontend($params=array(), $nr=0, &$options=array())
	{
		$udb = $this->getList($params, $nr, $options);
		
		foreach ($udb as $key=>&$row) {
			$time_format = isset($options['time_format'])?$options['time_format']:'Y-m-d';
			$maxlen = isset($options['maxlen'])?$options['maxlen']:128;
			
			$row['show_time'] = tformat($row['ts'], $time_format);
			$row['longtime'] = tformat_timelong($row['ts']);
			$row['subtitle'] = $row['title'];
			if ($maxlen > 0) {
				$row['subtitle'] = utf8_substr($row['subtitle'], 0, $maxlen);			
			}
			
		}				

		return $udb;
	}
	
	public function getDigestByTTag($ttag, $nr=0, $options=array())
	{
		$udb = array();		
		$m = Factory::GetModel('module');
		$minfo = $m->getOne(array('title'=>$ttag));
		if ($minfo) {
			$mid = $minfo['mid'];
			$_params = array();
			$_params['mid'] = $mid;
			$udb = $this->getListForFrontend($_params, $nr, $options);
		}
		return $udb;
	}	
	
	public function getDigestByMID($mid, $nr=0, $options=array())
	{
		$mid = $minfo['mid'];
		$_params = array();
		$_params['mid'] = $mid;
		$udb = $this->getListForFrontend($_params, $nr, $options);
		
		return $udb;
	}	
	
	
	public function getPubtoForView($id, $options=array())
	{
		$params = parent::getPubtoForView($id, $options);
		
		//media_platform
		$to_modname = $this->getToModname('media_platform');
		if (is_model($to_modname)) {
			$this->getPubtoList($id, $to_modname, $params, $options);			
		}
		
		
		return $params;
	}
	
	
	public function getPubtoPlatform($content_id)
	{
		$pdb = array();
		$mdb = array();
		
		if (is_model('media_platform')) {
			$m = Factory::GetModel('media_m2m');
			$m2 = Factory::GetModel('media_platform');
			$pdb = $m2->gets();
			$mdb = $m->gets(array('modname'=>$this->_name, 'mid'=>$content_id));
		}
		
		$c2p = array();
		foreach ($mdb as $key=>$v) {
			$c2p[$v['platform_id']] = $v;
		}
		
		foreach ($pdb as $key => &$v) {
			$v['disable'] = $v['status'] == 1?'':'disabled';
			$v['checked'] = isset($c2p[$v['id']])?'checked':'';
		}
		
		return $pdb;
	}
	
	protected function setFlags($id, $flags)
	{
		
		$old = $this->get($id);
		if (!$old)
			return false;
		
		$res = parent::setFlags($id, $flags);
				
		if ($res && $old['flags'] != $flags) {//flags变动
			
			$this->triggerContent($id);
		}
		
		return $res;
	}
	
	
	
	
	protected function preparePubtoMediaPlatform(&$pubtoinfo, &$contentinfo, $options)
	{
		$m = Factory::GetModel('file');
		
		//photo
		$photo = $contentinfo['photo'];
		if (!$photo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no photo!");
			return false;
		}
		
		//本地化
		//检查是否为本地图片
		$finfo = $m->getFileInfoByUrl($photo);
		if (!$finfo) {
			$finfo = $m->importUrl($photo, '',  $options);	
			if (!$finfo) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call importUrl  '$photo' failed! ");
				return false;
			}
			$contentinfo['photo'] = $finfo['url'];	
		}
		
		//内容中的图片本地化
		$contentinfo['content'] = $this->image2local($contentinfo['content'], $options);
		
		return true;
	}
	
	
	
	protected function addWebPrefix($src, $webprefix='', &$usefiles=array(), $options)
	{
		if ($src) {
			
			//本地加前缀
			$m = Factory::GetModel('file');		
			$finfo = $m->getFileInfoByUrl($src);
			if (!$finfo) {
				$finfo = $m->importUrl($src, '',  $options);	
				if ($finfo) {
					$src = $finfo['url'];					
				}
			}
			
			if ($finfo) {
				$src = $webprefix.'/f/'.$finfo['fileid'].'/'.urlencode($finfo['filename']);				
				
				$finfo['fromurl'] = $src;
				$usefiles[$finfo['fileid']] = $finfo;
			}
		}
		
		return $src;
		
	}
	
	
	protected function addAttachs($aids, $webprefix='', &$usefiles=array())
	{
		if ($aids) {
			$m2 = Factory::GetModel('file');		
			
			$adb = explode(',', $aids);
			foreach ($adb as $key=>$v) {
				//本地加前缀
				$finfo = $m2->getFileInfoByFileID($v);
				if ($finfo) {
					$src = $webprefix.'/f/'.$finfo['fileid'].'/'.urlencode($finfo['filename']);				
					
					$finfo['fromurl'] = $src;
					$usefiles[$finfo['fileid']] = $finfo;
				}
			}
		}		
	}
	
	/**
	 * preparePubtoContent
	 *
	 * 发布到节点前准备相应资源
	 * 
	 * @param mixed $contentinfo This is a description
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function preparePubtoNodePlatform(&$pubtoinfo, &$contentinfo, $options)
	{
		$cf = get_config();
		$webprefix = $cf['webprefix'];
	
		$usefiles = array();
		
		//照片预处理
		$contentinfo['photo'] = $this->addWebPrefix($contentinfo['photo'], $webprefix, $usefiles, $options);
		//视频预处理
		$contentinfo['video'] = $this->addWebPrefix($contentinfo['video'], $webprefix, $usefiles, $options);
		
		//内容中的图片本地化
		$_content = $contentinfo['content'];
		$contentinfo['content'] = $this->image2local($_content, $options, $webprefix);
		
		//附件,长度为32个十六进制数
		$attachs = array();
		$pattern = "/\[attach\]([0-9a-fA-F]{32})\[\/attach\]/";		
		$res = preg_match_all($pattern, $_content, $attachs);
		if ($res && count($attachs[1]) > 0)
		{
			$m = Factory::GetModel('file');
			
			$nr = count($attachs[1]);			
			for ($i=0; $i<$nr; $i++) {
				$aid = $attachs[1][$i];
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $aid);
				
				$finfo = $m->getFileInfoByFileIDForView($aid, $options);
				if ($finfo) {
					$src = $webprefix.'/f/'.$finfo['fileid'].'/'.urlencode($finfo['filename']);				
					
					$finfo['fromurl'] = $src;
					
					$usefiles[$finfo['fileid']] = $finfo;
				}	
			}
		}
		
		
		
		$contentinfo['content'] = $_content;
		
		//集附件		
		$this->addAttachs($contentinfo['aids'], $webprefix, $usefiles);
		
		$contentinfo['usefiles'] = $usefiles;
		$contentinfo['_usefiles'] = serialize($usefiles);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $contentinfo);
		
		
		return true;
	}
	
	public function preparePubtoForModel(&$pubtoinfo, &$cinfo, $params, $options)
	{
		parent::preparePubtoForModel($pubtoinfo, $cinfo, $params, $options);
		
		$to_modname = $pubtoinfo['to_modname'];
		
		//准备
		switch ($to_modname) {
			case 'media_platform':
				$res = $this->preparePubtoMediaPlatform($pubtoinfo, $cinfo, $options);
				break;
			default:
				$res = $this->preparePubtoNodePlatform($pubtoinfo, $cinfo, $options);
				break;
		}
		
		return $res;
	}
	
	
	protected function prepareSyncCluster_set(&$params, $options=array())
	{
		
		parent::prepareSyncCluster_set($params, $options);
		
		$cf = get_config();
		$webprefix = $cf['webprefix'];
		
		$usefiles = array();
		
		//照片预处理
		if (isset($params['photo']))
			$params['photo'] = $this->addWebPrefix($params['photo'], $webprefix, $usefiles, $options);
		//视频预处理
		if (isset($params['video']))
			$params['video'] = $this->addWebPrefix($params['video'], $webprefix, $usefiles, $options);
		
		//内容中的图片本地化
		if (isset($params['content'])) {
			$_content = $params['content'];
			$params['content'] = $this->image2local($_content, $options, $webprefix);
			
			//附件,长度为32个十六进制数
			$attachs = array();
			$pattern = "/\[attach\]([0-9a-fA-F]{32})\[\/attach\]/";		
			$res = preg_match_all($pattern, $_content, $attachs);
			if ($res && count($attachs[1]) > 0)
			{
				$m = Factory::GetModel('file');
				
				$nr = count($attachs[1]);			
				for ($i=0; $i<$nr; $i++) {
					$aid = $attachs[1][$i];
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $aid);
					
					$finfo = $m->getFileInfoByFileIDForView($aid, $options);
					if ($finfo) {
						$src = $webprefix.'/f/'.$finfo['fileid'].'/'.urlencode($finfo['filename']);				
						
						$finfo['fromurl'] = $src;
						
						$usefiles[$finfo['fileid']] = $finfo;
					}	
				}
			}
			
			
			
			$params['content'] = $_content;
		}
		
		//集附件
		if (isset($params['aids'])) 
			$this->addAttachs($params['aids'], $usefiles);
		
		$references = array();
		if ($usefiles) {
			//file
			$references['file'] = $usefiles;
			//fiie2model
			//
		}
		
		$params['_references'] = serialize($references);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		
		return true;
		
	}
		
	
	protected function postSyncClusterForSetReferFile($fdb, $params, $options)
	{
		$m = Factory::GetModel('file');
		
		foreach ($fdb as $key=>$v) {
			unset($v['id']);
			unset($v['status']);			
			$old = $m->getOne(array('fileid'=>$v['fileid']));
			if ($old) {
				$v['id'] = $old['id'];
			} else { //连接
				$v['status'] = FILE_S_LINK;
			}
			
			$res = $m->set($v);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call set failed!", $v);
			}
		}
	}
	
	
	protected function processSyncClusterForSet($params, $options)
	{
		if (isset($params['_references'])) {
			foreach ($params['_references'] as $key=>$v) {
				switch($key) {
					case 'file'://文件引用
						$this->postSyncClusterForSetReferFile($v, $params, $options);						
						break;
					default:
						break;
				}
			}
		}
	}
	
	
	protected function processSyncCluster_set($params, $options=array())
	{	
		$res = parent::processSyncCluster_set($params, $options);
		
		
		//处理关联
		if ($res)
			$res2 = $this->processSyncClusterForSet($params, $options);		
		
		return $res;
	}
	
	
	
	
	
	protected function doPubtoMediaOne($platform_id, $cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		
		//准备
		$this->preparePubtoContent($cinfo, $options);
		
		//预览
		$preview = isset($params['preview'])?intval($params['preview']):0;
		//发表
		$release = isset($params['release'])?intval($params['release']):0;
		//群发通知
		$notify = isset($params['notify'])?intval($params['notify']):0;
		
		
		$m = Factory::GetModel('media_platform');
		$m2 = Factory::GetModel('media_content');
		$m3 = Factory::GetModel('media_m2m');
			
		//查询一下是否已有提交
		$m2minfo = $m3->getOne(array('modname'=>$this->_name, 'mid'=>$cinfo['id'], 'platform_id'=>$platform_id));
		$media_id = $m2minfo?$m2minfo['media_id']:'';
			
		$cinfo['media_id'] = $media_id;
		$cinfo['preview'] = $preview;
		$cinfo['release'] = $release;
		$cinfo['notify'] = $notify;
			
		$res = $m->pubtoContent($platform_id, $cinfo);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call pubto media_platform failed!");
			continue;
		} 
			
		$media_id = $res;	
			
		$cinfo['media_id'] = $media_id;					
			
		$res1 = $m2->syncContentOne($platform_id, $cinfo);
		
		$res2 = false;
		$res3 = false;
		
		//news
		//设置： m2m
		if (empty($m2minfo)) {
			$_params = array();
			$_params['mid'] = $cinfo['id'];
			$_params['modname'] = $this->_name;
			$_params['platform_id'] = $platform_id;
			$_params['media_id'] = $media_id;
			
			$res2 = $m3->set($_params);
			if (!$res2) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call set media_m2m failed!");
				//return false;
			}
		}	
		
		//重置
		$cinfo['media_id'] = $media_id;
		
		//发布: 草稿发布为内容
		if ($preview == 1 || $release || $notify) {
			$res3 = $m->pubtoContentRelease($platform_id, $cinfo);
		}
		
		$res = $res1 || $res2 || $res3;
		return $res;
	}
	
	protected function undoPubtoMediaOne($platform_id, $cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		
		$m = Factory::GetModel('media_platform');
		$m2 = Factory::GetModel('media_content');
		$m3 = Factory::GetModel('media_m2m');
		
		//查询一下是否已有提交
		$m2minfo = $m3->getOne(array('modname'=>$this->_name, 'mid'=>$cinfo['id'], 'platform_id'=>$platform_id));
		if (!$m2minfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no m2minfo!", $platform_id, $cinfo, $params);
			return false;
		}
		
		//
		$media_id = $m2minfo['media_id'];
		
		$res = $m2->undoPubtoMediaContent($platform_id, $media_id);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call undoPubtoMediaContent failed!", $platform_id, $media_id);
			return false;
		}
		
		return $res;
	}
	
	
	
	
	protected function formatAttachForView($pattern, $_content, $options)
	{
		$attachs = array();
		
		$m = Factory::GetModel('file');
		
		$res = preg_match_all($pattern, $_content, $attachs);
		if ($res && count($attachs[1]) > 0)
		{
			$old = array();
			$new = array();
			
			$nr = count($attachs[1]);			
			for ($i=0; $i<$nr; $i++) {
				$aid = $attachs[1][$i];
				if (is_md5($aid)) {
					$fileinfo = $m->getFileInfoByFileIDForView($aid, $options);
				} else {
					$fileinfo = $m->getForView($aid, $options);
				}
				if ($fileinfo) {
					$old[] = $attachs[0][$i];					
					$downloadUrl = $fileinfo['downloadUrl'];					
					$new[] = "<div> <i class='fa ft ft-$fileinfo[extname]'></i> <a href='$downloadUrl'>$fileinfo[name]</a> </div>";					
				}	
			}
			
			$_content = str_replace($old, $new, $_content);	
		}
		
		return $_content;
		
	}
	
	public function isContentView()
	{
		return true;
	}
	
	protected function getCatalogModel()
	{
		return Factory::GetModel('catalog');
	}
	
	public function getDir($pid, $params=array(), &$options=array() )
	{
		$m = $this->getCatalogModel();
		
		$params['pid'] = $pid;		
		$rows = $m->gets($params);
		
		foreach ($rows as $key=>&$v) {
			$v['hasChildren'] = $this->hasChildren($v['id'])?true:false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  $rows);
		
		return $rows;		
	}
	
	
	public function moveto($params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $params);
		
		$m = $this->getCatalogModel();
		
		$_params = array();
		$_params['cid'] = $params['new_pid'] ;
		
		$res = false;
		foreach ($params['id'] as $key => $v) {
			$_params['id'] = $v;
			$res = $this->update($_params);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "moveto failed!", $_params);
			}
		}
		
		return $res;
	}
	
	public function copyto($params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $params);
		
		$m = $this->getCatalogModel();
		
		$cid = $params['target_id'] ;
		
		$res = false;
		foreach ($params['id'] as $key => $v) {
			
			$cinfo = $this->get($v);
			if (!$cinfo) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id '$v'!");
				continue;
			}
			
			$_params = $cinfo;
			
			unset($_params['id']);
			unset($_params['uuid']);
			unset($_params['cuid']);
			unset($_params['ctime']);
			unset($_params['uid']);
			unset($_params['ts']);
			
			$_params['name'] .= ' 复件';
			
			$_params['cid'] = $cid;
			$res = $this->set($_params);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "copy failed!", $_params);
			}
		}
		
		return $res;
		
	}
	
	
}