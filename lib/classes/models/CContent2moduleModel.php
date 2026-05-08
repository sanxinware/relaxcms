<?php

defined('RPATH_BASE') or die();
class CContent2moduleModel extends CTableModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContent2moduleModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function delLastOf($mid)
	{
		$_params = array();
		$_params['mid'] = $mid;	
		
		$id = 0;
		$mincid = $this->min('cid', $_params, $id);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO .... min content_id '$mincid', id=$id!"); 
		if ($id > 0)
			$this->del($id);
		
		return true;
	}
	
	protected function checkIfContent2Model($cinfo, $moduleinfo)
	{
		$cid = $cinfo['cid'];
		
		$status = $cinfo['status'];
		$flags = $cinfo['flags'];
		$hits = $cinfo['hits'];
		
		$catalog_id = intval($moduleinfo['cid']);
		if ($catalog_id > 0 && $catalog_id != $cid) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid cid '$cid' expected '$v[cid]'!");
			return false;
		} 
		
		$_flags = intval($moduleinfo['flags']);
		if ($_flags == 0 && $catalog_id == 0) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: invalid flags=$_flags and cid=$catalog_id!");
			return false;
		}
		
		if (($_flags & $flags ) != $_flags) { //最低要求flags	
			$delta_flags = ($_flags & $flags );	
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "expected param_flags=$param_flags, flags=$flags, delta_flags=$delta_flags");									
			return false;
		}
		
		
		//tag
		$tags = trim($moduleinfo['tags']);
		if ($tags) {
			$pattern = str_replace(',', '|', $tags);
			$pattern = str_replace(' ', '|', $pattern);
			$pattern = "#$pattern#i"; //tag1|tag2|tag3
			$content = $cinfo['name'].' '.$cinfo['summary'].' '.$cinfo['content'];
			$res = preg_match($pattern, $content);
			if (!$res) {//no match
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tags=$tags");				
				return false;
			}
		}
		
		return true;
	}

	public function trigger($event, $args=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...event=$event"); 
		
		$res = false;

		$cinfo = $args;
		
		$content_id = $cinfo['id'];
		
		$m = Factory::GetModel('module');
		$tdb = $m->select();
		foreach ($tdb as $key=>$v) {
			$id = $v['id'];
			$maxnum = intval($v['maxnum']);
			$num = intval($v['num']);
			
			$c2minfo = $this->getOne(array('cid'=>$content_id, 'mid'=>$id));
			
			if (!($res = $this->checkIfContent2Model($cinfo, $v))) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "call checkIfContent2Model failed! skip module '$v[id]'!");
				
				//已经加入的要取消
				if ($c2minfo) {
					$this->del($c2minfo['id']);
					$m->dec($id, 'num');
				}
				continue;
			}
			
			if ($c2minfo) { //已经存在，不用动
				continue;
			}
			
			//hists
			/*$min_hits = $v['min_hits'];
			if ($min_hits > 0 && $min_hits < $hits) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "less hits=$hits");	
				continue;
			}*/
			//入队
			$_params = array();
			$_params['cid'] = $content_id;//content_id
			$_params['mid'] = $id;	
			$res = $this->set($_params);
			if (!$res) {//no match
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: set site content2module failed!", $_params);				
				continue;
			}
			
			$num ++;
			if ($maxnum > 0 && $num > $maxnum) { //超最大数量，最先入队的删除
				$res = $this->delLastOf($id);				
			} else {				
				$_params = array();
				$_params['id'] = $id;
				$_params['num'] = $num;			
				$res = $m->update($_params);	
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'old num='.$v['num'].', new num='.$num);			
				if (!$res) {//no match
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: update module failed!", $_params);				
				}
			}
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.. $res"); 
		return $res;
	}
	
	protected function delete($params=array())
	{
		$res = true;
		$m = Factory::GetModel('module');
		$udb = $this->gets($params);	
		foreach ($udb as $key=>$v) {
			$res = $this->del($v['id']);			
			$m->dec($v['mid'], 'num');
		}	
		
		return $res;
	}
	
}