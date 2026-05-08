<?php

/**
 * @file
 *
 * @brief 
 *   
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CApiComponent extends CUIComponent
{
	protected $_requestBody = null;
	protected $_requestParams = array();
		
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}

	public function CApiComponent($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	public function render(&$options=array())
	{
		if ($this->_options['method'] == 'POST' ) {
				
			$fp = fopen("php://input", "r");
			
			$body = '';
			while(!feof($fp)) 
			$body .= fread($fp, 4096);
			$this->_requestBody = $body;
			
			//body
			if ($this->_requestBody) {
				$params = CJson::decode($this->_requestBody);
				if ($params) {
					$params['apisync'] = true; //默认
					$this->_requestParams = $params;
					
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
					
				}
			}
		}

		$res = parent::render($options);
		return $res;
	}
	
	
	public function hello(&$options=array())
	{
		showStatus(-1);
		return true;
	}
}