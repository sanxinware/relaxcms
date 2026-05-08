<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CWeatherComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CWeatherComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function init(&$options=array())
	{
		parent::init($options);
		$css = array(
				'css' => array(
					'core'=>'css/weather.css',
					),
				);
		
		
		$this->setJSCSS('weather', $css);
	}
}
