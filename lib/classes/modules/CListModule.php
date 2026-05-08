<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CListModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CListModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}

	protected function getContentModel($cataloginfo)
	{
		return Factory::GetModel('content');
	}
	
	protected function getCols()
	{
		return isset($this->_attribs['cols'])?intval($this->_attribs['cols']):4;		
	}

	protected function setColumn($udb)
	{
		$nr = count($udb);
		
		$nr_col = $this->getCols();
		$nr_row = ceil($nr/$nr_col);
		$col_width = floor(12/$nr_col);

		$this->assign('rows', $nr_row);	
		$this->assign('cols', $nr_col);	
		$this->assign('col_width', $col_width);	
		$this->assign('nr', $nr);

	}
	
	protected function getPageSize()
	{
		$scf = get_site_config();
		$default_page_size = $scf['page_size'];
		if ($default_page_size <= 0)
			$default_page_size = 20;
			
		$page_size = isset($_REQUEST['page_size'])?intval($_REQUEST['page_size']):$default_page_size;
		
		return $page_size;		
	}
	
	protected function mk_page_html($page, $total, $url, $stc=0, &$paginationinfo=array())
	{
		$first = "";
		$tmp = "";
		$pages = "";
		
		$paginationinfo['pagedb'] = array();
		
		if ($total <= 1 || !is_numeric($page))
		{
			return '';
		}
		else
		{
			
			$flag = 0;
			if ($stc == 1)
			{
				$arr = explode('.', $url, 2);
				$list_name = $arr[0];
				$ext_name = $arr[1];
				$firstpageurl = $url;
			}
			else
			{
				$firstpageurl = $url."page=1";
			}
			
			$pages = "<a href='$tmp' style='font-weight:bold'>&laquo;</a>";
			
			$paginationinfo['first'] = '<a href="'.$firstpageurl.'" class="firstPage" ><i class="fa fa-angle-double-left"></i></a>';
			
			if ($page == 1) {
				$prepageurl = ($stc == 1)?$url:$url."page=$page";									
			} else {
				$i = $page - 1;
				$prepageurl = ($stc == 1)?$list_name."_$i.$ext_name":$url."page=$i";
			}
			
			if ($page >= $total) {
				$nextpageurl = ($stc == 1)?$url:$url."page=$total";									
			} else {
				$i = $page + 1;
				$nextpageurl = ($stc == 1)?$list_name."_$i.$ext_name":$url."page=$i";
			}
			
			$paginationinfo['prepage'] = '<a href="'.$prepageurl.'" class="prevPage" ><i class="fa fa-angle-left"></i></a>';
			$paginationinfo['nextpage'] = '<a href="'.$nextpageurl.'" class="nextPage" ><i class="fa fa-angle-right"></i></a>';				
			
			for ($i=$page-3; $i<=$page-1; $i++)
			{
				if ($i < 1) continue;
				
				if ($stc == 1)
				{
					if ($i == 1)
					{
						$tmp = $url;
					}
					else
					{
						$tmp = $list_name."_$i.$ext_name";
					}
				}
				else
				{
					$tmp = $url."page=$i";
				}
				
				$pages .= "<a href='$tmp'>$i</a>";
				
				
				$paginationinfo['pagedb'][] = array('url'=>$tmp, 'current'=>0, 'pageno'=>$i);
				
			}
			
			$pages.="<b> $page </b>";
			
			$paginationinfo['pagedb'][] = array('url'=>($stc == 1)?$list_name."_$i.$ext_name":$url."page=$i", 'pageno'=>$page, 'current'=>1);
			
			if ($page < $total)
			{
				for ($i=$page+1; $i<=$total; $i++)
				{
					
					if ($stc == 1)
					{
						$tmp = $list_name."_$i.$ext_name";
					}
					else
					{
						$tmp = $url."page=$i";
					}
					
					$pages .= "<a href='$tmp'>$i</a>";
					
					$paginationinfo['pagedb'][] = array('url'=>$tmp, 'current'=>0, 'pageno'=>$i);
					
					$flag++;
					if ($flag == 4) 
						break;
				}
				
			}
			
			if ($stc == 1)
			{
				$lastpageurl = $list_name."_$total.$ext_name";
			}
			else
			{
				$lastpageurl = $url."page=$total";
			}
			
			$pages .= "<a href='$lastpageurl' style='font-weight:bold'>&raquo;</a> $str_page_no:$page/$total";
			
			$paginationinfo['last'] = '<a href="'.$lastpageurl.'" class="lastPage" ><i class="fa fa-angle-double-right"></i></a>';
			
			
			return $pages;
		}
		
	}
	
	protected function selectForListview($params, $cataloginfo, $options=array())
	{
		$m = $this->getContentModel($cataloginfo);
		
		$res =  $m->selectForListview($params, $options);
		
		$page = $res['page'];
		$nr_page = $res['nr_page'];
		$page_size = $res['page_size'];
		$total = $res['total'];
		
		$paginationinfo = array();
		$paginationinfo['total'] = $res['total'];
		$paginationinfo['nr_page'] = $res['nr_page'];
		$paginationinfo['page'] = $res['page'];
		$paginationinfo['page_size'] = $res['page_size'];
		$paginationinfo['num'] = $res['num'];
		$paginationinfo['from'] = ($page-1)*$page_size+1;
		$paginationinfo['to'] = $paginationinfo['from'] + $res['num']-1;
		
		$res['paginationinfo'] = $paginationinfo;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		
		
		
		return $res;
		
	}
	
	
	public function show(&$options=array())
	{
		$res = parent::show($options);
		
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):0;
		$num = isset($this->_attribs['num'])?intval($this->_attribs['num']):12;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$mid = isset($this->_attribs['mid'])?$this->_attribs['mid']:'';
		
		//$vmask = isset($this->_attribs['vmask'])?intval($this->_attribs['vmask']):3;
		$defaultview = isset($this->_attribs['view'])?$this->_attribs['view']:'large';
		
		$keyword = isset($this->_attribs['keyword'])?$this->_attribs['keyword']:'';

		
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$params = isset($_REQUEST['params'])?$_REQUEST['params']:array();
		
		$page_size = $this->getPageSize();
		
		
		
		$keyword && empty($params['__keyword']) && $params['__keyword'] = $keyword;
		
		if ($col >12 || $col < 1)		
			$col = 6;

		$params['page'] = $page;
		$params['page_size'] = $page_size;
		
		//$params['__keyword'] = $keyword;
		$all_view_list  = array(
				'large'=>array('name'=>'large', 'className'=>'fa-th-large'),
				'listimg'=>array('name'=>'listimg','className'=>'fa-th-list'),
				'detail'=>array('name'=>'detail','className'=>'fa-list'),
				'small'=>array('name'=>'detail','className'=>'fa-th'),
				);
		
		$m2 = Factory::GetModel('catalog');
		$cataloginfo = $m2->getForView($cid, $options);
		
		
		if ($cid > 0)
			$params['cid'] = $cid;
		
		
		//last_tileview_list2_<id>
		$view = isset($_COOKIE['last_tileview_list2_'.$cid])?$_COOKIE['last_tileview_list2_'.$cid]:$defaultview;
		switch ($view) {
			case 'listimg':
				$active_viewtype = 1;
				break;
			case 'detail':
				$active_viewtype = 2;
				break;
			default:
				$active_viewtype = $cataloginfo ? $cataloginfo['viewtype']:0;
				break;
		}
		
		$vmask = isset($this->_attribs['vmask'])?intval($this->_attribs['vmask']):($cataloginfo['viewmode']>0?$cataloginfo['viewmode']:1);
		if ($vmask < 0 || $vmask > 0xf)
			$vmask = 3;
		
		$enable_view_list  = array();
		if ($vmask&1)
			$enable_view_list['large'] = $all_view_list['large'];
		if ($vmask&2)
			$enable_view_list['listimg'] = $all_view_list['listimg'];
		if ($vmask&4)
			$enable_view_list['detail'] = $all_view_list['detail'];
		if ($vmask&8)
			$enable_view_list['small'] = $all_view_list['small'];
		
		if (empty($enable_view_list[$view])) 
			$view = 'large';
		
		foreach($all_view_list as $key=>$v) {
			if ($key == $view) {
				$this->assign('active_'.$key, '');
				$this->assign('active_switchview_class', $v['className']);
			} else {
				$this->assign('active_'.$key, 'hidden');
			}
		}
			
		
		$position = $m2->position($cid, $options);
	
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		$res = $this->selectForListview($params, $cataloginfo, $options);
		
		$paginationinfo = $res['paginationinfo'];
		$nr_page = $res['nr_page'];
		
		$url = '';
		$stc = 0;
		
		$scf = get_site_config();
		if (!$keyword && $scf['htmlpub'] == 1) {
			$stc = 1;
			$url = $options['_webroot'].'/'.$scf['shtml_uri_base'].'/'.$cid.'/'.$scf['index_shtml_name'];
		} else {
			$url = $options['_webroot'].'/'.$scf['index_script_name']."?c=list&id=$cid&keyword=$keyword&";
		}
				
		$pages = $this->mk_page_html($page, $nr_page, $url, $stc, $paginationinfo);
		
		$this->assign('udb', $res['rows']);
		$this->assign('pages', $pages);
		$this->assign('position', $position);

		$this->setColumn($res['rows']);

		//rlog($paginationinfo);
		
		$this->assign('view', $view);
		$this->assign('enable_view_list', $enable_view_list);			
		
		
		$formid = md5('list2-'.$cid);

		$this->assign('paginationinfo', $paginationinfo);
		$this->assign('cataloginfo', $cataloginfo);
		$this->assign('formid', $formid);
		$this->assign('params', $params);
	
		return $res['rows'];
			
	}	
}