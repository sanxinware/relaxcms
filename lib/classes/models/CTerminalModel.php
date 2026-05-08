<?php
/**
 * @file
 *
 * @brief 
 * 客户管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTerminalModel extends CModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'terminal';
		parent::__construct($name, $options);
	}
	
	public function CTerminalModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	
	
	
	public function getInfoByID($id, $options)
	{
		return false;
	}
	
	
	public function getByTid($id)
	{
		return $this->get($id);
	}
	
	
	
	
	
	
	
	
	protected function formatOperate3($v, $id, &$options=array())
	{
		$defOpt = parent::formatOperate2($v, $id, $options);
		
		$url = $options['_base'].'/restart/'.$id;
		$res = "<a href='$url' class='btn red btn-xs btn-circle tmilink needconfirm' action='button' data-original-title='重启' title='重启' data-id=$id data-task='restart' msg='确定重启终端吗？' > <i class='fa fa-refresh' ></i> </a>";
		$url = $options['_base'].'/pushcontent/'.$id;
		$res .= "<a href='$url' class='btn blue btn-xs btn-circle tmbox action='button' data-original-title='推送节目' title='推送节目' data-id=$id data-task='pushcontent'> <i class='fa fa-caret-square-o-right' ></i> </a>";
		// 
		$res .= $defOpt;
		
		return $res;
	}
}
