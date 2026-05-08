<?php

/**
 * @file
 *
 * @brief 
 * 
 * 天气预报
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CWeatherModel extends CPubModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CWeatherModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}


	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'city':
				$f['input_type'] = 'regionvalselector';
				$f['region_pid'] = '0115612'; //安徽省14234

				break;
			case 'flags':
				$f['input_type'] = 'multicheckbox';
				break;
			case 'status':				
			case 'type':
				$f['input_type'] = 'selector';
				break;
			case 'date':
				$f['input_type'] = 'date';
				break;
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;
				$f['edit'] = false;				
				break;
			case 'uid':
				$f['input_type'] = 'UID';
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly'] = true;
				$f['edit'] = false;
				$f['show'] = false;
				break;

			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				break;				
			default:
				break;
		}

		return true;
	}

	public function formatForView(&$row, &$ioparams=array())
	{
		parent::formatForView($row, $ioparams);

		$type = $row['type'];
		
		$icon = 'wi'.$type;
		$row['_icon'] = $icon;
		$row['_type'] = "<div class='wi $icon'></div>";
		
		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
	}

	protected function fixForTV($params)
	{
		$winfo = array();
		
		$winfo['icon'] = $params['_icon'];
		$winfo['tmin'] = $params['tmin'];
		$winfo['tmax'] = $params['tmax'];
		$winfo['description'] = $params['description'];
		
		return $winfo;
	}

	public function getTodayByOid($oid, &$ioparams=array())
	{
		$datets = tformat_today();
		
		$params = array();
		
		//$params['date']=$ts;
		//$res = $this->getListByOid($oid, $params, 1, $ioparams);
		$res = $this->getOne(array('date'=>$datets));
		
				
		$wdb = array();		
		if (!$res) {
			$wdb[] = array(
				'icon'=>'wi0',
				'description'=>'数据待更新');
		} else {
			$this->formatForView($res);			
			$wdb[] = $this->fixForTV($res);
		}
				
		return $wdb;
	}
	

}