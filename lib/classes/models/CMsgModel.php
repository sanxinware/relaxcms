<?php
/**
 * @file
 *
 * @brief 
 * 
 * Msg Model
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
//('0'=>'审签','1'=>'发布', '2'=>'高亮', '3'=>'弹框', '4'=>'声音', '5'=>'邮件', '6'=>'短信', '7'=>'走马灯', '8'=>'锁屏')
define ('MSG_FLAGS_CHECKED',	0x1);
define ('MSG_FLAGS_RELEASE',	0x2);
define ('MSG_FLAGS_HIGHLIGHT',  0x4);
define ('MSG_FLAGS_SHOWBOX',	0x8);

define ('MSG_FLAGS_PLAYSOUND',	0x10);
define ('MSG_FLAGS_SENDMAIL',	0x20);
define ('MSG_FLAGS_SENDMSG',	0x40);
define ('MSG_FLAGS_MARQUEE',	0x80);
define ('MSG_FLAGS_LOCK',		0x100);


//		'sel_msg_status' => array('0'=>'默认', '1'=>'正常', '2'=>'撤回','3'=>'完结'),		
define('MSG_STATUS_DEFAULT',	0);
define('MSG_STATUS_NORMAL',		1);
define('MSG_STATUS_CANCEL',		2);
define('MSG_STATUS_DONE',		3);
define('MSG_STATUS_EXPIRED',	4);
define('MSG_STATUS_WAIT',		9); //待发布


//		'sel_msg_ctype' => array('0'=>'默认', '1'=>'文本', '2'=>'图片', '3'=>'视频', '255'=>'其它'),		

define('MSG_CTYPE_DEFAULT',	0);
define('MSG_CTYPE_TEXT',		1);
define('MSG_CTYPE_IMAGE',	2);
define('MSG_CTYPE_VIDEO',	3);
define('MSG_CTYPE_CONTENT',	12);

//'0'=>'立即', '1'=>'定时'
define('MSG_ETYPE_NOW',	  0);
define('MSG_ETYPE_TIMER', 1);

//'0'=>'默认', '1'=>'全天(周一至周日)', '2'=>'工作日(周一至周五)', '3'=>'指定日期'
define('MSG_DTYPE_DEFAULT',	0);
define('MSG_DTYPE_ALLDAY',	1);
define('MSG_DTYPE_WORKDAY',	2);
define('MSG_DTYPE_SPECDAY',	3);

//'0'=>'单次', '2'=>'每分', '3'=>'每时', '4'=>'每天', '5'=>'每周', '6'=>'每月', '7'=>'每年'
define('MSG_RATE_NOW', 0); //单次
define('MSG_RATE_SEC', 1); //秒
define('MSG_RATE_MIN', 2);
define('MSG_RATE_HOUR', 3);
define('MSG_RATE_DAY', 4);
define('MSG_RATE_WEEK', 5);
define('MSG_RATE_MONTH', 6);
define('MSG_RATE_YEAR', 7);



class CMsgModel extends CPubModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CMsgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			
			case 'description':
				$f['show'] = false;		
				break;
			case 'tid':
				$f['input_type'] = 'content';		
				$f['show'] = false;			
				break;	
			case 'videourl':
				$f['input_type'] = 'video';		
				$f['show'] = false;			
				break;	
			case 'imageurl':
				$f['input_type'] = 'image';	
				$f['show'] = false;				
				break;	
			case 'oid':
				$f['input_type'] = 'model';
				$f['model'] = 'org';
				$f['show'] = false;	
				$f['edit'] = false;	
				//$f['searchable'] = 2;	
				break;
			case 'etype':
				$f['input_type'] = 'selector';
				$f['edit'] = false;
				$f['show'] = false;
				break;
			
			case 'dtype':
				$f['show'] = false;	
				$f['input_type'] = 'selector';
				$f['classex'] = 'input-medium';
				break;	
			case 'rate':
				//$f['classex'] = 'input-small';					
			case 'level':
				$f['input_type'] = 'selector';
				break;
			case 'ctype':
				$f['input_type'] = 'selector';
				break;
			case 'type':
			case 'status':
				$f['input_type'] = 'selector';
				$f['edit'] = false;
				
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['edit'] = false;
				$f['show'] = false;	
				break;
			case 'cuid':
				$f['input_type'] = 'CUID';
				$f['show'] = false;
			case 'uid':
				$f['input_type'] = 'UID';
				break;
			case 'ctime':
				$f['readonly'] = true;
				$f['show'] = false;
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				break;
			case 'etime':
				$f['input_type'] = 'DATETIME';
				$f['edit'] = false;				
				break;
			case 'start_time':
			case 'end_time':
				$f['input_type'] = 'DATETIME';
				//$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'last_time':
				$f['show'] = false;			
			case 'next_time':
				//$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				break;
			case 'mnum':
				//$f['classex'] = 'input-small';
				break;
			case 'enum':
			case 'sends':
			case 'opens':
			case 'hits':
				//$f['show'] = false;			
				$f['edit'] = false;
				break;
			default:
				break;
		}
		
		return true;
	}
	
	public function getFieldsforInput($params=array(), &$options=array(), $isadd=false)
	{
		$fdb = parent::getFieldsforInput($params, $options);
		
		$sort = $this->_fields['rate']['sort'];
		
		//频率
		$field1 = $this->_fields['rate'];
		$field2 = $this->_fields['mnum'];
		$fdb['rate']['edit'] = false;
		$fdb['mnum']['edit'] = false;
		
		$name = 'rategroup';		
		$newfield = $this->newField($name, 
				array('title'=>'执行频率', 'sort'=>$sort, 'addon'=>'次', 'classex'=>'input-large'));		
		$newfield['input'] = $this->buildInputGroup($newfield, $field1, $field2, $params,  $options);		
		$fdb[$name] = $newfield;
		
		$sort ++;
		
		//日期段
		$params['_start_date'] = tformat_date($params['start_time']);
		$params['_end_date'] = tformat_date($params['end_time']);
		
		$name = 'date';		
		$newfield = $this->newField($name, 
				array('title'=>'日期', 'sort'=>$sort, 'input_type'=>'DATERANGE'));		
		$newfield['input'] = $this->buildInput($newfield, $params,  $options);		
		$fdb[$name] = $newfield;
		
		//时段
		$name = 'time';			
		$params['_start_time'] = isset($params['start_time'])?tformat_time($params['start_time']):"00:00:00";
		$params['_end_time'] = isset($params['end_time'])?tformat_time($params['end_time']):"23:59:59";
		
		$newfield = $this->newField($name, 
				array('title'=>'时段', 'sort'=>$sort, 'input_type'=>'TIMERANGE'));		
		$newfield['input'] = $this->buildInput($newfield, $params,  $options);		
		$fdb[$name] = $newfield;
		
		
		
		
		array_sort_by_field($fdb, "sort", false);
		
		return $fdb;
	}
	
	protected function formatForViewForNextTime(&$row)
	{
		$status = $row['status'];
		if ($status == MSG_STATUS_DEFAULT) {
			$row['_next_time'] = '';
			return false;
		}
		
		/*$etype = $row['etype'];
		if ($etype == MSG_ETYPE_NOW) { //立即
			$row['_next_time'] = $this->formatLabelColorForView($row['enum']>0?0:2, $row['enum'] > 0?'已执行':'待执行');;
			return false;
		}*/
		
		$next_time = $row['next_time'];
		$color = $row['status'];
	
		$ts = time();
		
		$delta = $next_time - $ts;
		
		$_ntime = tformat_expired($next_time);
		
		if ($delta < 0) {
			$_done = $row['enum']>0?$this->formatLabelColorForView(0, '已执行'):$_ntime;
		} else {
			$_done = $_ntime;
		}
		
		
		$day = $next_time - $next_time%RC_TIMESEC_DAY;
		$today = $ts - $ts%RC_TIMESEC_DAY;
		
		$color = -1;
		
		if ($day == $today) {
			$_next_time = tformat_time($next_time).'('.$_ntime.')';
			if ($delta < 0) { //已过期
				$_next_time = $_done;
				$color = 0;
			} else if ($delta < RC_TIMESEC_MIN) {
				$color = 3;
			} else if ($delta < RC_TIMESEC_HOUR) {
					$color = 2;
				} else {
					$color = 1;
				}
			
		} else {
			if ($delta < 0) { //已过期
				$_next_time = $_done;
				$color = 0;
			} else {
				$_next_time = tformat($row['next_time']).'('.$_ntime.')';
			}
		}
		if ($color >= 0)
			$_next_time = $this->formatLabelColorForView($color, $_next_time);
		
		
		$row['_next_time'] = $_next_time;
	}
	
	
	
	public function formatForView(&$row, &$options = array())
	{
		parent::formatForView($row, $options);
		
		$levelcname = $this->getLabelColorName($row['level'], $icon);
		$row['_icon'] = "<span class='label label-sm label-icon label-$levelcname' title='$row[_level]'>
				<i class='fa $icon'></i> </span> ";
				
							
		$row['_ctimelong'] = tformat_timelong($row['ctime']);
		
		//status
		$color = $row['status'];
		
		switch ($color) {
			case 3:
				$color = 0;
				break;
			case 4:
				$color = 3;
				break;
			default:
				break;
		}
		$row['_status'] = $this->formatLabelColorForView($color, $row['_status']);
		
		/*$_description = $row['description'];
		switch($row['ctype']) {
			case MSG_CTYPE_VIDEO:
				$_description .= " $row[_videourl]";
				break;
			case MSG_CTYPE_IMAGE:
				$_description .= " $row[_imageurl]";
				break;
			case MSG_CTYPE_CONTENT:
				break;
			default:
				break;
		}
		
		$row['_description'] = $_description;	*/
		
		//rate
		/*if ($row['rate'] > 0) {
			$row['_rate'] = $row['mnum'].'/'.$row['_rate'];
		}	*/
		
		//next_time
		$this->formatForViewForNextTime($row);
		
	}
	
	
	protected function buildInputForMultiSelect(&$field, $params, &$options=array())
	{
		$ac = Factory::GetApp()->getActiveComponent();
		if($ac)			
			$ac->enableJSCSS('multiselect');
		
		$name = $field['name'];
		
		$m = Factory::GetModel('user');
		$users = $m->gets();
		
		$res = '';
		$res .= "<select multiple='multiple' class='multi-select' id='param_$name' name='params[$name][]'>";
		
		foreach ($users as $key=>$v) {
			$username = $v['nickname']?$v['nickname'] : $v['name'];
			$selected = '';
			$id = $v['id'];
			if (isset($params['_uid'])) {
				foreach ($params['_uid'] as $k2=>$v2) {
					if ($v2['uid'] == $id) {
						$selected = 'selected';
						break;
					}
				}
			}
			$res .= "<option  value='$id' $selected >$username</option>";
		}
		
		
		$res .= "</select><script>$('#param_$name').multiSelect(); </script>";
		return $res;				
	}	
	
	
		
	protected function deleteTo($id)
	{
		$m = Factory::GetModel('msg2user');
		$res = $m->delete(array('mid'=>$id));
		return $res;
	}
	
	protected function sendTo($params)
	{
		$res = false;
		$m = Factory::GetModel('msg2user');
		
		$id = $params['id'];
		
		$this->deleteTo($id);
		if (isset($params['uid'])) {
			$udb = is_array($params['uid'])?$params['uid']:explode(',', $params['uid']);	
			foreach ($udb as $key=>$v) {
				$item = array();
				$item['mid'] = $id;
				$item['uid'] = $v;
				
				$res = $m->set($item);			
				
			}
		}
		
		return $res;
	}
	
	
	protected function postSetMsg($params, $options=array()) 
	{
		$etype = $params['etype'];
		switch ($etype) {
			
		}
	}
	
	
	protected function getRateDelta($params)
	{
		$rate = $params['rate'];
		$mnum = isset($params['mnum'])?intval($params['mnum']):1;
		
		$begintime = $params['start_time'];
		$endtime = $params['end_time'];
		
		switch($rate) {
			default:
			case MSG_RATE_SEC: //秒
				$delats = 3;
				break;
			case MSG_RATE_MIN: //分
				$delats = 60/$mnum;
				break;
			case MSG_RATE_HOUR: //时
				$delats = 3600/$mnum;
				break;
			case MSG_RATE_DAY: //日
				$delats = 3600*24/$mnum;
				break;
			case MSG_RATE_WEEK: //周
				$delats = 3600*24*7/$mnum;
				break;
			case MSG_RATE_MONTH: //月
				$delats = 3600*24*30/$mnum;
				break;
			case MSG_RATE_YEAR: //年
				$delats = 3600*24*30*12/$mnum;
				break;
		}
		
		return $delats;
	}
	
	
	protected function checkParams(&$params, &$options=array())
	{
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $params); 
		
		$res = parent::checkParams($params, $options);
		
		if (empty($params['id'])) { //创建设置机构
			$params['oid'] = get_oid();
		}
		
		if (isset($params['rate']) && isset($params['_start_time']) && !isset($params['last_time'])) {//last_time
			//mnum
			if (intval($params['mnum']) <= 0)
				$params['mnum'] = 1;
			
			//_end_date
			if (!isset($params['_end_date']))
				$params['_end_date'] = $params['_start_date'];
				
			$today = tformat_date();
			$stime = s_mktime($today.' '.$params['_start_time']);
			$etime = s_mktime($today.' '.$params['_end_time']);
			if ($stime > $etime) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid etime '$etime'!");
				return false;
			}
			
			
			//start_time, end_time
			$start_time = s_mktime($params['_start_date'].' '.$params['_start_time']);
			$end_time = s_mktime($params['_end_date'].' '.$params['_end_time']);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $start_time, $params); exit;
			
			//每分执行
			if ($params['rate'] == MSG_RATE_MIN) {
				//开始时间秒部分变为0
				$start_time = $start_time - $start_time%60;				
				$end_time = $end_time - $end_time%60+59;
			} 
			
			if ($end_time <= $start_time) {
				$end_time = $start_time + 60;
			}
			
			$params['start_time'] = $start_time;
			$params['end_time'] = $end_time;
			
			$ts = time();
			$delats = $this->getRateDelta($params);
			
			$last_time = $start_time + ceil(($ts-$start_time-$delats-1)/$delats)*$delats;
			
			$params['last_time'] = $last_time;
			$params['next_time'] = $last_time + $delats;
			
			
			
		}
		
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $params); exit;
		
		return $res;
	}
	
	public function set(&$params, &$options=array())
	{
		$res = parent::set($params, $options);
		if ($res) {
			//发送
			$this->postSetMsg($params, $options);
		}
		return $res;
	}
	
	public function del($id, &$options=array())
	{
		$res = parent::del($id, $options);
		if ($res) {
			$this->deleteTo($id);
		}
		
		return $res;
	}
	
	public function getMsgListForPub($options=array())
	{
		$udb = array();
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		return $udb;
	}
	
	protected function doPubtoModelNow($pubinfo, $msginfo)
	{
		$res = parent::doPubtoModelNow($pubinfo, $msginfo);
		
		//检查是否过期
		if ($msginfo['rate'] == MSG_RATE_NOW) {
			$start_time = $msginfo['start_time'];
			$end_time = $msginfo['end_time'];
			$life = $end_time - $start_time;
									
			$ts = time()+3;
			$_params['id'] = $msginfo['id'];
			if ($ts > $start_time) {
				$_params['start_time'] = $ts;
			}
						
			if ($ts > $end_time) {		
				$_params = array();	
				$_params['start_time'] = $ts;				
				$_params['end_time'] = $_params['start_time']+$life;				
			}
				
			$_params['next_time'] = $ts + 5;				
			
			$this->update($_params);
		}
		
		return $res;
	}
	
	
	
	protected function doMsgTimer($params)
	{
		$res = false;

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO..");
		
		return $res;		
	}
	
	
	protected function timerProcessOne($params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		$id = $params['id'];
		
		$rate = intval($params['rate']);
		$ctype = $params['ctype'];
		
		$ts = time();
		$vtime = tformat_vtime($ts);
		
		$hh = $vtime[3];
		$mm = $vtime[4];
		$ss = $vtime[5];
		$week = $vtime[6];
		//当前时间
		//当前是星期几？
		
		//时间段
		$begintime = $params['start_time'];
		$endtime = $params['end_time'];//23:59:59		
		if ($begintime > $ts || $endtime < $ts) { //时间段
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "msg '$id' time range invalid! [".tformat($begintime).",".tformat($endtime)."]");
			if ($ts > $endtime) { //过期
				$_params = array();
				$_params['id'] = $params['id'];
				$_params['status'] = MSG_STATUS_EXPIRED; //过期
				$this->update($_params);
			}
			return false;
		}
		
		$id = $params['id'];
		if ($begintime) {
			$vtime = tformat_vtime($begintime);
			
			$hhS = $vtime[3];
			$mmS = $vtime[4];
			$ssS = $vtime[5];
			
			switch($rate) {
				case MSG_RATE_DAY: //每天
					if ( $hh < $hhS) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current hour hh $hh < hhS $hhS ");
						return false;
					} else if ($hh == $hhS && $mm < $mmS) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current minute mm $mm < mmS $mmS ");
						return false;
					} else if ($hh == $hhS && $mm == $mmS  && $ss < $ssS) {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current sec ss $ss < ssS $ssS ");
							return false;
						}
					break;
				case MSG_RATE_HOUR:
					if ( $mm < $mmS) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current minute mm $mm < mmS $mmS ");
						return false;
					} else if ($mm == $mmS  && $ss < $ssS) {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current sec ss $ss < ssS $ssS ");
							return false;
						}
					break;
				case MSG_RATE_MIN:
					if ($ss < $ssS) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current sec ss $ss < ssS $ssS ");
						return false;
					}
					break;
				default:					
					break;
			}		
		}
		
		
		if ($endtime) {
			$vtime = tformat_vtime($endtime);
			$hhE = $vtime[3];
			$mmE = $vtime[4];
			$ssE = $vtime[5];
			
			
			switch($rate) {
				case MSG_RATE_DAY: //每天
					if ($hhE > 0 &&  $hh > $hhE) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "msg $id current hour $hh > hhE $hhE ");
						return false;
					} else if ($hhE > 0 && $hh == $hhE && $mm > $mmE) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current minute $mm > mmE $mmE ");
						return false;
					} else if ($ssE > 0 && $hh == $hhE && $mm == $mmE  && $ss < $ssE) {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current sec $ss > ssE $ssE ");
							return false;
						}
					break;
				case MSG_RATE_HOUR://每小时
					if ( $mmE > 0 && $mm  >  $mmE) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current minute $mm > mmE $mmE ");
						return false;
					} else if ($ssE > 0 &&  $mm == $mmS  && $ss > $ssE) {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current sec $ss > ssE $ssE ");
							return false;
						}
					break;
				case MSG_RATE_MIN:
					if ($ssE > 0 && $ss  >  $ssE) {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"msg $id current sec $ss > ssE $ssE ");
						return false;
					}
					break;
				default:					
					break;
			}		
		}
		
		//周日检查
		$dtype = $params['dtype'];
		
		if ( $dtype == MSG_DTYPE_WORKDAY && $week >= 6) { //1=>'全天(周一至周日)', 2=>'工作日(周一至周五)'
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "rate '$rate' not include week '$week' ");
			return false;
		}
		
		//上次执行时间
		$last_time = isset($params['last_time'])?$params['last_time']:0;
		$mnum = isset($params['mnum'])?intval($params['mnum']):1;
		if ($last_time == 0) {
			$last_time = $begintime;
		} /*else if ($last_time > $begintime && $rate == 0) {//单次已经执行过了
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid id '$id' rate only run once!");
			return false;
		}*/
		
		
		if ($mnum <= 0)
			$mnum  = 1;
		
		$delats = 0; // 时间间隔
		$qd = 16;    // 误差
		
		$delats = $this->getRateDelta($params);
		
		if ($last_time + $delats < $ts) {
			$res = $this->doMsgTimer($params);
			if ($res) {
				$next_time = $ts+$delats;
				
				
				$_params = array();
				$_params['id'] = $params['id'];
				
				$_params['last_time'] = $last_time+ceil(($ts-$last_time-$delats)/$delats)*$delats;
				$_params['next_time'] = $next_time;
				$_params['enum'] = intval($params['enum']) + 1;
				
				if ($rate == MSG_RATE_NOW || $next_time > $endtime) { //单次或超期
					$_params['status'] = MSG_STATUS_DONE; //完结
				}
				
				$this->update($_params); 
				rlog(RC_LOG_INFO, __FILE__, __LINE__, __FUNCTION__, "do msg '$id' ok.", $_params);
				
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: call doMsgTimer failed!", $params);
			}
			
			return true;
		} else {
			$left = $last_time + $delats - $ts;
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "msg $id is waiting...,delats=$delats,left=$left");
			return false;
		}
	}
	
	public function timerProcess()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN...");
		$udb = $this->gets("where status=1");
		
		foreach ($udb as $key=>$v) 
			$this->timerProcessOne($v);
			
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return false;
	}
	
}
