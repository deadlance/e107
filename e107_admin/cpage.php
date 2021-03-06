<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 * Admin-related functions for custom page and menu creation
*/
define('e_MINIMAL',true);
require_once('../class2.php');

if (!getperms("5|J")) { header('location:'.e_ADMIN.'admin.php'); exit; }

e107::css('inline',"

.e-wysiwyg { height: 400px }
");

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_page.php');

$e_sub_cat = 'custom';


class page_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'page'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'page_chapters_ui',
			'path' 			=> null,
			'ui' 			=> 'page_chapters_form_ui',
			'uipath' 		=> null
		),
		'menu'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'dialog'		=> array(
			'controller' 	=> 'menu_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'menu_admin_form_ui',
			'uipath' 		=> null
		)
			
	);	
	
	protected $adminMenu = array(
		'page/list'		=> array('caption'=> CUSLAN_48, 'perm' => '5'),
		'menu/list'		=> array('caption'=> CUSLAN_49, 'perm' => 'J', 'tab' => 2),	
		'page/create' 	=> array('caption'=> CUSLAN_12, 'perm' => '5'),
		'other' 		=> array('divider'=> true),
		'cat/list' 		=> array('caption'=> "List Books/Chapters", 'perm' => '5'), // Create Category. 
		'cat/create' 	=> array('caption'=> "Add Book/Chapter", 'perm' => '5'), // Category List
		'other2' 		=> array('divider'=> true),
	
	//	'menu/create' 	=> array('caption'=> CUSLAN_31, 'perm' => 'J', 'tab' => 2),
		'page/prefs'	=> array('caption'=> LAN_OPTIONS, 'perm' => '0')		
	);
	

	protected $adminMenuAliases = array(
		'page/edit'		=> 'page/list',
		'menu/edit'		=> 'menu/create'				
	);	
	
	protected $menuTitle = ADLAN_42;
}

class page_admin_form_ui extends e_admin_form_ui
{
	
	function page_title($curVal,$mode,$parm)
	{
	
		if($mode == 'read') 
		{
			$id = $this->getController()->getListModel()->get('page_id');
			return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";
		}
			
		if($mode == 'write')
		{
			return null;
		}
			
		if($mode == 'filter')
		{
			return null;
		}
		if($mode == 'batch')
		{
			return null;
		}		
	}
	

		// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		
		if($attributes['mode'] == 'read')
		{
			parse_str(str_replace('&amp;', '&', e_QUERY), $query); //FIXME - FIX THIS
			$query['action'] = 'edit';
			$query['id'] = $id;
			$query = http_build_query($query);	
				
			$text = "<a href='".e_SELF."?{$query}' class='btn btn-default' title='".LAN_EDIT."' data-toggle='tooltip' data-placement='left'>
						".ADMIN_EDIT_ICON."</a>";
			
			$text .= $this->submit_image('menu_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'.$delcls));
			
			return $text;
		}
	}

}

//FIXME - needs a layout similar to the admin sitelinks page. ie. showing chapters as we would 'sublinks'. 
// BOOKS & CHAPTERS 
class page_chapters_ui extends e_admin_ui
{
		protected $pluginTitle	= 'Page';
		protected $pluginName	= 'core';
		protected $table 		= "page_chapters";
		protected $pid			= "chapter_id";
		protected $perPage 		= 0; //no limit
		protected $batchDelete 	= false;
		protected $batchCopy	= true;	
        protected $batchLink   	= true;
		protected $listOrder 	= ' COALESCE(NULLIF(chapter_parent,0), chapter_id), chapter_parent > 0, chapter_order '; //FIXME works with parent/child but doesn't respect parent order. 
		protected $url         	= array('route'=>'page/chapter/index', 'vars' => array('id' => 'chapter_id', 'name' => 'chapter_sef'), 'name' => 'chapter_name', 'description' => ''); // 'link' only needed if profile not provided. 
	
