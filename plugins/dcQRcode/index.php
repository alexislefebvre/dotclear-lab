<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

$qrc = new dcQRcode($core,QRC_CACHE_PATH);
$types = $qrc->getAccept('type');
$s = $core->blog->settings->dcQRcode;
$tab = empty($_REQUEST['tab']) ? '' : $_REQUEST['tab'];
$part = !empty($_REQUEST['type']) && isset($types[$_REQUEST['type']]) ? $_REQUEST['type'] : key($types);
$action = empty($_POST['action']) ? '' : $_POST['action'];
$combo_sizes = array();
foreach($qrc->getAccept('size') as $px)
{
	$combo_sizes[sprintf(__('%sx%s pixels'),$px,$px)] = $px;
}

# Admin form to create custom QR code
if ($tab == 'create' && $s->qrc_active) {
	echo
	'<html><head><title>'.__('QR code').'</title>'.
	dcPage::jsPageTabs($part).
	'</head><body>'.
	'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('QR code').
	' &rsaquo; <span class="page-title">'.__('New image').'</span>'.
	' - <a href="'.$p_url.'" class="button">'.__('Records').'</a>'.
	' - <a href="'.$p_url.'&amp;tab=settings" class="button">'.__('Settings').'</a>'.
	'</h2>';

	foreach($types as $type => $class)
	{
		$size = empty($_POST['QRC_'.$type.'_size']) ? $qrc->getParam('size') : $_POST['QRC_'.$type.'_size'];
		
		$qrc->setSize($size);
		$qrc->setType($type);
		
		echo 
		'<div id="'.$type.'" class="multi-part" title="'.$qrc->getTitle().'">'.
		'<div class="clear two-cols"><div class="col">'.
		'<h3>'.$qrc->getTitle().'</h3>'.
		'<form method="post" action="plugin.php">'.

		'<p><label for="QRC_'.$type.'_size">'.
		__('Image size').'<br />'.
		form::combo('QRC_'.$type.'_size',$combo_sizes,$size).
		'</label></p>';
		
		$qrc->getForm();
		
		echo 
		'<p>'.
		'<input type="submit" name="create_url" value="'.__('Create').'" />'.
		form::hidden(array('p'),'dcQRcode').
		form::hidden(array('tab'),'create').
		form::hidden(array('type'),html::escapeHTML($type)).
		form::hidden(array('action'),'createqrcode').
		$core->formNonce().
		'</p>'.
		'</form>'.
		'</div><div class="col">';
		
		if ($action == 'createqrcode' && !empty($_POST['type']) && $_POST['type'] == $type) {
			try {
				$qrc->saveForm();
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		echo 
		'</div></div></div>';
	}
}
# Admin form for default plugin settings
elseif ($tab == 'settings') {
	if ($action == 'savesettings') {
		try {
			$s->put('qrc_active',isset($_POST['qrc_active']));
			$s->put('qrc_use_mebkm',isset($_POST['qrc_use_mebkm']));
			$s->put('qrc_img_size',(integer) $_POST['qrc_img_size']);
			$s->put('qrc_cache_use',isset($_POST['qrc_cache_use']));
			$s->put('qrc_custom_css',$_POST['qrc_custom_css']);
			$s->put('qrc_bhv_entrytplhome',isset($_POST['qrc_bhv_entrytplhome']));
			$s->put('qrc_bhv_entrytplpost',isset($_POST['qrc_bhv_entrytplpost']));
			$s->put('qrc_bhv_entrytplcategory',isset($_POST['qrc_bhv_entrytplcategory']));
			$s->put('qrc_bhv_entrytpltag',isset($_POST['qrc_bhv_entrytpltag']));
			$s->put('qrc_bhv_entrytplarchive',isset($_POST['qrc_bhv_entrytplarchive']));
			$s->put('qrc_bhv_entryplace',$_POST['qrc_bhv_entryplace']);
			
			if ($core->auth->isSuperAdmin() 
			 && isset($_POST['qrc_cache_use']) 
			 && !empty($_POST['qrc_cache_path'])
			) {
				if (!is_dir($_POST['qrc_cache_path'])) {
					throw new Exception('Unable to find cache path');
				}
				$s->put('qrc_cache_path',$_POST['qrc_cache_path']);
			}
			
			//$qrc->cleanCache();
			
			$core->blog->triggerBlog();
			http::redirect($p_url.'&tab=settings');
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	
	echo
	'<html><head><title>'.__('QR code').'</title>'.
	'</head><body>'.
	'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('QR code').
	' &rsaquo; <span class="page-title">'.__('Settings').'</span>'.
	' - <a href="'.$p_url.'&amp;tab=" class="button">'.__('Records').'</a>';
	
	if ($s->qrc_active) {
		echo 
		' - <a href="'.$p_url.'&amp;tab=create" class="button">'.__('New image').'</a>';
	}
	
	echo 
	'</h2>'.
	'<form method="post" action="plugin.php">'.
	
    '<h3>'.__('Settings').'</h3>'.
	
    '<p><label for="qrc_active" class="classic">'.
	form::checkbox('qrc_active','1',$s->qrc_active).
	__('Enable plugin').
	'</label></p>'.
	
    '<p><label for="qrc_use_mebkm" class="classic">'.
	form::checkbox('qrc_use_mebkm','1',$s->qrc_use_mebkm).
	__('Use MEBKM anchor for URL QR codes').
	'</label></p>'.
	'<p class="form-note">'.
	__('MEBKM anchors made links as bookmarks with titles.').
	'</p>'.

    '<p><label for="qrc_img_size">'.
	__('Image size:').
	form::combo('qrc_img_size',$combo_sizes,$s->qrc_img_size).
	'</label></p>';

	if ($core->auth->isSuperAdmin()) {
		echo 
		'<p><label for="qrc_cache_use" class="classic">'.
		form::checkbox('qrc_cache_use','1',$s->qrc_cache_use).
		__('Use image cache').
		'</label></p>'.

		'<p><label for="qrc_cache_path">'.
		__('Custom path for cache:').
		form::field('qrc_cache_path',50,255,$s->qrc_cache_path).
		'</label></p>'.
		'<p class="form-note">'.
		sprintf(__('Default is %s'),path::real($core->blog->public_path).'/qrc').
		'<br />'.
		sprintf(__('Currently %s'),QRC_CACHE_PATH).
		'</p>';
	}
	
	echo 
	'<h3>'.__('Theme').'</h3>'.

	'<p class="area" id="style-area"><label for="_style">'.__('CSS:').'</label>'.
	form::textarea('qrc_custom_css',50,3,html::escapeHTML($s->qrc_custom_css),'',2).
	'</p>'.
	'<p class="form-note">'.__('You can add here special cascading style sheet. QRcode images have HTML tag "div" of class "qrcode" and "qrcode-widget" for widgets.').'</p>'.
	
	'<p><label for="qrc_bhv_entrytplhome" class="classic">'.
	form::checkbox('qrc_bhv_entrytplhome','1',$s->qrc_bhv_entrytplhome).
	__('Include on entries on home page').
	'</label></p>'.

	'<p><label for="qrc_bhv_entrytplpost" class="classic">'.
	form::checkbox('qrc_bhv_entrytplpost','1',$s->qrc_bhv_entrytplpost).
	__('Include on entries on post page').
	'</label></p>'.

	'<p><label for="qrc_bhv_entrytplcategory" class="classic">'.
	form::checkbox('qrc_bhv_entrytplcategory','1',$s->qrc_bhv_entrytplcategory).
	__('Include on entries on category page').
	'</label></p>'.

	'<p><label for="qrc_bhv_entrytpltag" class="classic">'.
	form::checkbox('qrc_bhv_entrytpltag','1',$s->qrc_bhv_entrytpltag).
	__('Include on entries on tag page').
	'</label></p>'.

	'<p><label for="qrc_bhv_entrytplarchive" class="classic">'.
	form::checkbox('qrc_bhv_entrytplarchive','1',$s->qrc_bhv_entrytplarchive).
	__('Include on entries on monthly archive page').
	'</label></p>'.

	'<p><label for="qrc_bhv_entryplace">'.
	__('Place where to insert image:').
	form::combo('qrc_bhv_entryplace',array(__('before content')=>'before',__('after content')=>'after'),$s->qrc_bhv_entryplace).
	'</label></p>'.

	'<p class="form-note">'.
	__('In order to use this, blog theme must have behaviors "publicEntryBeforeContent" and  "publicEntryAfterContent".').'<br />'.
	__('A template value is also available, you can add {{tpl:QRcode}} anywhere inside &lt;tpl:Entries&gt; loop in templates.').
	'</p>'.
	
	'<p>'.
	'<input type="submit" name="save_settings" value="'.__('Save').'" />'.
	form::hidden(array('p'),'dcQRcode').
	form::hidden(array('tab'),'settings').
	form::hidden(array('action'),'savesettings').
	$core->formNonce().
	'</p>'.
	'</form>';
}
else {
	if ($action == 'deleteqrc' && !empty($_POST['entries'])) {
		try {
			foreach($_POST['entries'] as $id)
			{
				$qrc->delQRcode($id);
			}
			if (!empty($redir)) {
				http::redirect($redir);
			}
			else {
				http::redirect($p_url);
			}
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	
	# Filters
	$show_filters = false;
	$sorttype = !empty($_GET['sorttype']) ? $_GET['sorttype'] : '';
	$sortsize = !empty($_GET['sortsize']) ? $_GET['sortsize'] : '';
	$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'qrcode_id';
	$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';

	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
	$nb_per_page =  10;
	if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
		if ($nb_per_page != $_GET['nb']) $show_filters = true;
		$nb_per_page = (integer) $_GET['nb'];
	}
	
	# Combos
	$sortby_combo = array(
		__('ID') => 'qrcode_id',
		__('Size') => 'qrcode_size',
		__('Type') => 'qrcode_type'
	);
	
	$order_combo = array(
		__('Descending') => 'desc',
		__('Ascending') => 'asc'
	);
	
	$types_combo = array();
	foreach($types as $type => $_)
	{
		$qrc->setType($type);
		$types_combo[$qrc->getTitle()] = $type;
	}
	$types_combo = array_merge(array('-'=>''),$types_combo);
	
	$sizes_combo = array();
	foreach($qrc->getAccept('size') as $size)
	{
		$sizes_combo[sprintf(__('%sx%s pixels'),$size,$size)] = $size;
	}
	$sizes_combo = array_merge(array('-'=>''),$sizes_combo);
	
	# Params for list
	$params = array();
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

	if ($sortby != '' && in_array($sortby,$sortby_combo))
	{
		if ($sorttype != '' && in_array($sorttype,$types_combo))
			$params['qrcode_type'] = $sorttype;
		
		if ($sortsize != '' && in_array($sortsize,$sizes_combo))
			$params['qrcode_size'] = $sortsize;

		if ($order != '' && in_array($order,$order_combo))
			$params['order'] = $sortby.' '.$order;

		if ($sortby != 'qrcode_id' || $sortsize != '' || $order != 'desc' || $sorttype != '')
			$show_filters = true;
	}
	
	$redir = $p_url.
	'&amp;tab='.
	'&amp;sorttype='.$sorttype.
	'&amp;sortsize='.$sortsize.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';
	
	try {
		$list_all = $qrc->getQRcodes($params);
		$list_counter = $qrc->getQRcodes($params,true)->f(0);
		$list_current = new dcQRcodeList($core,$list_all,$list_counter,$pager_base_url);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	
	echo 
	'<html><head><title>'.__('QR code').'</title>'.
	"\n<script type=\"text/javascript\"> \n".
	"$(function(){ $('.checkboxes-helpers').each(function(){dotclear.checkboxesHelpers(this);}); }); \n".
	"</script>\n";
	
	if (!$show_filters) {
		echo 
		dcPage::jsLoad('js/filter-controls.js');
	}
	echo 
	'</head><body>'.
	'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('QR code').
	' &rsaquo; <span class="page-title">'.__('Records').'</span>';
	
	if ($s->qrc_active) {
		echo 
		' - <a href="'.$p_url.'&amp;tab=create" class="button">'.__('New image').'</a>';
	}
	
	echo 
	' - <a href="'.$p_url.'&amp;tab=settings" class="button">'.__('Settings').'</a>'.
	'</h2>';
	
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}

	echo '
	<form action="'.$p_url.'" method="get" id="filters-form">
	<fieldset><legend>'.__('Filters').'</legend>
	<div class="three-cols">
	<div class="col">
	<label>'.__('Type:').form::combo('sorttype',$types_combo,$sorttype).'
	</label> 
	<label>'.__('Size:').form::combo('sortsize',$sizes_combo,$sortsize).'
	</label> 
	</div>
	<div class="col">
	<label>'.__('Order by:').form::combo('sortby',$sortby_combo,$sortby).'
	</label> 
	<label>'.__('Sort:').form::combo('order',$order_combo,$order).'
	</label>
	</div>
	<div class="col">
	<p>
	<label class="classic">'.form::field('nb',3,3,$nb_per_page).' '.__('Image per page').'
	</label> 
	<input type="submit" value="'.__('filter').'" />'.
	form::hidden(array('p'),'dcQRcode').
	form::hidden(array('tab'),'').'
	</p>
	</div>
	</div>
	<br class="clear" />
	</fieldset>
	</form>';

	$list_current->display($page,$nb_per_page,
	
	'<form action="'.$p_url.'" method="post" id="form-actions">
	%s
	<div class="two-cols">
	<p class="col checkboxes-helpers"></p>
	<p class="col right">
	<input type="submit" value="'.__('Delete selected QR codes').'" />'.
	form::hidden(array('action'),'deleteqrc').
	form::hidden(array('p'),'dcQRcode').
	form::hidden(array('redir'),sprintf($redir,$page)).
	$core->formNonce().'
	</p>
	</div>
	</form>'
	);
}

dcPage::helpBlock('dcQRcode');

echo '
<hr class="clear" />
<p class="right">
dcQRcode - '.$core->plugins->moduleInfo('dcQRcode','version').'&nbsp;
<img alt="dcQRcode" src="index.php?pf=dcQRcode/icon.png" />
</p>
</body></html>';
?>