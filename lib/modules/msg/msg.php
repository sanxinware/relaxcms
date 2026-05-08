<?php
/**
 * @file
 *
 * @brief 
 * Msg 模块
 *
 */
class MsgModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function MsgModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$options=array())
	{
		$hasMsg = is_model('my_msg');
		if ($hasMsg) {
			$m = Factory::GetModel('my_msg');		
			$params = array();
			$udb = $m->selectForView($params, $options);
			
			$total = $params['total'];
			
			
			$this->assign('udb', $udb);
			$this->assign('total', $total);
		} 
		
		$this->assign('hasMsg', $hasMsg);		
		
		return true;
	}	
}