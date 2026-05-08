<?php

/**
 * @file
 *
 * @brief 
 * 
 * 模块
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModuleModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CModuleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			
			case 'status':
				$f['input_type'] = "onoff";
				break;
			case 'mid':
			case 'url':
			case 'src':
				$f['show'] = false;
				break;
			case 'layout':
				$f['input_type'] = "selector";
				break;
			case 'ctype':
			case 'content':
				$f['show'] = false;
			case 'name':
			case 'type':
			case 'mid':
				$f['edit'] = false;
				
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['sortable'] = true;
				$f['selector'] = 'content_flags';
				$f['show'] = false;
				break;			
			case 'cid':
				$f['input_type'] = 'treemodel';
				$f['treemodel'] = 'catalog';
				$f['default'] = true;
				break;
			//case 'maxnum':
			//case 'num':
			case 'description':
			case 'tags':
			case 'reserved':
				$f['show'] = false;
				break;
				
			default:
				break;
		}
		return true;
	}
	
	
	public function formatForView(&$row, &$options = array())
	{
		//content
		$res = parent::formatForView($row, $options);
		
		//$row['_content'] = htmlspecialchars_decode($row['content']);
		
		return $res;
	}
	
	public function set(&$params, &$options=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $params);
		
		$res = parent::set($params, $options);
		if ($res) {
			//查询tplfile
			$m2 = Factory::GetModel('module2tplfile');
			$tdb = $m2->select(array('mid'=>$params['id']));
			foreach ($tdb as $key=>$v) {
				if (is_file($v['tplfile']))
					touch($v['tplfile']);
			}			
		}
		return $res;	
	}
	
	public function setModuleParams($mid, $params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...", $mid, $params);
		
		$params['id'] = $mid;
		
		$mpinfo = $this->get($mid);
		if ($mpinfo) {
			$params['id'] = $mpinfo['id'];			
			$res = $this->update($params);
		}  else {
			$res = $this->set($params);
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params, $res);
		
		if ($res) {
			//查询tplfile
			$m2 = Factory::GetModel('module2tplfile');
			$tdb = $m2->select(array('mid'=>$mid));
			foreach ($tdb as $key=>$v) {
				if (is_file($v['tplfile']))
					touch($v['tplfile']);//变更时间截
			}
		}
		return $res;	
	}
	
	public function del($id, &$options=array())
	{
		$old = parent::del($id, $options);
		
				
		return $old;	
	}
		
	public function createModule($params)
	{
		if (!isset($params['mid']))
			$params['mid'] = $this->newUUID($params['title'].time());
			
		$res = $this->set($params);
		if ($res) {
			return $this->get($params['id']);
		}
		
		return $res;
	}
	
}