	//	protected $sortField	= 'chapter_order';
	//	protected $orderStep 	= 10;
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',						'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'chapter_id'				=> array('title'=> LAN_ID,					'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'chapter_icon' 				=> array('title'=> LAN_ICON,				'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'writeParms'=> 'glyphs=1', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       		
         	'chapter_parent' 			=> array('title'=> "Book",					'type' => 'dropdown',		'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'filter'=>true),                   	
         	'chapter_name' 				=> array('title'=> "Book or Chapter Title",	'type' => 'method',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'writeParms'=>'size=xxlarge'),       
         	'chapter_template' 			=> array('title'=> LAN_TEMPLATE, 			'type' => 'dropdown', 		'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
        
         	'chapter_meta_description'	=> array('title'=> LAN_DESCRIPTION,			'type' => 'textarea',		'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>FALSE),
			'chapter_meta_keywords' 	=> array('title'=> "Meta Keywords",			'type' => 'tags',			'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
			'chapter_sef' 				=> array('title'=> "SEF Url String",		'type' => 'text',			'width' => 'auto', 'readonly'=>FALSE, 'inline'=>true, 'writeParms'=>'size=xxlarge&inline-empty=1'), // Display name
			'chapter_manager' 			=> array('title'=> "Can be edited by",		'type' => 'userclass',		'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'chapter_order' 			=> array('title'=> LAN_ORDER,				'type' => 'text',			'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),										
			'chapter_visibility' 		=> array('title'=> LAN_VISIBILITY,			'type' => 'userclass',		'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
		
			'options' 					=> array('title'=> LAN_OPTIONS,				'type' => 'method',			'width' => '10%', 'forced'=>TRUE, 'thclass' => 'left last', 'class' => 'left', 'readParms'=>'sort=1')
		
		);

		protected $fieldpref = array('checkboxes', 'chapter_icon', 'chapter_id', 'chapter_name', 'chapter_description','chapter_template', 'chapter_manager', 'chapter_order', 'options');

		protected $books = array();
	
		function init()
		{
			$sql = e107::getDb();
			$sql->gen("SELECT chapter_id,chapter_name FROM #page_chapters WHERE chapter_parent =0");
			$this->books[0] = "(New Book)";
			
			while($row = $sql->fetch())
			{
				$bk = $row['chapter_id'];
				$this->books[$bk] = $row['chapter_name'];
			}
			
			asort($this->books);
			
			$this->fields['chapter_parent']['writeParms'] = $this->books;	
			
			
			$tmp = e107::getLayouts('', 'chapter', 'front', '', true, false);
			$tmpl = array();
			foreach($tmp as $key=>$val)
			{
				if(substr($key,0,3) != 'nav')
				{
					$tmpl[$key] = $val;	
				}	
			}
			
			$this->fields['chapter_template']['writeParms'] = $tmpl; // e107::getLayouts('', 'chapter', 'front', '', true, false); // e107::getLayouts('', 'page', 'books', 'front', true, false); 
			
		}
		
		
		public function beforeCreate($new_data)
		{
			if(empty($new_data['chapter_sef']))
			{
				$new_data['chapter_sef'] = eHelper::title2sef($new_data['chapter_name']);
			}
			else 
			{
				$new_data['chapter_sef'] = eHelper::secureSef($new_data['chapter_sef']);
			}
			
			$sef = e107::getParser()->toDB($new_data['chapter_sef']);
			
			if(e107::getDb()->count('page_chapters', '(*)', "chapter_sef='{$sef}'"))
			{
				e107::getMessage()->addError('Please choose unique SEF URL string for this entry.');
				return false;
			}
			
			return $new_data;	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{	
			// return $this->beforeCreate($new_data);	
		}

}


class page_chapters_form_ui extends e_admin_form_ui
{
	
	function chapter_name($curVal,$mode,$parm)
	{
	
		$frm = e107::getForm();
	
		if($mode == 'read') 
		{
			$parent 	= $this->getController()->getListModel()->get('chapter_parent');
			$id			= $this->getController()->getListModel()->get('chapter_id');

			$linkQ = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";	
			$level_image = $parent ? '<img src="'.e_IMAGE_ABS.'generic/branchbottom.gif" class="icon" alt="" style="margin-left: '.($level * 20).'px" />&nbsp;' : '';

			return ($parent) ?  $level_image."<a href='".$linkQ."' >".$curVal."</a>" : $curVal;
		}
			
		if($mode == 'write')
		{
			return $frm->text('chapter_name',$curVal,255,'size=xxlarge');	
		}
			
		if($mode == 'filter')
		{
			return;	
		}
		if($mode == 'batch')
		{
			return;
		}		
	}
	
	
	
	
	
	
		// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		//$id = $this->getController()->getListModel()->get('page_id');
		//	return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";
		$parent = $this->getController()->getListModel()->get('chapter_parent');
	//	$id = $this->getController()->getListModel()->get('chapter_id');
	//	$att['readParms'] = 'sort=1';
		$text = "";
		
		if($attributes['mode'] == 'read')
		{

			$text .= $this->renderValue('options',$value,$att,$id);
			
			if($parent != 0)
			{
				$link = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";	
				$text .= "<a href='".$link."' class='btn' title='View Pages in this chapter'>".E_32_CUST."</a>";
			}
			
			return $text;
		}
	}
}


// Menu Area. 
/*
class menu_admin_ui extends e_admin_ui
{
		protected $pluginTitle = ADLAN_42;
		protected $pluginName = 'core';
		protected $table = "page";
		
		protected $listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.menu_name != '' "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 			= "page_id";
		protected $listOrder 	= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 		= 10;
		protected $batchDelete 	= true;
		protected $batchCopy 	= true;	
	//	protected $sortField	= 'page_order';
		protected $orderStep 	= 10;
		
		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> 'ID',			'type'=>'text',   'tab' => 0,	'width'=>'5%', 'readParms'=>'','forced'=> TRUE),
         	'menu_name' 		=> array('title'=> "Menu Name", 	'tab' => 0,	'type' => 'text', 		'width' => 'auto','nolist'=>true),
		
		    'page_title'	   	=> array('title'=> LAN_TITLE, 		'tab' => 0,	'type' => 'text', 		'width'=>'25%', 'inline'=>true),
		//	'page_template' 	=> array('title'=> 'Template', 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),     
		// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
        
			'options' 	=> array('title'=> LAN_OPTIONS, 'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center'
		);
	
		protected $fieldpref = array("page_id","menu_name", "page_title", "page_text");	
		
		
		function init()
		{
			$this->fields['page_id']['readParms'] = array('link'=> e_SELF."?mode=dialog&action=preview&id=[id]", 'target'=> 'modal', 'iframe' => true);
			
			
			if(E107_DEBUG_LEVEL > 0 && e_AJAX_REQUEST)
			{
				echo "REQUEST = ".e_REQUEST_SELF; //XXX Why no Query String ?? FIXME
				// $this->getAction()	
			}
			
			
			
			if($this->getMode() == 'dialog')
			{
				
				$this->getRequest()->setAction('preview');
				
			//	$this->setDefaultAction('previewPage');
				
			//	echo "ACTIOn = ".$this->getAction();
				
				define('e_IFRAME', TRUE);
				
				// return;
			};
				
			
		}

		function CreateHeader()
		{
			// e107::css('inline',' body { background-color: green } ');	
		}
		
		// Create Menu in Menu Table
	
		
		
		function previewPage() //XXX FIXME Doesn't work when in Ajax mode.. why???
		{
			print_a($_GET);
			
		//	$id = $this->getListModel()->get('page_id');
			$tp = e107::getParser();			
		}
					
				
			
		
		
}

//TODO XXX FIXME // Hooks! 
	$hooks = array(
					'method'	=>'form', 
					'table'		=>'page', 
					'id'		=> $id, 
					'plugin'	=> 'page', 
					'function'	=> 'createPage'
				);
				
				
	//			$text .= $frm->renderHooks($hooks);



class menu_form_ui extends e_admin_form_ui
{

}
*/




//  MAIN Pages. 
class page_admin_ui extends e_admin_ui
{
		protected $pluginTitle  	= ADLAN_42;
		protected $pluginName   	= 'core';
		protected $eventName   		= 'page';
		protected $table        	= "page";
		
		protected $listQry      	= "SELECT SQL_CALC_FOUND_ROWS
		                                    p.*,u.user_id,u.user_name,pch.chapter_sef,pbk.chapter_sef AS book_sef
		                               FROM #page AS p
		                               LEFT JOIN #user AS u ON p.page_author = u.user_id
		                               LEFT JOIN #page_chapters AS pch ON p.page_chapter = pch.chapter_id
		                               LEFT JOIN #page_chapters AS pbk ON pch.chapter_parent = pbk.chapter_id
		                               WHERE p.page_title != '' "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 				= "page_id";
		protected $listOrder 		= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 			= 10;
		protected $batchDelete 		= true;
		protected $batchCopy 		= true;	
        protected $batchLink    	= true;
	  	protected $batchFeaturebox   = true;
		protected $sortField		= 'page_order';
		protected $orderStep 		= 10;
		//protected $url         	= array('profile'=>'page/view', 'name' => 'page_title', 'description' => '', 'link'=>'{e_BASE}page.php?id=[id]'); // 'link' only needed if profile not provided. 
		protected $url         		= array('route'=>'page/view/index', 'vars' => array('id' => 'page_id', 'name' => 'page_sef', 'other' => 'page_sef', 'chapter' => 'chapter_sef', 'book' => 'book_sef'), 'name' => 'page_title', 'description' => ''); // 'link' only needed if profile not provided.
		protected $tabs		 		= array("Page","Page Options","Menu", "Menu Options");
		protected $featurebox		= array('name'=>'page_title', 'description'=>'page_text', 'image' => 'menu_image', 'visibility' => 'page_class', 'url' => true);
		
		/*
		 * 	'fb_title' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'inline'=>true,  'width' => 'auto', 'thclass' => 'left'), 
     	'fb_text' 			=> array('title'=> FBLAN_08,			'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1','writeParms'=>'template=admin'), 
		//DEPRECATED 'fb_mode' 			=> array('title'=> FBLAN_12,			'type' => 'dropdown',		'data'=> 'int',	'width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
		//DEPRECATED 'fb_rendertype' 	=> array('title'=> FBLAN_22,			'type' => 'dropdown',		'data'=> 'int',	'width' => 'auto', 'noedit' => TRUE),	
        'fb_template' 		=> array('title'=> LAN_TEMPLATE,			'type' => 'layouts',		'data'=> 'str', 'width' => 'auto', 'writeParms' => 'plugin=featurebox', 'filter' => true, 'batch' => true),	 	// Photo
		'fb_image' 			=> array('title'=> "Image",				'type' => 'image',			'width' => 'auto', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60'),
		'fb_imageurl' 		=> array('title'=> "Image Link",		'type' => 'url',			'width' => 'auto'),
		'fb_class' 	
		 */
		
		
	//		protected $listSorting = true; 
	
		// PAGE LIST/EDIT and MENU EDIT modes. 
		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'3%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> LAN_ID,			'type' => 'text', 'tab' => 0,	'width'=>'5%', 			'forced'=> TRUE, 'readParms'=>'link=sef&target=dialog'),
            'page_title'	   	=> array('title'=> LAN_TITLE, 		'tab' => 0,	'type' => 'text', 'inline'=>true,		'width'=>'25%', 'writeParms'=>'size=block-level'),
		    'page_chapter' 		=> array('title'=> 'Book/Chapter', 	'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
       
			'page_template' 	=> array('title'=> LAN_TEMPLATE, 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),

		 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'tab' => 0,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page&template=page'), 
		
		
			// Options Tab. 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'tab' => 1,	'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1'),
            'page_class' 		=> array('title'=> LAN_VISIBILITY, 	'tab' => 1,	'type' => 'userclass', 	'data'=>'int', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> ADLAN_114,		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_password' 	=> array('title'=> LAN_PAGE_9, 		'tab' => 1, 'type' => 'text', 	'data'=>'str', 'width' => 'auto', 'writeParms'=>array('password'=>1, 'nomask'=>1, 'size' => 40, 'class' => 'tbox e-password', 'generate' => 1, 'strength' => 1, 'required'=>0)),								
			'page_sef' 			=> array('title'=> LAN_SEFURL, 		'tab' => 1,	'type' => 'text', 'inline'=>true, 'width' => 'auto', 'writeParms'=>'size=xxlarge'),		
			'page_metakeys' 	=> array('title'=> LAN_KEYWORDS, 		'tab' => 1,	'type' => 'tags', 'width' => 'auto'),								
			'page_metadscr' 	=> array('title'=> CUSLAN_11, 		'tab' => 1,	'type' => 'text', 'width' => 'auto', 'writeParms'=>'size=xxlarge'),	
		
			'page_order' 		=> array('title'=> LAN_ORDER, 		'tab' => 1,	'type' => 'number', 'width' => 'auto', 'inline'=>true),
			
			// Menu Tab  XXX 'menu_name' is 'menu_name' - not caption. 
			'menu_name' 		=> array('title'=> "Menu Name", 		'tab' => 2,	'type' => 'text', 		'width' => 'auto','nolist'=>true, "help"=>"Will be listed in the Menu-Manager under this name or may be called using {CMENU=name} in your theme. Must use ASCII characters only."),
		   	'menu_title'	   	=> array('title'=> "Menu Title", 	'nolist'=>true, 'tab' => 2,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Caption displayed on the menu item.", 'writeParms'=>'size=xxlarge'),
			'menu_text' 		=> array('title'=> "Menu Body",		'nolist'=>true, 'tab' => 2,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page' ), 
			'menu_template' 	=> array('title'=> "Menu Template", 'nolist'=>true, 'tab' => 2,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
            'menu_class' 		=> array('title'=> LAN_VISIBILITY, 	'tab' => 3,	'type' => 'userclass', 	'data'=>'int', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'menu_button_text'	=> array('title'=> "Custom Button Text", 	'nolist'=>true, 'tab' => 3,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Leave blank to use the default"),
		
			'menu_button_url'	=> array('title'=> "Custom Button URL", 	'nolist'=>true, 'tab' => 3,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Leave blank to use the corresponding page", 'writeParms'=>'size=xxlarge'),
		
			'menu_icon'			=> array('title' =>"Menu Icon/Glyph", 'nolist'=>true, 'tab' => 2,	'type' => 'icon', 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'media=page&glyphs=1', 'readonly'=>false),		  					
		
			'menu_image'		=> array('title' =>"Menu Image/Video", 	'nolist'=>true, 'tab' => 2,	'type' => 'image', 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'media=page&video=1', 'readonly'=>false),		  					
			
	
	
	   	//	'page_ip_restrict' 	=> array('title'=> LXXAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS,   'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);
	
		protected $fieldpref = array("page_id","page_title","page_chapter","page_template","page_author","page_class");

		protected $prefs = array( 
			'listPages'	   			=> array('title'=> CUSLAN_29, 						'type'=>'boolean'),
			'listBooks'	   			=> array('title'=> 'List Books/Chapters', 			'type'=>'boolean'),
			'listBooksTemplate'   	=> array('title'=> 'List Books/Chapters Template', 	'type'=>'dropdown'),
			'pageCookieExpire'		=> array('title'=> CUSLAN_30, 						'type'=>'number') //TODO Set default value to  84600
		);

		protected $books = array();
		protected $cats = array(0 => 'None');
		protected $templates = array();

		function init()
		{
			
			if(vartrue($_POST['menu_delete'])) // Delete a Menu (or rather, remove it's data )
			{
				$key = key($_POST['menu_delete']);
				
				if($key)
				{
					e107::getDb()->update('page',"menu_name = '' WHERE page_id=".intval($key)." LIMIT 1");
				}
			}

			// USED IN Menu LIST/INLINE-EDIT MODE ONLY. 
			if($this->getMode() == 'menu' && ($this->getACtion() == 'list' || $this->getACtion() == 'inline'))
			{
			
				$this->listQry = "SELECT SQL_CALC_FOUND_ROWS p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.menu_name != '' "; // without any Order or Limit.
			
				$this->listOrder 		= 'p.page_id desc';
			
				$this->batchDelete 	= false;
				$this->fields = array(
					'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'3%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
					'page_id'			=> array('title'=> 'ID',			'type'=>'text',   'tab' => 0,	'width'=>'5%', 'readParms'=>'','forced'=> TRUE),
		       
					'menu_image'		=> array('title' =>"Menu Image/Video", 	 	'type' => 'image', 		'width' => '110px',	'thclass' => 'left', 'class' => "left", 'nosort' => false, 'readParms'=>'thumb=140&thumb_urlraw=0&thumb_aw=140', 'readonly'=>false),		  					
					'menu_icon'			=> array('title'=> LAN_ICON, 	 	'type' => 'icon', 		'width' => '80px',	'thclass' => 'center', 'class' => "center", 'nosort' => false, 'readParms'=>'thumb=80&thumb_urlraw=0&thumb_aw=80', 'readonly'=>false),		  					
				
			  		'menu_title'	   	=> array('title'=> "Menu Title", 	'forced'=> TRUE, 	'type' => 'text', 		'inline'=>true,		'width'=>'20%'),
			
				
				  	'menu_name' 		=> array('title'=> "Menu Name", 	'type' => 'text', 	'inline'=>true,	'width' => '10%','nolist'=>false, "help"=>"Will be listed in the Menu-Manager under this name. Must use ASCII characters only."),
					'menu_template' 	=> array('title'=> "Menu Template",  	'type' => 'dropdown', 	'width' => '15%', 'filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
          			'menu_class' 		=> array('title'=> LAN_USERCLASS, 		'type' => 'userclass', 	'data'=>'int', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
		
				// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
					'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
		     	
			   		'page_chapter' 		=> array('title'=> 'Book/Chapter', 	'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
      
					'menu_text' 		=> array('title'=> "Menu Body",		 	'type' => 'bbarea',		'data'=>'str',	'width' => 'auto', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page'), 
				
					'options' 	=> array('title'=> LAN_OPTIONS, 'type' => 'method',	'noselector' => true, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'delete=0&deleteClass='.e_UC_NOBODY)
				);
	
				$this->fieldpref = array("page_id","menu_name", "menu_title", 'menu_image', 'menu_template', 'menu_icon', 'page_chapter', 'menu_class');

                ### Parse aliases again or all filters shall fail due to the menu hack!
                $this->_alias_parsed = false;
                $this->parseAliases();
			}
				
							
			
			$this->templates = e107::getLayouts('', 'page', 'front', '', true, false); 
			unset($this->templates['panel'], $this->templates['nav']);
			
			$this->fields['page_template']['writeParms'] = $this->templates;			
			$this->fields['menu_template']['writeParms'] = e107::getLayouts('', 'menu', 'front', '', true, false); 
			$this->fields['menu_name']['writeParms'] 	= array('pattern'=>'^[\w-]*'); 
			
			
			$tmp = e107::getLayouts('', 'chapter', 'front', '', true, false);
			$tmpl = array();
			foreach($tmp as $key=>$val)
			{
				if(substr($key,0,3) != 'nav')
				{
					$tmpl[$key] = $val;	
				}	
			}
			
			
			$this->prefs['listBooksTemplate']['writeParms'] = $tmpl; 
			
			$sql = e107::getDb();
			
			
			$sql->gen("SELECT chapter_id,chapter_name,chapter_parent FROM #page_chapters ORDER BY chapter_parent asc, chapter_order");
			while($row = $sql->fetch())
			{
				$cat = $row['chapter_id'];

				if($row['chapter_parent'] == 0)
				{
					$this->books[$cat] = $row['chapter_name'];	
				}
				else
				{
					$book = $row['chapter_parent'];
					$this->cats[$cat] = $this->books[$book] . " : ".$row['chapter_name'];	
				}			
			}
		//	asort($this->cats);			
			
			$this->fields['page_chapter']['writeParms'] = $this->cats;

		}


        /**
         * Overrid
         */
        public function ListObserver()
        {
            parent::ListObserver();

            // fix current url config limitation
            $tree = $this->getTreeModel();

            /** @var e_admin_model $model */
            foreach ($tree->getTree() as $id => $model)
            {
                // No chapter, override route
                if(!$model->get('page_chapter'))
                {
                    $urlData = $this->url;
                    $urlData['route'] = 'page/view/other';
                    $model->setUrl($urlData);
                }
            }
        }

		function afterCreate($newdata,$olddata, $id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
			$menu_name = $tp->toDB($newdata['menu_name']); // not to be confused with menu-caption.
			$menu_path = intval($id);
				
			if (!$sql->select('menus', 'menu_name', "`menu_path` = ".$menu_path." LIMIT 1")) 	
			{		
				$insert = array('menu_name' => $menu_name, 'menu_path' => $menu_path);
			
				if($sql->insert('menus', $insert) !== false)
				{
					$mes->addDebug("Menu Created");
					return true;
				}
			}	
			
			return $newdata;
			
		}
		
		function beforeCreate($newdata,$olddata)
		{
			$newdata['menu_name'] = preg_replace('/[^\w-*]/','-',$newdata['menu_name']);
			
			if(empty($newdata['page_sef']))
			{
				if(!empty($newdata['page_title']))
				{
					$newdata['page_sef'] = eHelper::title2sef($newdata['page_title']);
				}
				elseif(!empty($newdata['menu_name']))
				{
					$newdata['page_sef'] = eHelper::title2sef($newdata['menu_name']);
				}
		
			}
			else 
			{
				$newdata['page_sef'] = eHelper::secureSef($newdata['page_sef']);
			}
			
			$sef = e107::getParser()->toDB($newdata['page_sef']);
			
			if(e107::getDb()->count('page', '(*)', "page_sef='{$sef}'"))
			{
				e107::getMessage()->addError('Please choose unique SEF URL string for this entry.');
				return false;
			}


			return $newdata;	
		}
		
		function beforeUpdate($newdata,$olddata)
		{
			$newdata['menu_name'] = preg_replace('/[^\w-*]/','',$newdata['menu_name']);

			return $newdata;	
		}		
		
		// Update Menu in Menu Table
		function afterUpdate($newdata,$olddata,$id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
					
			$menu_name = $tp->toDB($newdata['menu_name']); // not to be confused with menu-caption.
				
			if ($sql->select('menus', 'menu_name', "`menu_path` = ".$id." LIMIT 1")) 	
			{		
				if($sql->update('menus', "menu_name='{$menu_name}' WHERE menu_path=".$id." ") !== false)
				{
					$mes->addDebug("Menu Updated");
					return true;
				}
			}
			else // missing menu record so create it.  
			{
				$mes->addDebug("Missing Menu-id detected: ".$id);
				return $this->afterCreate($newdata,$olddata,$id);	
				
			}				
		}






}


new page_admin();
require_once('auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');






?>