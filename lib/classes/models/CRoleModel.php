<?php
/**
 * @file
 *
 * @brief 
 * 
 * 角色管理类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CRoleModel extends CTableModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CRoleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = 'selector';
				//$f['show'] = false;	
				$f['edit'] = false;								
				break;
			case 'level':
				$f['edit'] = false;
				$f['show'] = false;									
				break;
			default:
				break;
		}

		return true;
	}
	
	protected function _initFeild()
	{
		parent::_initFeild();
		
		$this->newField('gdb', array('input_type'=>'custom', 'format'=>'custom', 'sort'=>99, 'show'=>false));		
	}
	
	protected function getActions($row=array(), &$options=array())
	{
		$actions = $this->_default_actions;
		
		if ($row['type'] == 1) { //内置
			unset($actions['del']);
		}
		
		return $actions;
	}
	
	
	
	/*protected function formatOperate($row, &$options=array())
	{
		$id = $row[$this->_pkey];
		
		$opt = "";
		if (hasPrivilegeOf($options['component'], 'edit')) 
			$opt .= "<button type='button' class='btn green btn-xs btn-circle tlink tooltips ' data-original-title='修改' data-id='$id' data-task='edit'>
				<i class='fa fa-edit' ></i></button> ";
		if (hasPrivilegeOf($options['component'], 'del') && $row['type'] != 1) 	
			$opt .= "<button type='button' class='btn red btn-xs btn-circle delete tooltips ' data-original-title='删除' data-id='$id'>
				<i class='fa  fa-trash-o' ></i></button>"; //icon-wrench / icon-trash 
		
		return $opt;
	}*/
		
	public function get($id)
	{
		$res = parent::get($id);
		if (!$res) {
			return false;
		}
				
		
				
		return $res;	
	}
	
	public function formatForView(&$row, &$options = array())
	{
		$res =  parent::formatForView($row, $options);
		
		//previewUrl for Listview
		$row['previewUrl'] = $options['_dstroot']."/img/group.png";
		
	}

	public function set(&$params, &$options=array())
	{
		$res = parent::set($params);
		if ($res) {
			$rid = $params['id'];						
			if (isset($params['gdb'])) {
				$m = Factory::GetModel('group2role');
				$filter = array('rid'=>$rid);
				$m->clean($filter);			
				$gdb = $params['gdb'];
				foreach($gdb as $key=>$v) {
					$params = array('rid'=>$rid, 'gid'=>$v);
					$res = $m->set($params);
				}
			}
		}
		return $res;
	}
	
	protected function add(&$params=array(), &$options=array())
	{
		$params['type'] = 2;
		$res = parent::add($params);
		
		return $res;
	}
	
	public function getRoleTitleById($id)
	{
		$res = parent::getRowById($id);
		return $res['title'];
	}


	/**
     * 组表
	 */
	protected function buildInputForGdb($params, $field, &$options=array())
	{
		$m = Factory::GetModel('group2role');
		$_params = array('rid'=>$params['id']);
		$udb = $m->select($_params);		
		
		$gids = array();
		foreach($udb as $key =>$v) {
			$gids[] = $v['gid'];
		}		

		$m = Factory::GetGroup();
		$gdb = $m->select();
						
		//生新checkbox.
		$group_checkbox = "<div class='group'>\n";
		foreach ($gdb as $key=>$v)
		{
			$checked = '';
			$gid = $v['id'];
			
			if (in_array($gid, $gids))
				$checked = 'checked';
			
			$group_checkbox .= "<label class='checkbox-inline'>\n";
			$group_checkbox .= "<input type='checkbox' name='params[gdb][]' value='$gid' $checked /> {$v['name']} \n";
			$group_checkbox .= "</label>\n";
			
		}	
		
		$group_checkbox .= "</div>\n";		

		return $group_checkbox;
	}
	
	public function formatCustomForView(&$row, $field, &$options = array())
	{
		$name = $field['name'];
		switch($name) {
			case 'gdb':
				$row['_gdb'] = $this->buildInputForGdb($row, $field, $options);
				break;
			default:
				break;
		}
	}
	
	protected function  buildInputCustom(&$field, $params, &$options=array())
	{
		$name = $field['name'];
		
		switch($name) {
			case 'gdb':
				return $this->buildInputForGdb($params,  $field, $options);
			default:
				return parent::buildInputCustom($field, $params, $options);
		}
	}
	
	
	public function del($id, &$options=array())
	{
		$info = $this->get($id);
		if (!$info)
			return false;
			
		if ($info['type'] == 1)
			return false;
			
		$res = parent::del($id, $options);
		return $res;
		
	}
}