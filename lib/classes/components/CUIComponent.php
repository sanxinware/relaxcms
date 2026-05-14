<?php

class CUIComponent extends CComponent
{
	/**
	 *  模板
	 */
	protected $_tpl;
	
	/**
	 * 模板上级目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_ptdir;
	
	/**
	 * 模板目录
	 *  
	 */
	protected $_tdir;
	
	
	/**
	 * 默认模板目录
	 * 
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_tdir;
	
	protected $_sbt = false;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_tpl = $name;		
	}
	
	function CUIComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		parent::_init();
				
		$tplname = isset($this->_options['tplname'])?$this->_options['tplname']:'default';
		$compath = dirname($this->_options['classfile']);		
		$ptdir = dirname($compath).DS.'templates';
		//ptdir
		$this->_ptdir = $ptdir;		
		//模板目录
		$this->_tdir = $ptdir.DS.$tplname;
		//默认目录
		$this->_default_tdir = $ptdir.DS."default";
	}
		
	
	/* ===============================================================================
	 * JS/CSS Functions
	 * =============================================================================*/
	
	protected function initJSCSS(&$options=array())
	{
		//$browser = get_browser(null, true);
		
		$langname = $options['_lang'];
		$thename = $options['_thename'];
		$tplname = $options['_tplname'];
		
		$r = Factory::GetRequest();
		$leie9 = $r->isLeIE9();
		
		//css列表
		$jscssdb = array(
				'plugins'=>array(
					'fixedbrowser_leie9'=> array(
						'enable'=>$leie9,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'respond'=>'respond.min.js',
							'excanvas'=>'excanvas.min.js',			
							),
						),
					
					'font_awesome'=> array(
						'name'=>'FontAwesome',
						'version'=>'4.4.0',						
						'description'=>'Font Awesome 4.4.0 by @davegandy - http://fontawesome.io - @fontawesome',
						'licensename'=>'SIL&MIT',
						'licenseurl'=>'http://fontawesome.io/license',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'core'=>'font-awesome/css/font-awesome.min.css',
							),
						'js' => array(),
						),
					'simple_line_icons'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'simple-line-icons/simple-line-icons.min.css',
							),
						'js' => array(),
						),
					
					'jquery'=> array(
						'name'=>'jQuery',
						'version'=>'1.12.4',						
						'description'=>'jQuery JavaScript Library',
						'licensename'=>'MIT',
						'licenseurl'=>'https://jquery.org/license/',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'jquery.min.js',
							),
						),
					//应用迁移辅助插件（jQuery高级版本兼容低级版本辅助插件）
					'jquery_migrate'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-migrate.min.js',
							),
						),
					
					'jquery_ui'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-ui/jquery-ui.min.js',
							),
						),
					'bootstrap'=> array(
						'name'=>'Bootstrap',
						'version'=>'3.3.7',						
						'description'=>'Bootstrap JavaScript Library',
						'licensename'=>'MIT',
						'licenseurl'=>'https://github.com/twbs/bootstrap/blob/master/LICENSE',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap/css/bootstrap.min.css',
							),
						'js' => array(
							'core'=>'bootstrap/js/bootstrap.min.js',
							),
						),
						
					
					
					'jquery_slimscroll'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-slimscroll/jquery.slimscroll.min.js',
							),
						),
					
					'jquery_blockui'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.blockui.min.js',
							),
						),
					
					'jquery_cokie'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.cokie.min.js',
							),
						),/*
										
					'icheck'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'icheck/skins/all.css',
						),
					'js' => array(
						'core'=>'icheck/icheck.min.js',
						),
					),
					'jquery_uniform'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'uniform/css/uniform.default.css',
						),
					'js' => array(
						'core'=>'uniform/jquery.uniform.min.js',
						),
					),*/
					
					'jquery_validation'=>array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery-validation/js/jquery.validate.min.js',
							),
						),
					
					'jquery_form'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'jquery.form.min.js',
							),
						),
					'jquery_backstretch'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'backstretch/jquery.backstretch.min.js',
							),
						),
					
					
					'bootstrap_switch'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-switch/css/bootstrap-switch.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-switch/js/bootstrap-switch.min.js',
							),
						),
					
					
					'bootstrap_toastr'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-toastr/toastr.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-toastr/toastr.min.js',
							),
						),
					
					'bootstrap_hover_dropdown'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js'
							),
						),
					'bootstrap_table3'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-table/bootstrap-table3.css',
							),
						'js' => array(
							'core'=>'bootstrap-table/bootstrap-table3.js',
							),
						),
					
					
					'tagsinput'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-tagsinput/bootstrap-tagsinput.css',
							'typeahead'=>'bootstrap-tagsinput/bootstrap-tagsinput-typeahead.css',
							),
						'js' => array(
							'core'=>'bootstrap-tagsinput/bootstrap-tagsinput.min.js',
							//'typeahead'=>'typeahead/handlebars.min.js',
							//'typeahead_bundle'=>'typeahead/typeahead.bundle.min.js',
							),
						),
					
					
					'typeahead'=> array(
						'enable'=>false,
						'css' => array(
							'typeahead'=>'typeahead/typeahead.css',
							),
						'js' => array(
							'typeahead'=>'typeahead/handlebars.min.js',
							'typeahead_bundle'=>'typeahead/typeahead.bundle.min.js',
							),
						),
					
					
					//<script src="../assets/pages/scripts/components-multi-select.min.js" type="text/javascript"></script>
					'multiselect'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-select/css/bootstrap-select.min.css',
							'jquery-multi-select'=>'jquery-multi-select/css/multi-select.css',
							),
						'js' => array(
							'core'=>'bootstrap-select/js/bootstrap-select.min.js',
							'jquery_multi-select'=>'jquery-multi-select/js/jquery.multi-select.js',
							),
						),
					
					'datepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-datepicker/css/bootstrap-datepicker3.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-datepicker/js/bootstrap-datepicker.min.js',
							'locale-zh-CN'=>'bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js',
							),
						),
					'datetimepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
							'locale-zh-CN'=>'bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js',
							),
						),
					'timepicker'=> array(
						'enable' => false,
						'css' => array(
							'core'=>'bootstrap-timepicker/css/bootstrap-timepicker.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-timepicker/js/bootstrap-timepicker.min.js',
							'locale-zh-CN'=>'bootstrap-timepicker/js/locales/bootstrap-timepicker.zh-CN.js',
							),
						),
					
					
					
					'select2'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'select2/select2.css',
							),
						'js' => array(
							'core'=>'select2/select2.min.js',
							),
						),
					
					'bootbox'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'bootbox/bootbox.min.js',
							),
						),
					
					'underscore'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'',
							),
						'js' => array(
							'core'=>'underscore.js',
							),
						),
					
					
					
					'jquery_fileupload'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'jquery-file-upload/css/jquery.fileupload.css',
							'fileupload_ui'=>'jquery-file-upload/css/jquery.fileupload-ui.css',
							'blueimp'=>'jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css',
							'ladda'=>'ladda/ladda-themeless.min.css',
							
							),
						'js' => array(
							'widget'=>'jquery-file-upload/js/vendor/jquery.ui.widget.js',
							'ladda_spin'=>'ladda/spin.min.js',
							'ladda_ladda'=>'ladda/ladda.min.js',
							'transport'=>'jquery-file-upload/js/jquery.iframe-transport.js',
							'fileupload'=>'jquery-file-upload/js/jquery.fileupload.js',
							
							//'tmpl'=>'jquery-file-upload/js/vendor/tmpl.min.js',
							'load-image'=>'jquery-file-upload/js/vendor/load-image.min.js',
							'fileupload-ui'=>'jquery-file-upload/js/jquery.fileupload-ui.js',
							'blueimp'=>'jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js',
							'fileupload-process'=>'jquery-file-upload/js/jquery.fileupload-process.js',
							'fileupload-image'=>'jquery-file-upload/js/jquery.fileupload-image.js',
							'fileupload-audio'=>'jquery-file-upload/js/jquery.fileupload-audio.js',
							'fileupload-video'=>'jquery-file-upload/js/jquery.fileupload-video.js',
							'fileupload-validate'=>'jquery-file-upload/js/jquery.fileupload-validate.js',
							
							),
						),
					
					'fancybox'=> array(
						'enable'=>false,
						'css' => array(
							'fancybox'=>'fancybox/source/jquery.fancybox.css'),
						'js' => array(
							'mixitup'=>'jquery-mixitup/jquery.mixitup.min.js',
							'fancybox'=>'fancybox/source/jquery.fancybox.pack.js',
							),
						),
					'gtreetable'=> array(
						'enable'=>false,
						'css' => array(
							'gtreetable'=>'bootstrap-gtreetable/bootstrap-gtreetable.min.css'),
						'js' => array(
							'gtreetable'=>'bootstrap-gtreetable/bootstrap-gtreetable.min.js',
							),
						),
					
					'datatables'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'datatables/plugins/bootstrap/dataTables.bootstrap.css',
							),
						'js' => array(
							'core'=>'datatables/media/js/jquery.dataTables.min.js',
							'dt_tableTools'=>'datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
							'dt_colreorder'=>'datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
							'dt_scroller'=>'datatables/extensions/Scroller/js/dataTables.scroller.min.js',
							'dt_bootstrap'=>'datatables/plugins/bootstrap/dataTables.bootstrap.js',
							),
						),
					/*
					'datatables'=> array(
					'enable'=>false,
					'css' => array(
						'core'=>'datatables/datatables.min.css',
						'datatables_bootstrap'=>'datatables/plugins/bootstrap/datatables.bootstrap.css',
						),
					'js' => array(
						'core'=>'datatables/datatables.all.min.js',
						'datatables_bootstrap'=>'datatables/plugins/bootstrap/datatables.bootstrap.js',
						),
					),*/
					
					'bootstraptable'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-table/bootstrap-table.min.css',
							),
						'js' => array(
							'core'=>'bootstrap-table/bootstrap-table2.js',
							'bootstrap-table-treegrid'=>'bootstrap-table/extensions/treegrid/bootstrap-table-treegrid.min.js',
							),
						),
					'dropzone'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'dropzone/css/dropzone.css',
							),
						'js' => array(
							'core'=>'dropzone/dropzone.min.js',
							),
						),	
					
					
					'treegrid'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'jquery-treegrid/css/jquery.treegrid.css',
							),
						'js' => array(
							'core'=>'jquery-treegrid/js/jquery.treegrid.min.js',
							),
						),	
					
					'ckeditor'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'ckeditor/ckeditor.js',
							),
						),	
					
					//summernote
					/*'sneditor'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'bootstrap-summernote/summernote.css',
							),
						
						'js' => array(
							'core'=>'bootstrap-summernote/summernote.min.js',
							),
						),*/
					
					//videojs
					'videojs'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'videojs/css/videojs.css',
							),
						
						'js' => array(
							'core'=>'videojs/js/videojs.js',
							),
						),
					
					
					'crypto'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'crypto-js.js',
							),
						),	
					'layer'=> array(
						'name'=>'LayUI',
						'version'=>'3.1.1',						
						'description'=>'Web弹层组件 http://layer.layui.com/',
						'licensename'=>'MIT',
						'licenseurl'=>'https://gitee.com/sentsin/layui/blob/master/LICENSE',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'layer/layer.js',
							),
						),
					
					'jcrop'=> array(
						'name'=>'jcrop',
						'version'=>'0.9.12',						
						'description'=>'Query Image Cropping Plugin - released under MIT License',
						'licensename'=>'MIT',
						'licenseurl'=>'http://github.com/tapmodo/Jcrop',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							'jcrop'=>'jcrop/css/jquery.Jcrop.min.css'),
						'js' => array(
							'jcrop_color'=>'jcrop/js/jquery.color.js',
							'jcrop'=>'jcrop/js/jquery.Jcrop.min.js',
							),
						),	
					
					
					'bgallery'=> array(
						'name'=>'Gallery',
						'version'=>'3.4.0',						
						'description'=>'blueimp Gallery is a touch-enabled, responsive and customizable image and video
							gallery',
						'licensename'=>'MIT',
						'licenseurl'=>'https://github.com/blueimp/Gallery',
						'showlicense'=>true,
						
						'enable'=>false,
						'css' => array(
							//'gallery'=>'blueimp-gallery/css/blueimp-gallery.min.css'
							),
						'js' => array(
							'gallery'=>'blueimp-gallery/js/blueimp-gallery.min.js',
							//'jquery_gallery'=>'jcrop/js/jquery.blueimp-gallery.min.js',
							),
						),
						
					'bootstrapswitch'=> array(
						'name'=>'Bootstrap-switch',
						'version'=>'3.3.2',						
						'description'=>'Turn checkboxes and radio buttons in toggle switches',
						'licensename'=>'APACHE 2.0',
						'licenseurl'=>'http://www.apache.org/licenses/LICENSE-2.0',
						'showlicense'=>true,						
						'enable'=>false,
						'css' => array(
							'gallery'=>'bootstrap-switch/css/bootstrap-switch.min.css'),
						'js' => array(
							'gallery'=>'bootstrap-switch/js/bootstrap-switch.min.js',
							),
						),
					'bootstrapwizard'=> array( //bootstrap-wizard
						'name'=>'Bootstrap-wizard',
						'version'=>'1.0',						
						'description'=>'jQuery twitter bootstrap wizard plugin',
						'licensename'=>'MIT and GPL',
						'licenseurl'=>'http://www.gnu.org/licenses/gpl.html',
						'showlicense'=>true,						
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'bwizard'=>'bootstrap-wizard/jquery.bootstrap.wizard.min.js',
							),
						),
						
						
					'treenav'=> array(
						'name'=>'treenav',
						'version'=>'0.1.0',						
						'description'=>'treenav',
						'licensename'=>'MIT',
						'licenseurl'=>'http://github.com/relaxcms/treenav',
						'showlicense'=>false,
						
						'enable'=>false,
						'css' => array(
							'treenav'=>'treenav/css/treenav.css'),
						'js' => array(
							'treenav'=>'treenav/js/treenav.js',
							),
						),	
						
						/* amcharts5 https://github.com/amcharts/amcharts5*/						
						'amcharts5'=> array(
							'name'=>'amcharts5',
							'version'=>'5.1.12',						
							'description'=>'amCharts 5 is the fastest, most advanced amCharts data vizualization library, ever.',
							'licensename'=>'amCharts',
							'licenseurl'=>'https://github.com/amcharts/amcharts5',
							'showlicense'=>false,							
							'enable'=>false,
							'css' => array(
								'amcharts5'=>'amcharts5/css/amcharts5.css'),
							'js' => array(
								'amcharts5'=>'amcharts5/index.js',
								'amcharts5_xy'=>'amcharts5/xy.js',
								'amcharts5_Animated'=>'amcharts5/themes/Animated.js',
								'amcharts5_zh_Hans'=>'amcharts5/locales/zh_Hans.js',
								),
						),
						
					//touchspin						
					'touchspin'=> array(
						'name'=>'touchspin',
						'version'=>'3.0.1',						
						'description'=>'Bootstrap TouchSpin',
						'licensename'=>'Apache2.0',
						'licenseurl'=>'http://www.apache.org/licenses/LICENSE-2.0',
						'showlicense'=>true,						
						'enable'=>false,
						'css' => array(
							'touchspin'=>'bootstrap-touchspin/bootstrap.touchspin.min.css'),
						'js' => array(
							'touchspin'=>'bootstrap-touchspin/bootstrap.touchspin.min.js',
							),
						),
						
					),				
				
				
				'global' => array(					
					'encrypt'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'js/encrypt.min.js',
							),
						),
					
					'datatable'=> array(
						'enable'=>false,
						'css' => array(),
						'js' => array(
							'core'=>'js/datatable.min.js',
							),
						),
					'fileview'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/fileview.css'
							),
						'js' => array(
							'core'=>'js/fileview.js',),
						),
					'bupload'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/bupload.css'
							),
						'js' => array(
							'core'=>'js/bupload.js',),
						),
					
					'sneditor'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/sneditor.css'
							),
						'js' => array(
							'core'=>'js/sneditor.js',),
						),
					'listview'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/listview.css'
							),
						'js' => array(
							'core'=>'js/listview.js',),
						),
					'video'=> array(
						'enable'=>false,
						'css' => array(
							'core'=> 'css/video.css'
							),
						'js' => array(
							'core'=>'js/video.js',),
						),
					
					'tileupload'=> array(
						'enable'=>false,
						'css' => array(
							//'core'=>'css/tileupload.min.css',
							),
						'js' => array(
							'core'=>'js/tileupload.min.js',
							),
						),
					
					'fileupload'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/fileupload.min.css',
							),
						'js' => array(
							'core'=>'js/fileupload.min.js',
							),
						),
					'fileselector'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/fileselector.min.css',
							),
						'js' => array(
							'core'=>'js/fileselector.min.js',
							),
						),
					
					'gallery'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/gallery.min.css',
							),
						'js' => array(
							'core'=>'js/gallery.min.js',
							),
						),
						
					'cropimg'=> array(
						'enable'=>false,
						'css' => array(
							'core'=>'css/cropimg.css',
							),
						'js' => array(
							'core'=>'js/cropimg.js',
							),
						),	
					),
				);
		
		
		$this->_jscssdb = $jscssdb;
	}
	
	public function enableJSCSS($jscssmodules=array(), $enable=true)
	{
		if (!is_array($jscssmodules))
			$jscssmodules = explode(',', $jscssmodules);
		
		
		foreach ($jscssmodules as $key => $name) {
			
			$pkey = '';		
			foreach ($this->_jscssdb as $k2 => $v2) {
				
				if (isset($v2[$name])) {
					
					$pkey = $k2;
					break;
				}
			}
			
			if ($pkey)
				$this->_jscssdb[$pkey][$name]['enable'] = $enable;
		}
		
		return true;
	}
	
	
	protected function getJSCSSDB(&$cssdb, &$jsdb, &$options=array())
	{
		$cssdb = array();
		$jsdb = array();
		
		$_dstroot = $options['_dstroot'];
		$_theroot = $options['_theroot'];
		$basedirs = array(
				'root'=>$_dstroot.'',
				'plugins'=>$_dstroot.'/plugins',
				'global' =>$_dstroot,
				);
		
		
		foreach($this->_jscssdb as $key=>$v)
		{
			//模块
			$bdir = $basedirs[$key];
			foreach ($v as $k2 => $m) {
				if ($m['enable']) {
					
					//css, js
					if (isset($m['css'])) {
						foreach ($m['css'] as $k3=> $v3) {
							if($v3) {
								$_newkey = 'css_'.$k2.'_'.$k3;
								$cssdb[$_newkey] = $bdir.'/'.$v3;
							}
						}						
					}
					
					if (isset($m['js'])) {
						
						foreach ($m['js'] as $k3 => $v3) {
							if($v3) {
								$_newkey = 'js_'.$k2.'_'.$k3;
								$jsdb[$_newkey] = $bdir.'/'.$v3;
							}
						}						
					}
					
				}
			}			
		}
		return true;
	}
	
	protected function setJSCSS($name, $modJSCSS=array(),$type='global')
	{
		$modJSCSS['enable'] = true;
		$this->_jscssdb[$type][$name] = $modJSCSS;
	}
	
	/* ===============================================================================
	 * TAB Functions
	 * =============================================================================*/
	
	protected function initActiveTab($nr, $force_active_id=-1, $selector='')
	{
		$tabs = parent::initActiveTab($nr, $force_active_id);
		foreach ($tabs as $key => $v) {
			$this->assign('navtab'.$v['id'], $v);
		}		
		
		if ($selector) {
			$sdb = get_i18n($selector);
			foreach ($tabs as $key => &$v) {
				if (isset($sdb[$v['id']]))
					$v['title'] = $sdb[$v['id']];
			}
		}
		
		$this->assign('navtabs', $tabs);
		
		return $tabs;
	}
	
	protected function setHistory($options)
	{
		$m = Factory::GetModel('history');
		$m->setHistory($options);
		return true;
	}
	
	
	/* ===============================================================================
	 * TASK Functions
	 * =============================================================================*/
	
	protected function show(&$options=array())
	{
		return true;
	}
	
	protected function ajaxSystemTime(&$options=array())
	{
		showStatus(0,array('systime'=>tformat_cstdatetime(time())));
	}
	
	protected function ajaxLunar(&$options=array())
	{
		$lunar = Factory::GetLunar();		
		$data = $lunar->getNowForView();		
		showStatus(0, $data);
	}
	
	//nopic/w/h
	protected function nopic(&$options=array())
	{
		$offset = $options['vpath_offset'];
		$width = isset($options['vpath'][$offset])?$options['vpath'][$offset++]:640;
		$height = isset($options['vpath'][$offset])?$options['vpath'][$offset++]:480;
		$img = Factory::GetImage();	
		//$img->mknopic("请选择图片(大小:640x480)", $width, $height);	
		$img->mknopic("Select IMG(640x480)", $width, $height);	
		exit;
	}	
	
	protected function showimg(&$options=array())
	{
		$offset = $options['vpath_offset'];
		$width = isset($options['vpath'][$offset])?$options['vpath'][$offset++]:640;
		$height = isset($options['vpath'][$offset])?$options['vpath'][$offset++]:480;
		$img = Factory::GetImage();
		$imgfile = isset($options['imgfile'])?$options['imgfile']:false;
		if (!$imgfile || !is_file($imgfile)) {
			$img->mknopic("Select IMG({$width}x{$height})", $width, $height);	
		} else {
			header("Content-type: image/png");
			$res = readfile($imgfile);	
		}
		exit;
	}
	
	protected function cropimg(&$options=array())
	{
		$this->setTpl("cropimg");
		$this->enableJSCSS('jcrop');
	}
	
	protected function __docropimg(&$options=array())
	{
		$id = $this->_id;
		
		$x = get_int('x');
		$y = get_int('y');
		$w = get_int('w');
		$h = get_int('h');
		
		//$dstimgfile='', $target_w=128, $target_h=128
		
		$dstimgfile = isset($options['dstimgfile'])?$options['dstimgfile']:RPATH_CACHE.DS."cropimg.png";
		$target_w = isset($options['width'])?$options['width']:128;
		$target_h = isset($options['height'])?$options['height']:128;
		
		
		if (!$id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		if (!$dstimgfile) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no dstimgfile '$dstimgfile'!");
			return false;
		}
		
		//srcfile
		$file = Factory::GetModel('file');
		$srcfile = $file->getImagePath($id);
		if (!$srcfile) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "get getImagePath failed! id=$id!");
			return false;
		}	
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, "srcfile=$srcfile!");
		$m = Factory::GetImage();
		$res = $m->cropImage($srcfile, $x, $y, $w, $h, $dstimgfile, $target_w, $target_h);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call cropImage failed!");			
		}
		
		return $res;
	}
	
	
	protected function docropimg(&$options=array())
	{
		$res = $this->__docropimg($options);		
		showStatus($res?0:-1);
	}
	
	protected function delcropimg(&$options=array())
	{
		showStatus(-1);
	}
		
	protected function selectlink(&$options=array())
	{
		$this->setTpl("selectlink");
		$this->_modname = 'linkcontent';
		$this->show($options);
				
		return true;
	}

	protected function queryForInput(&$options=array())
	{
		$modname = $this->request('modname');
		$name = $this->request('name');
		$val = $this->request('val');
		$m = Factory::GetModel($modname);
		
		$params = array();
		
		$this->getParams($params);
		
		$res = $m->queryForInput($name, $val, $params, $options);
		showStatus($res?0:-1, $res);
	}
	
	protected function initParamsForModelselector(&$params, &$options=array())
	{
		//__hidefields
		
		//getHideFieldsForModelSelector();
		$m = $this->getModel();
		$m->setHideFieldsForModelSelector($params);		
	}
	
	protected function modelselector(&$options=array())
	{
		$modname = $this->request('modname');
		$formodname = $this->request('formodname');
		$name = $this->request('name');
		$val = $this->request('val');
		$singleselect = $this->requestInt('singleselect');
				
		$res = $this->show($options);
		
		$this->setTpl("modelselector");
		
		$this->assign('treeview', 0);
		
		return $res;
	}
	
	
	protected function initRequestParams()
	{
		//for hidden request
		$hiddrequetdb = array();
		foreach ($_REQUEST as $key=>$v) {
			if (!is_array($v))
				$hiddrequetdb[$key] = $v;
			
		}
		$this->assign('requestparams', $hiddrequetdb);
	}
	

	protected function treemodelselector(&$options=array())
	{
		$modname = $this->request('modname');
		$formodname = $this->request('formodname');
		$name = $this->request('name');
		$val = $this->request('val');
		
		if ($this->_sbt) { //选中
			$params = $_REQUEST;
			//rlog($params);
			$m = Factory::GetModel($formodname);
			$res = $m->treemodeselectorForInput($name, $val, $params, $options);
			showStatus($res?0:-1, $res);
		}
				
		
		$this->_modname = $modname;
					
		$id = $this->_id;
		$tablename = $this->_modname;
		$table_id = $tablename.rand();
		$dlg = $options['dlg'];
		
		
		$sortName = isset($_COOKIE['sortName'])?$_COOKIE['sortName']:'';
		$sortOrder = isset($_COOKIE['sortOrder'])?$_COOKIE['sortOrder']:'';
		
		$m = $this->getModel();
		
		
		
		//查询
		$parentdb = array();
		$m->getParents($id, $parentdb);
		//keyword
		$this->getParams($params);
		if (!$params)
			$params['pid']=$id;
		if ($sortName)
			$params['__orderby'] = array($sortName=>$sortOrder);
		
		$this->initParamsForShow($params, $options);

		
		$rows = $m->selectForView($params, $options);
		
		$modinfo = $m->getModelInfo();
		$fdb = $modinfo['fdb'];
		$pkey = $modinfo['pkey'];
		$sfdb = $m->getFieldsForSearch($params, $options);
		
		
		$_baseurl = $options['_base'].'/'.$this->_task."?dlg=$dlg&modname=$modname&formodname=$formodname&name=$name&val=$val";
		foreach($rows as $key=>&$v)  {
			$name = $v['name'];
			$id = $v['id'];
			if ($m->hasChildren($id))
				$v['_name'] = "<a href='$_baseurl&id=$id'>$name</a>";
		}
		
		
		//array_reverse($parentdb);
		//nav
		$nav = '';
		foreach ($parentdb as $k2 => $v2) {
			$nav = "<i class='fa fa-angle-right'> </i> <a href='$_baseurl&id=$v2[id]'> $v2[name] </a>".$nav;
		}
		
		
		$this->initRequestParams();
		
		$this->assign('sortName', $sortName);
		$this->assign('sortOrder', $sortOrder);			
		$this->assign('nav', $nav);			
		$this->assign('table_id', $table_id);			
		$this->assign('tablename', $tablename);
		$mi18n = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);		
		$this->assign('fdb', $fdb);
		$this->assign('sfdb', $sfdb);
		$this->assign('pkey', $fdb[$pkey]);
		$this->assign('tabledata', $rows);		
		$this->assign('mbaseurl', $_baseurl);		
		
		$this->setTpl('treeviewselector');
		
		return $rows;
	}
	
	/* ===============================================================================
	 * TPL Functions
	 * =============================================================================*/
	
	
	protected function getTplFile(&$options=array())
	{
		//模版所在目录: RUN APP TEMPLATES/default
		$tdir = $options['tdir'];
				
		//模版
		$tpl = $this->_tpl;
		$tpl_filename = $tpl.'.htm';		
		if (!isset($options['cfg_tdir']) || !file_exists($options['cfg_tdir'].DS.$tpl_filename)) {
			$file = $tdir.DS.$tpl_filename;
			if (!file_exists($file)) {				
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");
				$default_tdir = $options['app_tdir'];//app :<APPDIR>/templates/default
				$file = $default_tdir.DS.$tpl_filename;
				if (!file_exists($file)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");		
					$default_tdir = RPATH_TEMPLATE_DEFAULT;// <ROOTDIR>/templates/default
					$file = $default_tdir.DS.$tpl_filename;					
					if (!file_exists($file)) {						
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");		
						$file = RPATH_LIBTEMPLATE_DEFAULT.DS.$tpl_filename;	//内置												
						if (!file_exists($file)) {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");				
							$file = $default_tdir.DS.'404.htm';
							if (!file_exists($file)) {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");				
								$file = RPATH_LIBTEMPLATE_DEFAULT.DS.'404.htm';
							} else {
								rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: use TPL '$file'!");	
							}
						} else {
							rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: use TPL '$file'!");	
						}
					} else {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: use TPL '$file'!");	
					}
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "use TPL '$file'!");	
				}
			}
		} else {
			$file = $options['cfg_tdir'].DS.$tpl_filename;
		}
		
		if (!file_exists($file)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "Not found TPL '$file'!");
			return false;
		} else {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "Found TPL '$file'!");	
		}
		
		//模板编译
		$t = Factory::GetTemplate();		
		$cpl_file = $t->compileTemplate($file, $options, $tpl_filename);
		
		return $cpl_file;
	}
	
	protected function initModuleAttribs($name, &$attribs, $options=array())
	{
		//TODO...
		//$attribs['dlg'] = 1;
	}
	
	/**
	 * 解析RDOC标记
	 *
	 * eg: <rdoc:include file="head.htm" />
	 * 
	 * @param mixed $type This is a description
	 * @param mixed $name This is a description
	 * @param mixed $attrs This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function __parse_rdoc_tags($type, $name, $attribs, &$options=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ....................name=$name ....");
		
		$attribs['rundir'] = $this->_options['rundir'];
		$res = false;
		switch($type) {
			case "module":
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $attribs);
				$this->initModuleAttribs($name, $attribs, $options);
				$mod = Factory::GetModule($name, $attribs);
				if ($mod)
					$res = $mod->render($options, $this->_var);
				break;
			default:
				break;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT.");
		
		return $res;
	}
	
	
	//解析模板
	protected function parseTemplate($data, &$options=array())
	{
		$replace = array();
		$matches = array();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN ....................");
		if (($matches = matchModule($data))) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2 ....................");
			
			$nr = count($matches[1]);
			for($i=0; $i<$nr; $i++) {
				
				$attribs = attr2array( $matches[4][$i] );
				//$attribs = array_merge($this->_var, $attribs);
				
				$type  = $matches[2][$i];
				
				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				//$attribs['args'] = $matches[6][$i];				
				
				//合并
				$replace[$i] = $this->__parse_rdoc_tags($type, $name, $attribs, $options);
			}
			
			$data = str_replace($matches[0], $replace, $data);
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT ..");
		return $data;
	}
	
	
		
		
	/**
	 * 模板加载
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function loadTemplate(&$options = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $options);
		//系统变量
		$sys_errors = get_errors_html();		
		//$sys_error = get_error();	
		
		//全局准备模板所使用变量
		extract($this->_var);
		//当前方法
		extract($options);
		
		//css and js
		$this->getJSCSSDB($cssdb, $jsdb, $options);
		//$_dstroot/js/$component.js
		//rlog(RPATH_DIST.DS.'js'.DS.$component.'.js');
		$hasComponentJS = file_exists(RPATH_STATIC.DS.'js'.DS.$component.'.js')?true:false;
				
		$i18n = get_i18n(); 
		//语言
		extract($i18n);
		
		//常用方法名称'edit','add'
		if (isset($i18n['str_'.$this->_task])) {
			$str_task = $i18n['str_'.$this->_task];			
		} else {
			$str_task = $this->_task;
		}
			
		//postion
		$sys_position = $sys_component_name.' / '.$str_task;
		
		//$t and $T variable
		//t
		/*if (isset($i18n['t_'.$this->_name]))
			$t = $i18n['t_'.$this->_name];	
		else
			$t = array();	
			*/
		$t = $options['_i18ndb'];
		
		$T = $i18n;
		
		//page global JS variable 'G'
		$_g = array();
		foreach($options as $k=>$v) {
			if ($k[0] == '_') {
				$_g[$k] = $v; 
			}
		}
		foreach($this->_var as $k=>$v) {
			if ($k[0] == '_') {
				$_g[$k] = $v; 
			}
		}
		//for old
		$_g['webroot'] = $options['_webroot'];
		$_g['base'] = $options['_base'];
		$_g['basename'] = $options['_basename'];
		$_g['lang'] = $options['_lang'];//fixed for old ver
		$_g['name'] = $options['sys_title'];
		$_g['title'] = $options['sys_title'];
		//$_g['syserror'] = $sys_errors;
		$_g['component'] = $options['component'];
		$_g['task'] = $options['task'];
		
		$_gjson = CJson::encode($_g);
		if (!$_gjson) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call CJson encode failed!", $_g);
			$_gjson = "{}";
		}		
		$sys_JS_G = "<script language='javascript'> var G = \n$_gjson;\n</script>";
		
		//当前用户
		//$_userinfo = Factory::GetApp()->getUserInfo();
		
		//id 
		$id = $this->_id;
		//加载
		$tpl_file = $this->getTplFile($options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN3...................................");
		

		ob_start();		
		if (file_exists($tpl_file)) {
			require $tpl_file;
		}
		
		$contents = ob_get_contents();
		
		//去utf-8 起始符
		$contents = strim_bom($contents);		
		ob_end_clean();	

		//解析
		$contents = $this->parseTemplate($contents, $options);
		
		return $contents;	
	}
	
	/* ===============================================================================
	 * Main Functions
	 * =============================================================================*/
	protected function initSbt(&$options=array())
	{
		//检查submit
		$name = $this->_name; //$options['_base']; //empty($options['_cname'])?$this->_name:$options['_cname'];
		
		$this->_sbt = is_sbt($name);
		//模块使用
		$options['issbt'] = $this->_sbt;
	}
	
	protected function initI18n(&$options=array())
	{
		parent::initI18n($options);
		
		$i18n = get_i18n();
		
		//common
		$_i18ndb = array();
		
		$tkey = 't_i18ndb';
		if (array_key_exists($tkey, $i18n)) 
			$_i18ndb = $i18n[$tkey];
		
		$tkey = 't_i18ndb_'.$this->_name;	
		if (array_key_exists($tkey, $i18n)) 
			$_i18ndb = array_merge($_i18ndb, $i18n[$tkey]);
		
		$key = $this->_task;
		$_i18ndb[$key] = isset($_i18ndb[$key])?$_i18ndb[$key]:$key;
		
		$options['_i18ndb'] = $_i18ndb;
	}
	
	
	/**
	 * 展现之前调用初始化
	 *
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function init(&$options=array())
	{
		//session_cache_limiter( "private, must-revalidate" ); 
		//if (!session_id())
		//	session_start();
		
		parent::init($options);
		
		if(!isset($options['tdir']))
			$options['tdir'] = $this->_tdir;
		if (!isset($options['default_tdir']))
			$options['default_tdir'] = $this->_default_tdir;
		
		$options['_dlg'] = $options['dlg'] = $this->requestInt('dlg');
		
		
		$this->initJSCSS($options);	
		$this->initSbt($options);	
	}
	
	protected function setSbt(&$options=array())
	{
		$name = $this->_name; //$options['_base']; //empty($options['_cname'])?$this->_name:$options['_cname'];
		$options['sbt'] = mk_sbt($name);
	}
	
	protected function fini(&$options=array())
	{
		parent::fini($options);
		$this->setSbt($options);		
	}
	
	
	
	public function render(&$options=array())
	{
		parent::render($options);
		$this->setHistory($options);
		$data = $this->loadTemplate($options);
		
		return $data;
	}
	
	
	protected function sendSecurityCode(&$options=array())
	{
		$type = $this->requestInt('type');
		
		$account = $this->request('account');	
		$m = Factory::GetModel('user');
		$res = $m->sendSecurityCode($account,$type);
		
		showStatus($res?0:-1);
	}
	
	
	public function setMessage($options, $level=null)
	{
		!$options['msg_backurl'] && $options['msg_backurl'] =  $this->_options['_base'];
		foreach($options as $key=>$v) 
			$this->_var[$key] = $v;
		
		$this->setTpl('message');
				
		Factory::GetApplication()->cleanMessage();
	}
	
	

	private function generate_string()
	{
		$cf = get_config();
		$res = $cf['seccodeonleynum'] == 1? "1234567890" : "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
		return $res;
	}

	/* Gen Captcha Image */
	protected function genCaptchaImage($show=false, $char_number=5, $font_size=12, $width = 88, $height = 23)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		//fonts = array("AntykwaBold", "Duality", "Jura", "StayPuft");
		$fonts = array("Duality", "Jura");
		$fontname = $fonts[array_rand($fonts)];
		$tt_font = RPATH_SUPPORTS.DS."fonts".DS.$fontname.".ttf";

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'ttfont='.$tt_font);
		
		$chars_number = rand(4, $char_number);		
		$string = $this->generate_string();
		
		//$im = imagecreate($width, $height);
		$im = imagecreatetruecolor($width, $height);
		if (!$im){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecreatetruecolor failed!w=".$width.',h='.$height);
		}
		
		/* Set a White & Transparent Background Color */
		$bgcolor = imagecolorallocatealpha($im, 255, 255, 255, 0); // (PHP 4 >= 4.3.2, PHP 5)
		if (!$bgcolor){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecolorallocatealpha failed!w=".$width.',h='.$height);
		}
		//填充
		imagefill($im, 0, 0, $bgcolor);
		
		$capt = "";
		for ($i=0; $i<$chars_number; $i++)
		{
			$char = $string[rand(0, strlen($string)-1)];
			$capt .= $char;
			$factor = 14;
			$x = ($factor * ($i + 1)) - 6;
			$y = rand(15, 17);
			$angle = rand(1, 15);
			$textcolor = imagecolorallocate($im, mt_rand(0,120), mt_rand(0,120), mt_rand(0,120));
			 
			//imagettftext — 用 TrueType 字体向图像写入文本
			$res = imagettftext($im, $font_size, $angle, $x, $y, $textcolor, $tt_font, $char);
			if (!$res){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!, $tt_font,char=$char,font_size=$font_size, angle=$angle, x=$x, y=$y");
			}
		}
		
		for ($i=0; $i<150; $i++){
			$fontcolor = imagecolorallocate($im,mt_rand(180,255),mt_rand(180,255),mt_rand(180,255));
			$res = imagesetpixel($im, mt_rand(0,$width), mt_rand(0,$height), $fontcolor);
			if (!$res){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!");
			}
		}
		
		$seccode = strtolower($capt);
		$_SESSION['seccode'] = strtolower($seccode); 
		
		if ($show) {
			header('Cache-control: private'); // IE 6 FIX
			header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT'); 
			header('Cache-Control: no-store, no-cache, must-revalidate'); 
			header('Cache-Control: post-check=0, pre-check=0', false); 
			header('Pragma: no-cache');
			/* Output the verification image */
			header("Content-type: image/png");
			$res = imagepng($im);
			imagedestroy($im);
			exit;
		} else {
			$secfile = RPATH_CACHE.DS."seccodecachefile_$seccode.png";
			$res = imagepng($im, $secfile);
			imagedestroy($im);			
			if (!$res){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call ImageColorAllocateAlpha failed!");
				return false;
			}
			$data = s_read($secfile);
			$data64 = 'data:image/png;base64,'.base64_encode($data);
			@unlink($secfile);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
			return $data64;
		}
	}	

	protected function genRequestToken(&$options=array())
	{
		//公key
		$pkey = $this->setPKey($options);
		$name = $this->_name; //$options['_base']; //empty($options['_cname'])?$this->_name:$options['_cname'];
		
		$token = array();
		$token['pkey'] = $pkey;
		$token['seccodeimg'] = $this->genCaptchaImage();
		$token['sbt'] = mk_sbt($name);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '__aeskey='.$pkey.',sbt='.$token['sbt'].',name='.$name);
		
		
		return $token;

	}

	protected function getRequestToken(&$options=array())
	{
		$token = $this->genRequestToken($options);
		showStatus(0, $token);
	}

	protected function checkSecCode($captcha)
	{
		$cf = get_config();
		if (!isset($cf['enable_captcha']) || !$cf['enable_captcha'])
			return true;

		$captcha = strtolower($captcha);
		$sessionCap = $_SESSION['seccode'];
		if ($captcha != $sessionCap){
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_login_invalid_seccode");
			return false;
		}
		return true;
	}
	
	public function setPKey(&$options=array())
	{
		if (!isset($options['__aeskey'])) {
			//公key
			$pkey = md5(time());
			$this->assignSession('__aeskey', $pkey);		
			$this->assign('pkey', $pkey);				
			
			$options['__aeskey'] = $pkey;
		}
		return $options['__aeskey'];
	}
	
	
	protected function showepassword(&$options=array())
	{
		$name = $this->request('name');
		$m = $this->getModel();
		$params = $m->get($this->_id);
		$this->setTpl('showepassword');
		$epassword = '';
		if (!hasPrivilegeOf($this->_name, 'exec')) 
			$epassword = "无权限请联系管理员";
		else {
			//rlog(RC_LOG_DEBUG, __FILE__, 'epassword='.$params[$name]);
			$epassword = deSimpleString($params[$name]);
		}
			
		$this->assign('epassword', $epassword);
	}
	
	protected function qrcode(&$options=array())
	{
		return false;
	}
	
	protected function cache(&$options=array())
	{
		$m = $this->getModel();
		$res = $m->cache($options);
		showStatus($res?0:-1);
	}
	
}
