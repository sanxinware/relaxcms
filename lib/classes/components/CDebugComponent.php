<?php

/**
 * @file
 *
 * @brief 
 *  调测
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CDebugComponent extends CUIComponent
{
	protected $_attag="autotest";
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CDebugComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function getDebugList()
	{
		return array();
	}
	
	
	protected function isClean()
	{
		$clean  = get_int('clean', 0);
		return $clean == 1;		
	}
	
	
	protected function show(&$options=array())
	{
		$tdb = $this->getDebugList();
		
		$tdb[] = array( 'name'=>'all', 
				'title'=>'全自动测试','description'=>'全自动测试');
				
		$id = 1;
		$params = array();
		foreach ($tdb as $key=>$v) {
			$v['id'] = $id ++;
			$params[] = $v;
		}
		$this->assign('tdb', $params);
		$this->setTpl('debug');
	}
	
	
	protected function doAll($cmd, &$options=array())
	{
		$tdb = $this->getDebugList();
		foreach ($tdb as $key=>$v) {
			$name = $v['name'];
			if ($name == 'all')
				continue;
			
			$task = $cmd.ucfirst($name);	
			if (method_exists($this, $task)) {
				$res = $this->$task($options);	
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call $task failed!");
					return false;
				}	
			} 
		}
		
		return true;
	}
	
	
	protected function testAll(&$options=array())
	{
		return $this->doAll('test', $options);
	}
	
	protected function test(&$options=array())
	{
		$res = false;
		
		$name = $this->request('name');
		$task = 'test'.ucfirst($name);
		
		if (method_exists($this, $task)) {
			$res = $this->$task($options);		
		} 
		showStatus($res?0:-1);
	}
	
	protected function cleanAll(&$options=array())
	{
		return $this->doAll('clean', $options);
	}
			
	protected function clean(&$options=array())
	{
		$res = false;
		
		$name = $this->request('name');
		$task = 'clean'.ucfirst($name);
		
		if (method_exists($this, $task)) {
			$res = $this->$task($options);		
		} 
		
		showStatus($res?0:-1);
	}
}
