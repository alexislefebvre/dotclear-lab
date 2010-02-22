<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Check user perms
dcPage::check('admin,eventdata');

# Main class
$O = new dcEventdata($core);

# General
$msg = isset($_REQUEST['done']) ? __('Configuration saved') : '';
$img_green = '<img alt="%s" src="index.php?pf=eventdata/inc/img/green.png" />';
$img_red = '<img alt="%s" src="index.php?pf=eventdata/inc/img/red.png" />';
$img_orange = '<img alt="%s" src="index.php?pf=eventdata/inc/img/orange.png" />';
$img_scheduled = '<img alt="%s" src="index.php?pf=eventdata/inc/img/scheduled.png" />';

# Menu
$tab = array(
	'pst' => __('Entries'),
	'cat' => __('Categories'),
	'tpl' => __('Templates'),
	'adm' => __('Administration')
);

# Entries
$edit_eventdata = $delete_eventdata = $show_filters = false;
$user_id = !empty($_GET['user_id']) ? $_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ? $_GET['cat_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$selected = isset($_GET['selected']) ? $_GET['selected'] : '';
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$period = !empty($_GET['period']) ? $_GET['period'] : '';

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

# Categories
$cats_reordered = @unserialize($core->blog->settings->eventdata_tpl_cats);
if (!is_array($cats_reordered)) $cats_reordered = array();
$cats_unlisted = @unserialize($core->blog->settings->eventdata_no_cats);
if (!is_array($cats_unlisted)) $cats_unlisted = array();

# Templates
$default_tpl = $default_thm = '';
$default_xst = false;

# Uninstall
$understand = isset($_POST['s']['understand']) ? $_POST['s']['understand'] : 0;
$delete_table = isset($_POST['s']['delete_table']) ? $_POST['s']['delete_table'] : 0;
$delete_templates = isset($_POST['s']['delete_templates']) ? $_POST['s']['delete_templates'] : 0;
$delete_settings = isset($_POST['s']['delete_settings']) ? $_POST['s']['delete_settings'] : 0;

# Actions combo
$combo_action = array();
if ($core->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
	$combo_action[__('remove events')] = 'eventdata_remove';
}
if ($core->auth->check('publish,contentadmin',$core->blog->id)) {
	$combo_action[__('add event')] = 'eventdata_add';
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('schedule')] = 'schedule';
	$combo_action[__('mark as pending')] = 'pending';
	$combo_action[__('mark as selected')] = 'selected';
	$combo_action[__('mark as unselected')] = 'unselected';
}
$combo_action[__('change category')] = 'category';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('change author')] = 'author';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
	$combo_action[__('delete')] = 'delete';
}

