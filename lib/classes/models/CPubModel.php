<?php

/**
 * @file
 *
 * @brief 
 *
 * 发布模型
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('PUB_STATUS_DEFAULT',	0);
define ('PUB_STATUS_RELEASED',	1); //已发布
define ('PUB_STATUS_WAIT',		9); //待发布

//ETYPE
define ('PUB_PETYPE_NOW',	0);
define ('PUB_PETYPE_TIMER',	1);

//'sel_pub_eflags'=>array('0' => '预览','1' => '发表','2' => '群发通知','3' => '锁定节目','4' => '消息推送',	'5' => '取消删除同步',),
define ('PUB_EFLAGS_PREVIEW',	0x1);
define ('PUB_EFLAGS_RELEASE',	0x2);
define ('PUB_EFLAGS_NOTIFY',	0x4);
define ('PUB_EFLAGS_LOCK',		0x8);
define ('PUB_EFLAGS_PUSHMSG',	0x10);
define ('PUB_EFLAGS_UNDODEL',	0x20);

class CPubModel  extends CClusterModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CPubModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'eflags':
				$f['input_type'] = 'varmulticheckbox';
				break;
			case 'etype':
				$f['input_type'] = 'selector';
				$f['edit'] = false;
				break;
			case 'etime':
				$f['input_type'] = 'DATETIME';				
				break;
			default:
				break;
		}
		
		return true;
	}
	


	protected function getActions($row=array(), &$options=array())
	{
		$actions = parent::getActions($row, $options);
		
		/*if (is_model('org')) {
			$actions['pubto'] = array(
					'name'=>'pubto',
					'title'=>'发布到',
					'action'=>'tmbox',
					'enable'=>true,
					'showbutton'=>true,
					'icon'=>'fa fa-share',
					'class'=>'btn-success',
					'sort'=>1,
					);
			
			$actions['cancel'] = array(
					'name'=>'cancel',
					'title'=>'撤销',
					'action'=>'button',
					'msg'=>'确定撤销吗？',
					'enable'=>true,
					'showbutton'=>true,
					'icon'=>'fa fa-reply',
					'class'=>'btn-warning',
					'sort'=>4,
					);
		}*/
		
		//发布到
		$status = intval($row['status']);
		
		$actions['detail']['showbutton'] = false;
		
		$btn = $status == 1?'btn-success':($status == 0?'btn-primary':'btn-warning');
		$icon = $status == 1?'reply':'share';
		$actions['pub'] = array(
				'name'=>'pubto',
				'icon'=>'fa fa-'.$icon,
				'title'=>'发布',
				'description'=>'发布',
				'action'=>'tmbox',
				'class'=>$btn,
				'sort'=>1,
				'enable'=>true,
				'showbutton'=>1,					
				);
		
		
		
		
		return $actions;
	}
	
	/*
	getForView
	
	[tomod] eg:
	
	Array
	(
	   [org] => Array
	       (
	           [1] => 1
	       )
	
	   [media_platform] => Array
	       (
	           [1] => 1
	       )
	
	)
	*/
	public function getForView_unused(&$params, &$options = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		$res = parent::getForView($id, $options);
		
		//pubtodb
		/*$status = $res['status'];
		$m = Factory::GetModel('pub');
		$pubinfo = $m->getOne(array('uuid'=>$res['uuid']));
		if ($pubinfo) {
			$pubto = unserialize($pubinfo['tomod']);
			$pubtolist = array();
			foreach ($pubto as $key=>$v) {
				$to_modname = $key;
				foreach ($v as $k2=>$v2) {
					$to_mid = $v2;					
					$m2 = Factory::GetModel($to_modname);
					$toinfo = $m2->get($to_mid);
					
					$item = array();
					
					$item['id'] = $toinfo['id'];
					$item['to_name'] = $toinfo['name'];
					
					$item['to_modname'] = $to_modname;
					$item['to_mid'] = $to_mid;
					
					$pubtolist[] = $item;
					
				}
			}
			$res['pubtolist'] = $pubtolist;
		}*/
		
		//if ($status == 1) {
			$m = Factory::GetModel('pubto');
			$udb = $m->gets(array('modname'=>$this->_name, 'mid'=>$id));
			if ($udb) {
				$pubtolist = array();
				foreach ($udb as $key=>$v) {
					$m->formatForView($v, $options);
					
					$to_modname = $v['to_modname'];
					$to_mid = $v['to_mid'];
					
					$m2 = Factory::GetModel($to_modname);
					$toinfo = $m2->get($to_mid);
					
					$v['to_name'] = $toinfo['name'];
					
					$pubtolist[] = $v;
				}
				$res['pubtolist'] = $pubtolist;
			}
		//}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		
		return $res;
	}
	
	
	public function loadPubInfoForView(&$params, $options = array())
	{
		//pubinfo
		$uuid = $params['uuid'];
		$m = Factory::GetModel('pub');
		$pubinfo = $m->getOne(array('uuid'=>$uuid));
		if ($pubinfo) {
			$params['pubinfo'] = $pubinfo;
			
			$m = Factory::GetModel('pubto');
			$udb = $m->gets(array('modname'=>$this->_name, 'mid'=>$params['id']));
			if ($udb) {
				
				$pubtolist = array();
				foreach ($udb as $key=>$v) {
					$m->formatForView($v, $options);
					
					$to_modname = $v['to_modname'];
					$to_mid = $v['to_mid'];
					
					$m2 = Factory::GetModel($to_modname);
					$toinfo = $m2->get($to_mid);
					
					$v['to_name'] = $toinfo['name'];
					
					$pubtolist[] = $v;
				}
				$params['pubtolist'] = $pubtolist;
			}			
		}
		//}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		
		return $params;
	}
	
	public function getListByOid($oid, $params, $options)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $oid, $params);
		
		$modname = $this->_modname.'2org';
		if (is_model($modname)) {
			$params['b.oid'] = $oid;
			$rows = $this->selectJoinOn($modname, $params);			
		} else {
			$m = Factory::GetModel('pubto');
			
			
			$_params = array();
			$_params['to_mid'] = $oid;
			$_params['to_modname'] = 'org';
			$_params['modname'] = $this->_name;
			
			$udb = $m->select($_params);
			
			$mids = array();
			foreach($udb as $key=>$v) {
				$mids[] = $v['mid']; //mids
			}
			
			$rows = array();
			foreach ($mids as $key=>$mid) {
				$v = $this->get($mid, $options);
				$rows[] = $v;
			}
		}
		
		return $rows;
	}
	
	
	
	public function getByToMID($id, $to_modname, $to_mid=0)
	{
		$info = $this->get($id);
		if (!$info) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$_params = array();
		$_params['modname'] = $this->_name;
		$_params['mid'] = $id;
		$_params['to_modname'] = $to_modname;
		$_params['to_mid'] = $to_mid;
		
		$m = Factory::GetModel('pubto');
		$pubtoinfo = $m->getOne($_params);
		if ($pubtoinfo) {
			$info['target_id'] = $pubtoinfo['target_id'];
		}
		return $info;		
	}	
	
	
	public function getOidsByMID($modname, $mid)
	{		
		
		$_params = array();
		$_params['modname'] = $modname;
		$_params['mid'] = $mid;

		$m = Factory::GetModel('pub2org');
		$udb = $m->select($_params);
		
		$oids = array();
		foreach($udb as $key=>$v) {
			$oids[$v['oid']] = $v; 
		}
		return $oids;		
	}
	
	public function getPubtoPlatform($id)
	{
		$m = Factory::GetModel('org');
		$orgdb = $m->gets();

		$odb = $this->getOidsByMID($this->_name, $id);

		
		$c2p = array();
		foreach ($odb as $key=>$v) {
			$c2p[$v['oid']] = $v;
		}

		foreach ($orgdb as $key => &$v) {
			//$v['disable'] = $v['status'] == 1?'':'disabled';
			$v['checked'] = isset($c2p[$v['id']])?'checked':'';
		}

		return $orgdb;
		
	}

	protected function prePub2org(&$params)
	{
		return false;
	}
	
	
	protected function postPub2Org($params)
	{
		return false;
	}
	
	
	protected function setPub2Org($mid, $oid)
	{
		$m = Factory::GetModel('pub2org');

		$params = array();
		$params['modname'] = $this->_name;
		$params['mid'] = $mid;
		$params['oid'] = $oid;
		
		$this->prePub2Org($params);
		
		$res = $m->set($params);
		
		if ($res) {
			$this->postPub2Org($params);
		}
		
		return $res;	
	}



	protected function delPub2Org($mid, $oinfo)
	{
		$id = $oinfo['id'];
		$m = Factory::GetModel('pub2org');
		$res = $m->del($id);

		return $res;	
	}
	
	protected function getToModname($defToModname='org')
	{
		return $defToModname;
	}
	
	public function getPubtoForView($id, $options=array())
	{
		$params = $this->getForView($id, $options);
		
		//加载默认目标
		
		//发布到机构: org
		$to_modname = $this->getToModname();
		if (is_model($to_modname)) {
			$this->getPubtoList($id, $to_modname, $params, $options);			
		}
		
		//pub
		$uuid = $params['uuid'];
		$m = Factory::GetModel('pub');
		$pubinfo = $m->getOne(array('uuid'=>$uuid));
		$fields = $m->getFieldsForInputEdit($pubinfo, $options);
		
		$params['etype'] = $pubinfo['etype'];
		$params['etime'] = $pubinfo['etime'];
		
		$params['_etype'] = $fields['etype']['input'];
		$params['_etime'] = $fields['etime']['input'];
		
		
		$params['_eflags'] = $fields['eflags']['input'];
		$params['_eflagsChecked'] = $fields['eflags']['_checked'];
		
		return $params;
	}
	
	
	protected function getToModelList($to_modname, $params=array())
	{
		$m = Factory::GetModel($to_modname);
		$udb = $m->gets($params);
		return $udb;
	}
	
	public function getPubtoList($id, $to_modname, &$params=array(), &$options=array())
	{
		$is_detail = isset($options['detail']) && $options['detail'];
		
		$udb = $this->getToModelList($to_modname);
		
		
		
		$m2 = Factory::GetModel('pubto');
		$pubtodb = $m2->gets(array('modname'=>$this->_name, 'mid'=>$id, 'to_modname'=>$to_modname));
		
		$m2p = array();
		foreach ($pubtodb as $key=>$v) {
			$m2p[$v['to_mid']] = $v;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $m2p);
		
		$pdb = isset($params['pubtodb'])?$params['pubtodb']:array();
		
		foreach ($udb as $key=>$v) {
			
			//当status != 1时，禁用
			$v['_disable'] = $v['status'] == 1?'':'disabled';			
			$v['to_modname'] = $to_modname;
			
			if (isset($m2p[$v['id']])) {
				$pubinfo = $m2p[$v['id']];
				$v['checked'] = true;
				$v['_checked'] = 'checked';
				
				$checked = true;
			} else {
				$checked = false;
			}
			
			if (!$is_detail || ($is_detail && $checked))			
				$pdb[] = $v;
		}
		
		$params['pubtodb'] = $pdb;
		
		return $pdb;
	}
	
	
	
	
	public function preparePubtoForModel(&$pubtoinfo, &$cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		//生成默认：default target uuid
		$modname = $this->_name;
		$mid = $cinfo['id'];
		
		$to_modname = $pubtoinfo['to_modname'];
		$to_mid = $pubtoinfo['to_mid'];
		
		$pubtoinfo['tuuid'] = md5($modname.'-'.$mid.'-'.$to_modname.'-'.$to_mid);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		return true;
	}
	
	
	public function pubtoForModel(&$pubtoinfo, &$cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$res = true;
		
		$to_modname = $pubtoinfo['to_modname'];		
		$m = Factory::GetModel($to_modname);
		if (method_exists($m, 'pubtoForModel')) {
			$res = $m->pubtoForModel($pubtoinfo, $cinfo, $params, $options);
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");		
		
		return $res;
	}
	
	
	public function postPubtoForModel(&$pubtoinfo, $cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		
		return true;
	}
	
	
	protected function setModel2Model($pubtoinfo, $cinfo, $options)
	{
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$res = false;
		$modname = $this->_modname.'2'.$pubtoinfo['to_modname'];
		if (is_model($modname) && isset($cinfo['uuid'])) {			
			$m3 = Factory::GetModel($modname);
			$nr = 0;
			$_params = array();			
			$fdb = $m3->getFields();
			foreach ($fdb as $key=>$v) {
				if ($v['model'] == $this->_modname) {
					$_params[$key] = $cinfo['id'];
					$nr ++;
				}
				if ($v['model'] == $pubtoinfo['to_modname']) {
					$_params[$key] = $pubtoinfo['to_mid'];
					$nr ++;
				}
			}	
			$_params['tuuid'] = $pubtoinfo['tuuid'];
			
			$m2minfo = $m3->getOne(array('tuuid'=>$pubtoinfo['tuuid']));
			if ($m2minfo) {
				$_params['id'] = $m2minfo['id'];
			}			
			
			$res = $m3->set($_params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set model2model '$modname' failed!", $_params);
			}
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no model '$modname' or no cinfo[uuid]!!");
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return $res;
	}
	
	protected function setPubtoInfo(&$pubtoinfo, $cinfo, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$m1 = Factory::GetModel($pubtoinfo['to_modname']);
		$toinfo = $m1->get($pubtoinfo['to_mid']);
		if (!$toinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no to_mid failed!", $pubtoinfo);
			return false;
		}
		
		$pubtoinfo['uuid'] = $cinfo['uuid'];
		$pubtoinfo['to_uuid'] = $toinfo['uuid'];
		
		$m2 = Factory::GetModel('pubto');		
		$res = $m2->setPubtoInfo($pubtoinfo, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call set pubto failed!", $pubtoinfo);
			return false;
		}
		
		//<MODEL2MODEL>, eg: content2org
		$this->setModel2Model($pubtoinfo, $cinfo, $options);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		return $res;
	}
	
	protected function unsetModel2Model($pubtoinfo, $cinfo, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		//<MODEL2MODEL>
		$modname = $this->_modname.'2'.$pubtoinfo['to_modname'];
		if (is_model($modname)) {
			$m3 = Factory::GetModel($modname);
			$m2minfo = $m3->getOne(array('tuuid'=>$pubtoinfo['tuuid']));
			if ($m2minfo) {
				$m3->del($m2minfo['id']);
			}			
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT...");
		
		return $res;
	}
	
	
	protected function unsetPubtoInfo($pubtoinfo, $cinfo, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN..."); 
		$m = Factory::GetModel('pubto');		
		$res = $m->del($pubtoinfo['id']);
		
		//<MODEL2MODEL>
		$this->unsetModel2Model($pubtoinfo, $cinfo, $options);
				
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return $res;
	}
	
	
	protected function pubtoModelOne($to_modname, $to_mid, $cinfo, $params, $options)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		
		$pubtoinfo = array();
		
		$pubtoinfo['modname'] = $this->_name;
		$pubtoinfo['mid'] = $cinfo['id'];
		$pubtoinfo['to_modname'] = $to_modname;
		$pubtoinfo['to_mid'] = $to_mid;			
		
		
		//准备, init tuuid
		$this->preparePubtoForModel($pubtoinfo, $cinfo, $params, $options);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2...", $pubtoinfo, $cinfo);
		
		//设置: pubtoinfo
		$res2 = $this->setPubtoInfo($pubtoinfo, $cinfo, $options);		
		if (!$res2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "WARNING: call set pubto failed!", $pubtoinfo);
			return false;
		}
		
		//处理
		$res = $this->pubtoForModel($pubtoinfo, $cinfo, $params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call pubtoForModel failed!", $pubtoinfo, $params, $cinfo);
			return false;
		} 		
		
		//最后
		$this->postPubtoForModel($pubtoinfo, $cinfo, $params, $options);
			
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return $res;
	}
	
	public function undoPubtoForModel(&$pubtoinfo, $cinfo, $params=array(), $options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		//$modname, $mid, $to_modname, $to_mid, 
		$to_modname = $pubtoinfo['to_modname'];
		
		$m2 = Factory::GetModel($to_modname);
		if (method_exists($m2, 'undoPubtoForModel')) {
			$res = $m2->undoPubtoForModel($pubtoinfo, $cinfo, $params, $options);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call undoPubtoForModel failed!", $pubtoinfo);
			}
		}	
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
			
		return true;
	}
	
	protected function undoPubtoModelOne($to_modname, $to_mid, $cinfo, $params, $options)
	{
		
		$m = Factory::GetModel('pubto');
		
		//查询一下是否已有提交
		$pubtoinfo = $m->getOne(array('modname'=>$this->_name, 'mid'=>$cinfo['id'], 'to_modname'=>$to_modname, 'to_mid'=>$to_mid));
		if (!$pubtoinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no pubto info!", $to_modname, $to_mid, $cinfo, $params);
			return false;
		}
		
		$res = $this->undoPubtoForModel($pubtoinfo, $cinfo, $params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call undoPubtoForModel failed!", $pubtoinfo);
			return false;
		}
		
		//del
		$this->unsetPubtoInfo($pubtoinfo, $cinfo, $options);
		
		return $res;
	}
	
	
	/*
	Array
	(
	   [id] => 37
	   [flags] => Array
	       (
	           [0] => 0
	           [1] => 1
	           [2] => 10
	       )
	
	   [pubto] => Array
	       (
	           [media_platform] => Array
	               (
	                   [1] => 1
	               )
				 [qptv_org] => Array
	               (
	                   [7] => 7
	               )
	
	
	       )
	
		)
	*/
	
	protected function pubtoModel(&$pubinfo, $cinfo, $params, $options)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$id = $params['id'];
		$pubtodb = $cinfo['pubtodb'];
		
		//查询当前未勾选项
		$res = true;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $pubtodb);				
		$unpubtodb = array();
		
		foreach ($pubtodb as $k2=>$v2) {
			$to_mid = $v2['id'];
			$to_modname = $v2['to_modname'];
			
			$found = false;
			if (isset($params['pubto'])) {
				foreach ($params['pubto'] as $key => $v) {
					if ($key== $to_modname && isset($v[$to_mid])) { //选中项
						$found = true;
					}
				}
			}	
			
			if ($found) {//当前勾选项
				$res = $this->pubtoModelOne($to_modname, $to_mid, $cinfo, $params, $options);
				if (!$res) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call doPubtoModelOne '$to_modname' id '$to_mid' failed!");
				}
			} else if(isset($v2['checked']) && $v2['checked'] == 1) {//取消勾选
				$res = $this->undoPubtoModelOne($to_modname, $to_mid, $cinfo, $params, $options);	
				if (!$res) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call undoPubtoModelOne '$to_modname' id '$to_mid' failed!");
				} else {
					$unpubtodb[] = $v2;
				}
			}
		}
		
		$pubinfo['unpubtodb'] = $unpubtodb;
		
		return $res;
		
		
	}
	
	protected function setFlags($id, $flags)
	{
		$params = array();
		$params['id'] = $id;
		$params['flags'] = $flags;		
		$res = $this->update($params);
		
		return $res;
	}
	
	
	protected function setStatus($id, $status)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ... status=$status, ");
		$params = array();
		$params['id'] = $id;
		$params['status'] = $status;		
		$res = $this->update($params);
		
		
		//更新引用分享
		$m = Factory::GetModel('file2model');
		$res2 = $m->shareFile2Model($status == 1, $this->_modname, $id);		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT. $res");
		
		return $res;
	}
	
	protected function isFlagsChanged($newflags, $oldflags, &$status=0)
	{
		$newflags = $newflags;
		
		if (($newflags & 3) == 3) {
			$status = 1;
		} else {
			if ($status == 1) { //原先是发布，改为撤销
				$status = 2;
			} 
		}		
		return  ($newflags != $oldflags);
	}
	
	protected function setPubInfo($params, $cinfo, $options=array())
	{
		$uuid = $cinfo['uuid'];
		
		$pubto = empty($params['pubto'])?array():$params['pubto'];
		
		$params['uuid'] = $cinfo['uuid'];
		$params['modname'] = $this->_name;
		$params['mid'] = $cinfo['id'];
		$params['tomod'] = serialize($pubto);
		//flags
		$params['flags'] = isset($params['flags'])?$this->parseInputMultiCheckBox($params['flags']):0;
		
		$m = Factory::GetModel('pub');
		$pubinfo = $this->getOne(array('uuid'=>$uuid));
		if ($pubinfo) {
			$params['id'] = $pubinfo['id'];
		} else {
			$params['id'] = 0;
		}
		
		//status
		$params['status'] = $status = $params['etype'] == PUB_PETYPE_NOW?PUB_STATUS_DEFAULT:PUB_STATUS_WAIT;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$res = $m->set($params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set pub failed!", $params);
			return false;
		}
		
		$pubinfo = $m->get($params['id']);
		
		return $pubinfo;
	}
	
	
	
	
	/*
	Array
	(
	   [id] => 37
	   [flags] => Array
	       (
	           [0] => 0
	           [1] => 1
	           [2] => 10
	       )
		
	   [pubto] => Array
	       (
	           [media_platform] => Array
	               (
	                   [1] => 1
	               )
				 [qptv_org] => Array
	               (
	                   [7] => 7
	               )
		
		
	       )
		
		)
	*/
	
	//
	protected function doPubtoModelWait($pubinfo, $cinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $pubinfo);
		$res = $this->setStatus($pubinfo['mid'], PUB_STATUS_WAIT);	
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		return $res;
	}
	
	
	
	protected function doPubtoModel($pubinfo, $cinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$res = $this->setFlags($pubinfo['mid'], $pubinfo['flags']);
		
		//$res = $this->setStatus($pubinfo['mid'], PUB_STATUS_RELEASED);		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return $res;
	}
	
	protected function doPubtoModelNow($pubinfo, $cinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $pubinfo);
		
		$res = $this->doPubtoModel($pubinfo, $cinfo);		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return $res;
	}
	
	/*
	
	pubto:
	
	
	$params:	
	Array
	(
		[id] => 29
		[flags] => Array
			(
				[0] => 0
				[1] => 15
			)

	)
	
	*/
	public function pubto($params, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		$id = $params['id'];		
		$info = $this->getPubtoForView($params['id'], $options);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		//set pubinfo
		$pubinfo = $this->setPubInfo($params, $info, $options);
		if (!$pubinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set pubinfo failed!", $params);
			return false;
		}
		
		//pubtoModel
		$res = $this->pubtoModel($pubinfo, $info, $params, $options);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call pubtoModel failed!", $params);
			return false;
		}
		
		
		//status
		//$newStatus = $info['status'];
		//$changed = $this->isFlagsChanged($flags, $info['flags'], $newStatus);
		if ($pubinfo['etype'] == PUB_PETYPE_TIMER) {
			$res = $this->doPubtoModelWait($pubinfo, $info); 
		} else {//NOW
			$res = $this->doPubtoModelNow($pubinfo, $info);			
		}
		
		return $res;
	}
	
	protected function delPubtoInfo($cinfo)
	{
		$res = true;
		
		$id = $cinfo['id'];
		
		$m = Factory::GetModel('pubto');
		$pubtodb = $m->gets(array('modname'=>$this->_name, 'mid'=>$id));
		foreach ($pubtodb as $key=>$v) {
			$res = $this->undoPubtoForModel($v, $cinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call undoPubtoForModel failed!");
				break;
			}
			
			//del
			$m->del($v['id']);
			
		}
		
		return $res;
	}
	
	
	public function del($id, &$options=array())
	{
		//解除发布
		$cinfo = $this->get($id);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$res = $this->delPubtoInfo($cinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call delPubtoInfo failed!");
			return false;
		}
				
		$old = parent::del($id, $options);
		
		return $old;
	}
	
	protected function checkETime($pubinfo)
	{
		$delta = $pubinfo['etime'] - time();
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "have to wait {$delta}s ...");
		return ($delta <= 0)?true:false;
	}
	
	protected function timerProcessPubto($pubinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");	
		$modname = $pubinfo['modname'];
		$mid = $pubinfo['mid'];
		$m = Factory::GetModel($modname);
		$cinfo = $m->get($mid);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no mid '$mid'!");	
			return false;			
		}	
		
		$res = $m->doPubtoModel($pubinfo, $cinfo);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");	
		
		return $res;
	}
		
	protected function timerProcessOne($pubinfo)
	{
		$res = false;
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WAITING ......");
		
		//检查时间是否到了或超时
		$res = $this->checkETime($pubinfo);
		if ($res) { //时间到了		
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "DO PUB.... ......");
			$res = $this->	timerProcessPubto($pubinfo);
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		return $res;
	}
	
	public function timerProcess()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		
		$m = Factory::GetModel('pub');
		$udb = $m->gets(array('status'=>PUB_STATUS_WAIT));	 //所有待发布
		foreach ($udb as $key=>$v) {
			$res = $this->timerProcessOne($v);
			
			if ($res) {
				$_params = array();
				$_params['id'] = $v['id'];
				$_params['status'] = PUB_STATUS_RELEASED;	
				
				$m->update($_params);			
			}
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
	}
}