# Categories combo
$categories_combo = array('-'=>'');
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post')); //$categories also used by tab['cat']
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
while ($categories->fetch()) {
	$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
		html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

# Categories actions combo
$categories_actions_combo = array(
	__('Mark as reordered') => 'reorder_cats',
	__('Mark as not reordered') => 'unreorder_cats',
	__('Mark as unlisted') => 'unlist_cats',
	__('Mark as listed') => 'list_cats'
);

# Status combo
$status_combo = array('-' => '');
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Selected combo
$selected_combo = array(
'-' => '',
__('selected') => '1',
__('not selected') => '0'
);

# Sortby combo
$sortby_combo = array(
__('Date') => 'post_dt',
__('Event start') => 'eventdata_start',
__('Event end') => 'eventdata_end',
__('Event location') => 'eventdata_location',
__('Title') => 'post_title',
__('Category') => 'cat_title',
__('Author') => 'user_id',
__('Status') => 'post_status',
__('Selected') => 'post_selected'
);

# Order combo
$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

# Period combo
$period_combo = array(
'-' => '',
__('Not started') => 'notstarted',
__('Started') => 'started',
__('Finished') => 'finished',
__('Not finished') => 'notfinished',
__('Ongoing') => 'ongoing',
__('Outgoing') => 'outgoing'
);

# Templates Combo
foreach(eventdata::getThemes() AS $k => $v) {
	if ($v['selected']) {
		$combo_templates[__('Current blog theme').' '.$v['name']] = '';
		$default_thm = $k;
		$default_adt = $v['template_exists'];
		$default_xst = !empty($v['theme_file']);
	} elseif ($v['template_file']) {
		$combo_templates[__('Plugin').' '.$v['name'].' ('.$k.')'] = $k;
	}
}

/** "Static" params **/
$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
$params['eventdata_type'] = 'eventdata';
$params['post_type'] = '';

/** Editing eventdata **/
if (isset($_GET['eventdata'])) {

	$delete_eventdata = isset($_GET['a']) && $_GET['a'] == 'del' ? true : false;
	$get_eventdata = dcEventdata::unserializeURL($_GET['eventdata']);
	$params['post_id'] = $get_eventdata['post'];
	$params['eventdata_start'] = $get_eventdata['start'];
	$params['eventdata_end'] = $get_eventdata['end'];
	$params['eventdata_location'] = $get_eventdata['location'];
	$edit_eventdata = true;
	$_REQUEST['t'] = 'pst';
	unset($get_eventdata);
}

# Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# Selected filter
if ($selected !== '' && in_array($selected,$selected_combo)) {
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}	
	if ($sortby != 'post_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Period filter
if ($period !== '' && in_array($period,$period_combo)) {
	$params['period'] = $period;
	$show_filters = true;
}

# Default menu
$request_tab = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';
if (!$core->blog->settings->eventdata_active && empty($request_tab)) $request_tab = 'adm';
if ($core->blog->settings->eventdata_active && empty($request_tab)) $request_tab = 'pst';
if (!array_key_exists($request_tab,$tab)) $request_tab = 'adm';


# Save admin options
if (!empty($_POST['save_adm'])) {
	try {
		$core->blog->settings->setNamespace('eventdata');
		$core->blog->settings->put('eventdata_active',
			isset($_POST['eventdata_active']),
			'boolean','eventdata plugin enabled',true,false);
		$core->blog->settings->put('eventdata_blog_menu',
			isset($_POST['eventdata_blog_menu'])
			,'boolean','eventdata icon on blog menu',true,false);
		$core->blog->settings->put('eventdata_public_active',
			isset($_POST['eventdata_public_active']),
			'boolean','eventdata public page enabled',true,false);
		$core->blog->settings->setNamespace('system');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=adm&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Save tpl options
if (!empty($_POST['save_tpl'])) {
	try {
		$core->blog->settings->setNamespace('eventdata');
		$core->blog->settings->put('eventdata_tpl_title',$_POST['eventdata_tpl_title'],'string','Public page title',true,false);
		$core->blog->settings->put('eventdata_tpl_desc',$_POST['eventdata_tpl_desc'],'string','Public page description',true,false);
		$core->blog->settings->put('eventdata_tpl_theme',$_POST['eventdata_tpl_theme'],'string','Public page template',true,false);
		$core->blog->settings->put('eventdata_tpl_dis_bhv',$_POST['eventdata_tpl_dis_bhv'],'boolean','Disable public entry behavior',true,false);
		$core->blog->settings->setNamespace('system');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=tpl&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
$default_tpl = in_array($core->blog->settings->eventdata_tpl_theme,$combo_templates) ? 
	$core->blog->settings->eventdata_tpl_theme : '';

# Save redirected categories list
if (!empty($_POST['save_cat'])) {
	try {

		if ($_POST['action'] == 'reorder_cats') 
			$cats_reordered = array_merge($cats_reordered,$_POST['entries']);
		if ($_POST['action'] == 'unreorder_cats') 
			$cats_reordered = array_diff($cats_reordered,$_POST['entries']);

		if ($_POST['action'] == 'unlist_cats') 
			$cats_unlisted = array_merge($cats_unlisted,$_POST['entries']);
		if ($_POST['action'] == 'list_cats') 
			$cats_unlisted = array_diff($cats_unlisted,$_POST['entries']);

		$core->blog->settings->setNamespace('eventdata');
		$core->blog->settings->put('eventdata_tpl_cats',
			serialize(array_unique($cats_reordered)),
			'string','Redirected categories',true,false);
		$core->blog->settings->put('eventdata_no_cats',
			serialize(array_unique($cats_unlisted)),
			'string','Unlisted categories',true,false);
		$core->blog->settings->setNamespace('system');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=cat&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Delete an event
if (!empty($_POST['save']['pst']) && $_POST['action'] == 'eventdata_delete') {

	$redir = isset($_POST['redir']) ? $_POST['redir'] : $p_url.'&t=pst';

	if ($_POST['save']['pst'] == __('no'))
		http::redirect($redir);

	try {
		$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
		$old_start = date('Y-m-d H:i:00',strtotime($_POST['old_eventdata_start']));
		$old_end = date('Y-m-d H:i:00',strtotime($_POST['old_eventdata_end']));
		$old_location = isset($_POST['old_eventdata_location']) ? $_POST['old_eventdata_location'] : '';

		$O->delEventdata('eventdata',$post_id,$old_start,$old_end,$old_location);

		$core->blog->triggerBlog();
		http::redirect($redir.'&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Edit an event
if (!empty($_POST['save']['pst']) && $_POST['action'] == 'eventdata_edit') {
	try {
		if (strtotime($_POST['eventdata_start']) > strtotime($_POST['eventdata_end'])) {
			throw new Exception('Start date of event must be smaller than end date of event');
		}

		$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;

		$old_start = date('Y-m-d H:i:00',strtotime($_POST['old_eventdata_start']));
		$old_end = date('Y-m-d H:i:00',strtotime($_POST['old_eventdata_end']));
		$old_location = isset($_POST['old_eventdata_location']) ? $_POST['old_eventdata_location'] : '';

		$start = date('Y-m-d H:i:00',strtotime($_POST['eventdata_start']));
		$end = date('Y-m-d H:i:00',strtotime($_POST['eventdata_end']));
		$location = isset($_POST['eventdata_location']) ? $_POST['eventdata_location'] : '';

		$O->updEventdata('eventdata',$post_id,$old_start,$old_end,$old_location,$start,$end,$location);

		$core->blog->triggerBlog();
		$redir = isset($_POST['redir']) ? $_POST['redir'] : $p_url.'&t=pst';
		http::redirect($redir.'&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Event entries list
try {
	$posts = $O->getPostsByEventdata($params);
	$counter = $O->getPostsByEventdata($params,true);
	$post_list = new eventdataEventdataList($core,$posts,$counter->f(0));

} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
$show_filters =  !$show_filters ?
 dcPage::jsLoad('js/filter-controls.js').
 '<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>' : '';

$msg = !empty($msg) ? '<p class="message">'.$msg.'</p>' : '';

$postheader = !$delete_eventdata ? eventdataAdminBehaviors::adminPostHeaders(false) : '';

?>
<html>
<head>
 <title><?php echo __('Events'); ?></title>
 <?php echo 
  dcPage::jsLoad('js/_posts_list.js').
  dcPage::jsPageTabs($request_tab).
  $postheader;
 ?>
 <link rel="stylesheet" type="text/css" href="style/date-picker.css" />
</head>
<body>
 <h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('Events'); ?></h2>
 <?php echo $msg; ?>

<?php if (!$edit_eventdata) { ?>

<div class="multi-part" id="pst" title="<?php echo $tab['pst']; ?>">
<p><?php echo __('This is the list of all entries with event'); ?></p>

<?php echo $show_filters; ?>

 <form action="<?php echo $p_url; ?>" method="get" id="filters-form">
 <fieldset><legend><?php echo __('Filters'); ?></legend>
 <div class="three-cols">
 <div class="col">
 <label>
  <?php echo __('Category:').form::combo('cat_id',$categories_combo,$cat_id); ?>
 </label> 
 <label>
  <?php echo __('Status:').form::combo('status',$status_combo,$status); ?>
 </label> 
 <label>
  <?php echo __('Selected:').form::combo('selected',$selected_combo,$selected); ?>
 </label> 
 </div>
 <div class="col">
 <label>
  <?php echo __('Order by:').form::combo('sortby',$sortby_combo,$sortby); ?>
 </label> 
 <label>
  <?php echo __('Sort:').form::combo('order',$order_combo,$order); ?>
 </label>
 </div>
 <div class="col">
 <p><label>
  <?php echo __('Period:').form::combo('period',$period_combo,$period); ?>
 </label></p> 
 <p>
  <label class="classic">
   <?php echo form::field('nb',3,3,$nb_per_page).' '.__('Entries per page'); ?>
  </label> 
  <input type="submit" value="<?php echo __('filter'); ?>" />
 </p>
 </div>
 </div>
 <br class="clear" />
 <?php echo 
  form::hidden(array('p'),'eventdata').
  form::hidden(array('t'),'pst').
  $core->formNonce();
 ?>
 </fieldset>
 </form>

<?php } else { ?>

 <div class="multi-part" id="pst" title="<?php echo $tab['pst']; ?>">
 <p><a href="<?php echo $p_url; ?>&amp;t=pst"><?php echo __('Back to list of all events'); ?></a></p>

 <?php if (!$delete_eventdata) { ?>

  <div id="edit-eventdata">
  <h3>
   <?php echo ($counter->f(0) == 1 ? 
    __('Edit this event for this entry') :
    __('Edit this event for all entries')); 
   ?>
  </h3>
  <form action="<?php echo $p_url; ?>" method="post">
  <p>
  <label for="eventdata_start"><?php echo __('Event start:'); ?></label>
  <span class="p" id="eventdata-edit-start">
   <?php echo form::field('eventdata_start',16,16,$posts->eventdata_start,'eventdata-date-start',9); ?>
  </span><br />
  <label for="eventdata_end"><?php echo __('Event end:'); ?></label>
  <span class="p" id="eventdata-edit-end">
   <?php echo form::field('eventdata_end',16,16,$posts->eventdata_end,'eventdata-date-end',10); ?>
  </span><br />
  <label for="eventdata_location"><?php echo __('Event location:'); ?></label>
  <span class="p" id="eventdata-edit-location">
   <?php echo form::field('eventdata_location',20,200,$posts->eventdata_location,'eventdata-date-location',10); ?>
  </span><br />
  <input type="submit" name="save[pst]" value="<?php echo __('edit'); ?>" />
  <?php echo form::hidden(array('action'),'eventdata_edit'); ?>

 <?php } else { ?>

  <div id="delete-eventdata">
  <h3><?php echo __('Are you sure you want to delete this event'); ?></h3>
  <form action="<?php echo $p_url; ?>" method="post">
  <p>
  <input type="submit" name="save[pst]" value="<?php echo __('yes'); ?>" /> 
  <input type="submit" name="save[pst]" value="<?php echo __('no'); ?>" />
  <?php echo form::hidden(array('action'),'eventdata_delete'); ?>

 <?php } ?>

 <?php echo 
  form::hidden(array('p'),'eventdata').
  form::hidden(array('t'),'pst').
  $core->formNonce().
  ($counter->f(0) == 1 ? form::hidden('post_id',$posts->post_id) : '').
  form::hidden('old_eventdata_start',$posts->eventdata_start).
  form::hidden('old_eventdata_end',$posts->eventdata_end).
  form::hidden('old_eventdata_location',$posts->eventdata_location).
  form::hidden(array('redir'),$p_url.'&amp;t=pst');
 ?>
 </p>
 </form>
 </div>
<?php }

$post_list->display($page,$nb_per_page,
	'<form action="posts_actions.php" method="post" id="form-actions">'.
	'%s'.
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo(array('action'),$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('status'),$status).
	form::hidden(array('selected'),$selected).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	form::hidden(array('redir'),$p_url.'&amp;t=pst').
	$core->formNonce().
	'</div>'.
	'</form>'
); ?>
</div>


<div class="multi-part" id="cat" title="<?php echo $tab['cat']; ?>">
<p><?php echo __('This is a list of all the categories that can be rearranged by dates of events'); ?></p>
<form action="<?php echo $p_url; ?>" method="post" id="form-cats">
<table class="clear">
 <tr>
  <th colspan="2"><?php echo __('Title'); ?></th>
  <th><?php echo __('Id'); ?></th>
  <th><?php echo __('Level'); ?></th>
  <th><?php echo __('Entries'); ?></th>
  <th><?php echo __('Reordered'); ?></th>
  <th><?php echo __('Unlisted'); ?></th>
 </tr>
<?php while ($categories->fetch()) : ?>
 <tr class="line">
  <td class="nowrap"><?php echo form::checkbox(array('entries[]'),$categories->cat_id,'','','',false) ;?></td>
  <td class="maximal"><a href="<?php echo $p_url.'&amp;t=pst&amp;cat_id='.$categories->cat_id; ?>">
   <?php echo html::escapeHTML($categories->cat_title); ?></a></td>
  <td class="nowrap"><?php echo $categories->cat_id; ?></td>
  <td class="nowrap"><?php echo $categories->level; ?></td>
  <td class="nowrap"><?php echo $categories->nb_post; ?></td>
  <td class="nowrap">
	<?php if (in_array($categories->cat_id,$cats_reordered) || in_array($categories->cat_title,$cats_reordered)) 
     echo sprintf($img_green,__('Reordered')); else echo sprintf($img_red,__('Normal')); ?>
  </td>
  <td class="nowrap">
   <?php if (in_array($categories->cat_id,$cats_unlisted) || in_array($categories->cat_title,$cats_unlisted)) 
    echo sprintf($img_red,__('Unlisted')); else echo sprintf($img_green,__('Normal')); ?>
  </td>
 </tr>
<?php endwhile; ?>
</table>
<div class="two-cols">
<p class="col checkboxes-helpers"></p>
<p class="col right">
 <?php echo __('Selected categories action:'); ?> 
 <?php echo form::combo(array('action'),$categories_actions_combo); ?>
 <input type="submit" name="save_cat" value="<?php echo __('ok'); ?>" />
 <?php echo 
  form::hidden(array('p'),'eventdata').
  form::hidden(array('t'),'cat').
  $core->formNonce();
 ?>
</p>
</div>
</form>
</div>


<div class="multi-part" id="tpl" title="<?php echo $tab['tpl']; ?>">
<p><?php echo __('This is the management of the public page'); ?></p>
<?php if (!$core->blog->settings->eventdata_public_active) { ?>
 <p class="error"><?php echo __('Public page is disable'); ?></p>
<?php } ?>
<form method="post" action="<?php echo $p_url; ?>">

<h2><?php echo __('Description'); ?></h2>
 <p class="col"><label class=" classic">
  <?php echo __('Title'); ?><br />
  <?php echo form::field(array('eventdata_tpl_title'), 20,255,html::escapeHTML($core->blog->settings->eventdata_tpl_title),'maximal'); ?>
 </label></p>
 <p class="area"><label class=" classic">
  <?php echo __('Description'); ?><br />
  <?php echo form::textArea(array('eventdata_tpl_desc'), 50,5,html::escapeHTML($core->blog->settings->eventdata_tpl_desc)); ?>
 </label></p>
 
<h2><?php echo __('Theme'); ?></h2>
 <ul>
  <li>
   <?php echo __('Current blog theme:'); ?>
   <strong>&nbsp;<?php echo $default_thm ;?></strong>
  </li>
  <li>
   <?php echo __('Adapted template exists:'); ?>
   <strong>&nbsp;<?php echo ($default_adt ? __('yes') : __('no')); ?></strong>
  </li>
  <li>
   <?php echo __('Template on current theme exists:'); ?>
   <strong>&nbsp;<?php echo ($default_xst ? __('yes') : __('no')); ?></strong>
  </li>
  <li>
   <?php echo __('Alternate template:'); ?>
   <strong>&nbsp;<?php echo $default_tpl; ?></strong>
  </li>
  <li>
   <?php echo __('Public URL:'); ?>
   &nbsp;<a href="<?php echo $core->blog->url.$core->url->getBase('eventdatapage'); ?>"> 
   <?php echo $core->blog->url.$core->url->getBase('eventdatapage'); ?></a>
  </li>
 </ul>
 <p class="form-note"><?php echo __('In order to change url of public page you can use plugin myUrlHandlers.'); ?></p>
 <p><label class=" classic">
  <?php echo __('Choose predefined page template in case where theme of blog does not have it'); ?><br />
  <?php echo form::combo(array('eventdata_tpl_theme'),$combo_templates,$default_tpl); ?>
 </label></p>
 <p><label class=" classic">
  <?php echo __('Disable list of dates of event on an entry'); ?><br />
  <?php echo form::combo(array('eventdata_tpl_dis_bhv'),array(__('no')=>0,__('yes')=>1),$core->blog->settings->eventdata_tpl_dis_bhv); ?>
 </label></p>
<p>
<?php echo 
 form::hidden(array('p'),'eventdata').
 form::hidden(array('t'),'tpl').
 $core->formNonce();
?>
<input type="submit" name="save_tpl" value="<?php echo __('Save'); ?>" /></p>
</form>
</div>


<div class="multi-part" id="adm" title="<?php echo $tab['adm']; ?>">
<p><?php echo __('Plugin admistration options on this blog'); ?></p>
<form method="post" action="<?php echo $p_url; ?>">
<p><label class="classic">
<?php echo 
	form::checkbox(array('eventdata_active'),'1',
		$core->blog->settings->eventdata_active).
	' '.__('Enable plugin');
?>
</label></p>
<p><label class="classic">
<?php echo 
	form::checkbox(array('eventdata_blog_menu'),'1',
		$core->blog->settings->eventdata_blog_menu).
	' '.__('Plugin icon in Blog menu');
?>
</label></p>
<p><label class="classic">
<?php echo 
	form::checkbox(array('eventdata_public_active'),'1',
		$core->blog->settings->eventdata_public_active).
	' '.__('Enable public page');
?>
</label></p>
<p>
<?php echo 
 form::hidden(array('p'),'eventdata').
 form::hidden(array('t'),'adm').
 $core->formNonce();
?>
<input type="submit" name="save_adm" value="<?php echo __('Save'); ?>" /></p>
</form>
</div>

<?php echo dcPage::helpBlock('eventdata'); ?>

<hr class="clear"/>
<p class="right">
eventdata - <?php echo $core->plugins->moduleInfo('eventdata','version'); ?>&nbsp;
<img alt="eventdata" src="index.php?pf=eventdata/icon.png" />
</p>
</body>
</html>