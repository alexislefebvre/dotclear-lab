<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class zoneclearFeedServerEntriesList extends adminGenericList
{
	public function display($page,$nb_per_page,$url,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No entry').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->base_url = $url;
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Trackbacks').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function postLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}
		
		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,
			html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('scheduled'),'scheduled.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}
		
		$protected = '';
		if ($this->rs->post_password) {
			$protected = sprintf($img,__('protected'),'locker.png');
		}
		
		$selected = '';
		if ($this->rs->post_selected) {
			$selected = sprintf($img,__('selected'),'selected.png');
		}
		
		$attach = '';
		$nb_media = $this->rs->countMedia();
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}
		
		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_comment.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_trackback.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.' '.$attach.'</td>'.
		'</tr>';
		
		return $res;
	}
}

$feed_id = '';
$feed_name = '';
$feed_desc = '';
$feed_owner = '';
$feed_tweeter = '';
$feed_url = '';
$feed_feed = '';
$feed_lang = $core->auth->getInfo('user_lang');
$feed_tags = '';
$feed_get_tags = '0';
$feed_cat_id = '';
$feed_status = '0';
$feed_upd_int = 3600;

$can_view_page = true;

$feed_headlink = '<link rel="%s" title="%s" href="'.$p_url.'&amp;part=feed&amp;feed_id=%s" />';
$feed_link = '<a href="'.$p_url.'&amp;part=feed&amp;feed_id=%s" title="%s">%s</a>';

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# Combos
$combo_langs = l10n::getISOcodes(true);
$combo_status = $zc->getAllStatus();
$combo_upd_int = $zc->getAllUpdateInterval();
$combo_categories = array('-'=>'');
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
while ($categories->fetch()) {
	$combo_categories[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
		html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}


# Get entry informations
if (!empty($_REQUEST['feed_id']))
{
	$feed = $zc->getFeeds(array('feed_id'=> $_REQUEST['feed_id']));

	if ($feed->isEmpty())
	{
		$core->error->add(__('This feed does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$feed_id = $feed->feed_id;
		$feed_name = $feed->feed_name;
		$feed_desc = $feed->feed_desc;
		$feed_owner = $feed->feed_owner;
		$feed_tweeter = $feed->feed_tweeter;
		$feed_url = $feed->feed_url;
		$feed_feed = $feed->feed_feed;
		$feed_lang = $feed->feed_lang;
		$feed_tags = $feed->feed_tags;
		$feed_get_tags = $feed->feed_get_tags;
		$feed_cat_id = $feed->cat_id;
		$feed_status = $feed->feed_status;
		$feed_upd_int = $feed->feed_upd_int;

		$next_params = array(
			'sql' => 'AND feed_id < '.$feed_id.' ',
			'limit' => 1
		);
		$next_rs = $zc->getFeeds($next_params);
		$prev_params = array(
			'sql' => 'AND feed_id > '.$feed_id.' ',
			'limit' => 1
		);
		$prev_rs = $zc->getFeeds($prev_params);
		
		if (!$next_rs->isEmpty()) {
			$next_link = sprintf($feed_link,$next_rs->feed_id,
				html::escapeHTML($next_rs->feed_name),__('next feed').'&nbsp;&#187;');
			$next_headlink = sprintf($feed_headlink,'next',
				html::escapeHTML($next_rs->feed_name),$next_rs->feed_id);
		}
		
		if (!$prev_rs->isEmpty()) {
			$prev_link = sprintf($feed_link,$prev_rs->feed_id,
				html::escapeHTML($prev_rs->feed_name),'&#171;&nbsp;'.__('previous feed'));
			$prev_headlink = sprintf($feed_headlink,'previous',
				html::escapeHTML($prev_rs->feed_name),$prev_rs->feed_id);
		}
	}
}

if ($action == 'savefeed')
{
	try {
		$feed_name = $_POST['feed_name'];
		$feed_desc = $_POST['feed_desc'];
		$feed_owner = $_POST['feed_owner'];
		$feed_tweeter = $_POST['feed_tweeter'];
		$feed_url = $_POST['feed_url'];
		$feed_feed = $_POST['feed_feed'];
		$feed_lang = $_POST['feed_lang'];
		$feed_tags = $_POST['feed_tags'];
		$feed_get_tags = empty($_POST['feed_get_tags']) ? 0 : 1;
		$feed_cat_id = $_POST['feed_cat_id'];
		if (isset($_POST['feed_status'])) {
			$feed_status = (integer) $_POST['feed_status'];
		}
		$feed_upd_int = $_POST['feed_upd_int'];

		$testfeed_params['feed_feed'] = $feed_feed;
		if ($feed_id) {
			$testfeed_params['sql'] ='AND feed_id <> '.$feed_id.' ';
		}
		if ($zc->getFeeds($testfeed_params,true)->f(0))
		{
			throw new Exception(__('Record with same feed URL already exists.'));
		}
		if (empty($feed_name))
		{
			throw new Exception(__('You must provide a name.'));
		}
		if (empty($feed_owner))
		{
			throw new Exception(__('You must provide an owner.'));
		}
		if (!zoneclearFeedServer::validateURL($feed_url))
		{
			throw new Exception(__('You must provide valid site URL.'));
		}
		if (!zoneclearFeedServer::validateURL($feed_feed))
		{
			throw new Exception(__('You must provide valid feed URL.'));
		}
		$get_feed_cat_id = $core->blog->getCategory($feed_cat_id);
		if ($feed_cat_id != '' && !$get_feed_cat_id)
		{
			throw new Exception(__('You must provide valid category.'));
		}		
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if ($action == 'savefeed' && !$core->error->flag())
{
	$cur = $zc->openCursor();
	$cur->feed_name = $feed_name;
	$cur->feed_desc = $feed_desc;
	$cur->feed_owner = $feed_owner;
	$cur->feed_tweeter = $feed_tweeter;
	$cur->feed_url = $feed_url;
	$cur->feed_feed = $feed_feed;
	$cur->feed_lang = $feed_lang;
	$cur->feed_tags = $feed_tags;
	$cur->feed_get_tags = (integer) $feed_get_tags;
	$cur->cat_id = $feed_cat_id != '' ? (integer) $feed_cat_id : null;
	$cur->feed_status = (integer) $feed_status;
	$cur->feed_upd_int = (integer) $feed_upd_int;

	# Update feed
	if ($feed_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforeZoneclearFeedServerFeedUpdate
			$core->callBehavior('adminBeforeZoneclearFeedServerFeedUpdate',$cur,$feed_id);
			
			$zc->updFeed($feed_id,$cur);
			
			# --BEHAVIOR-- adminAfterZoneclearFeedServerFeedUpdate
			$core->callBehavior('adminAfterZoneclearFeedServerFeedUpdate',$cur,$feed_id);
			
			http::redirect($p_url.'&part=feed&feed_id='.$feed_id.'&msg=editfeed');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	else
	{		
		try
		{
			# --BEHAVIOR-- adminBeforeZoneclearFeedServerFeedCreate
			$core->callBehavior('adminBeforeZoneclearFeedServerFeedCreate',$cur);
			
			$return_id = $zc->addFeed($cur);
			
			# --BEHAVIOR-- adminAfterZoneclearFeedServerFeedCreate
			$core->callBehavior('adminAfterZoneclearFeedServerFeedCreate',$cur,$return_id);
			
			http::redirect($p_url.'&part=feed&feed_id='.$return_id.'&msg=createfeed');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

# Prepared entries list
if ($feed_id && $can_view_page)
{
	# Getting categories
	try {
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	# Getting authors
	try {
		$users = $core->blog->getPostsUsers();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	# Getting dates
	try {
		$dates = $core->blog->getDates(array('type'=>'month'));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	# Getting langs
	try {
		$langs = $core->blog->getLangs();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	# Creating filter combo boxes
	if (!$core->error->flag())
	{
		# Filter form we'll put in html_block
		$users_combo = $categories_combo = array();
		$users_combo['-'] = $categories_combo['-'] = '';
		while ($users->fetch())
		{
			$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
			$users->user_firstname,$users->user_displayname);
			
			if ($user_cn != $users->user_id) {
				$user_cn .= ' ('.$users->user_id.')';
			}
			
			$users_combo[$user_cn] = $users->user_id; 
		}
		
		while ($categories->fetch()) {
			$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
				html::escapeHTML($categories->cat_title).
				' ('.$categories->nb_post.')'] = $categories->cat_id;
		}
		
		$status_combo = array(
		'-' => ''
		);
		foreach ($core->blog->getAllPostStatus() as $k => $v) {
			$status_combo[$v] = (string) $k;
		}
		
		$selected_combo = array(
		'-' => '',
		__('selected') => '1',
		__('not selected') => '0'
		);
		
		# Months array
		$dt_m_combo['-'] = '';
		while ($dates->fetch()) {
			$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
		}
		
		$lang_combo['-'] = '';
		while ($langs->fetch()) {
			$lang_combo[$langs->post_lang] = $langs->post_lang;
		}
		
		$sortby_combo = array(
		__('Date') => 'post_dt',
		__('Title') => 'post_title',
		__('Category') => 'cat_title',
		__('Author') => 'user_id',
		__('Status') => 'post_status',
		__('Selected') => 'post_selected'
		);
		
		$order_combo = array(
		__('Descending') => 'desc',
		__('Ascending') => 'asc'
		);
	}

	# Actions combo box
	$combo_action = array();
	if ($core->auth->check('publish,contentadmin',$core->blog->id))
	{
		$combo_action[__('publish')] = 'publish';
		$combo_action[__('unpublish')] = 'unpublish';
		$combo_action[__('schedule')] = 'schedule';
		$combo_action[__('mark as pending')] = 'pending';
	}
	$combo_action[__('mark as selected')] = 'selected';
	$combo_action[__('mark as unselected')] = 'unselected';
	$combo_action[__('change category')] = 'category';
	if ($core->auth->check('admin',$core->blog->id)) {
		$combo_action[__('change author')] = 'author';
	}
	if ($core->auth->check('delete,contentadmin',$core->blog->id))
	{
		$combo_action[__('delete')] = 'delete';
	}

	/* Get posts
	-------------------------------------------------------- */
	$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
	$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
	$status = isset($_GET['status']) ?	$_GET['status'] : '';
	$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
	$month = !empty($_GET['month']) ?		$_GET['month'] : '';
	$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';
	$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
	$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

	$show_filters = false;

	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
	$nb_per_page =  30;

	if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
		if ($nb_per_page != $_GET['nb']) {
			$show_filters = true;
		}
		$nb_per_page = (integer) $_GET['nb'];
	}

	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
	$params['no_content'] = true;

	# - User filter
	if ($user_id !== '' && in_array($user_id,$users_combo)) {
		$params['user_id'] = $user_id;
		$show_filters = true;
	}

	# - Categories filter
	if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
		$params['cat_id'] = $cat_id;
		$show_filters = true;
	}

	# - Status filter
	if ($status !== '' && in_array($status,$status_combo)) {
		$params['post_status'] = $status;
		$show_filters = true;
	}

	# - Selected filter
	if ($selected !== '' && in_array($selected,$selected_combo)) {
		$params['post_selected'] = $selected;
		$show_filters = true;
	}

	# - Month filter
	if ($month !== '' && in_array($month,$dt_m_combo)) {
		$params['post_month'] = substr($month,4,2);
		$params['post_year'] = substr($month,0,4);
		$show_filters = true;
	}

	# - Lang filter
	if ($lang !== '' && in_array($lang,$lang_combo)) {
		$params['post_lang'] = $lang;
		$show_filters = true;
	}

	# - Sortby and order filter
	if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
		if ($order !== '' && in_array($order,$order_combo)) {
			$params['order'] = $sortby.' '.$order;
		}
		
		if ($sortby != 'post_dt' || $order != 'desc') {
			$show_filters = true;
		}
	}

	$pager_base_url = $p_url.
		'&amp;part=feed'.
		'&amp;tab=entries'.
		'&amp;feed_id='.$feed_id.
		'&amp;user_id='.$user_id.
		'&amp;cat_id='.$cat_id.
		'&amp;status='.$status.
		'&amp;selected='.$selected.
		'&amp;month='.$month.
		'&amp;lang='.$lang.
		'&amp;sortby='.$sortby.
		'&amp;order='.$order.
		'&amp;nb='.$nb_per_page.
		'&amp;page=%s';

	# Get posts
	try {
		$params['feed_id'] = $feed_id;
		$posts = $zc->getPostsByFeed($params);
		$counter = $zc->getPostsByFeed($params,true);
		$post_list = new zoneclearFeedServerEntriesList($core,$posts,$counter->f(0));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	$header .= dcPage::jsLoad('js/_posts_list.js');
	if (!$show_filters) {
		$header .= dcPage::jsLoad('js/filter-controls.js');
	}
}

$default_tab = 'edit-entry';
if ($feed_id) {
	$default_tab = isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'entries' ?
		'entries' : 'edit-entry';
}


/* DISPLAY
-------------------------------------------------------- */

echo '<html>
<head><title>'.__('Feeds server').'</title>'.$header.
dcPage::jsPageTabs($default_tab).
$next_headlink."\n".$prev_headlink.
'</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=feeds">'.__('Feeds').'</a>';
if ($feed_id) {
	echo
	' &rsaquo; '.__('Edit feed').
	' - <a class="button" href="'.$p_url.'&amp;part=feed">'.__('New feed').'</a>';
}
else {
	echo ' &rsaquo; '.__('New feed');
}
echo '
</h2>';

# Feed
if ($can_view_page)
{
	echo $msg;
	# nav link
	if ($feed_id)
	{
		echo '<p>';
		if ($prev_link) { echo $prev_link; }
		if ($next_link && $prev_link) { echo ' - '; }
		if ($next_link) { echo $next_link; }
		echo '</p>';
	}
	echo '
	<div class="multi-part" title="'.__('Feed').'" id="edit-entry">
	<form method="post" action="plugin.php">
	<div id="entry-sidebar">'.
	'<h2>'.__('Local settings').'</h2>'.
	'<p><label>'.__('Category:').
	form::combo(array('feed_cat_id'),$combo_categories,$feed_cat_id,'maximal',3).
	'</label></p>'.
	'<p><label>'.__('Status:').
	form::combo(array('feed_status'),$combo_status,$feed_status,'maximal',3).
	'</label></p>'.
	'<p><label>'.__('Update:').
	form::combo(array('feed_upd_int'),$combo_upd_int,$feed_upd_int,'maximal',3).
	'</label></p>'.
	'<p><label>'.__('Lang:').
	form::combo(array('feed_lang'),$combo_langs,$feed_lang,'maximal',5).
	'</label></p>'.
	'<p><label class="classic">'.
	form::checkbox(array('feed_get_tags'),'1',$feed_get_tags,'',3).' '.
	__('Import tags from feed').'</label></p>'.
	'</div>'.
	'<div id="entry-content"><fieldset class="constrained">'.
	'<h2>'.__('Feed information').'</h2>'.
	'<p><label class="required">'.__('Name:').
	form::field('feed_name',60,255,$feed_name,'maximal',2).
	'</label></p>'.
	'<p><label class="required">'.__('Owner:').
	form::field(array('feed_owner'),60,255,$feed_owner,'maximal',2).
	'</label></p>'.
	'<p><label>'.__('Tweeter or Identica ident:').
	form::field(array('feed_tweeter'),60,64,$feed_tweeter,'maximal',2).
	'</label></p>'.
	'<p><label class="required">'.__('Site URL:').
	form::field(array('feed_url'),60,255,$feed_url,'maximal',2).
	'</label></p>'.
	'<p><label class="required">'.__('Feed URL:').
	form::field(array('feed_feed'),60,255,$feed_feed,'maximal',2).
	'</label></p>'.
	'<p><label>'.__('Description:').
	form::field(array('feed_desc'),60,255,$feed_desc,'maximal',2).
	'</label></p>'.
	'<p><label>'.__('Tags:').
	form::field(array('feed_tags'),60,255,$feed_tags,'maximal',2).
	'</label></p>'.
	'</div>'.

	'<p class="clear">'.
	form::hidden(array('action'),'savefeed').
	form::hidden(array('feed_id'),$feed_id).
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('part'),'feed').
	$core->formNonce().
	'<input type="submit" name="save" value="'.__('save').'" /></p>
	</form>
	</div>';
}

# Entries
if ($feed_id && $can_view_page && !$core->error->flag())
{
	echo '<div class="multi-part" title="'.__('Entries').'" id="entries">';
	
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'&amp;part=feed" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<label>'.__('Author:').
	form::combo('user_id',$users_combo,$user_id).'</label> '.
	'<label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<label>'.__('Selected:').
	form::combo('selected',$selected_combo,$selected).'</label> '.
	'<label>'.__('Month:').
	form::combo('month',$dt_m_combo,$month).'</label> '.
	'<label>'.__('Lang:').
	form::combo('lang',$lang_combo,$lang).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label> '.
	'<label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" />'.
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('part'),'feed').
	form::hidden(array('tab'),'entries').
	form::hidden(array('feed_id'),$feed_id).'</p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	# Show posts
	$post_list->display($page,$nb_per_page,$pager_base_url,
		'<form action="posts_actions.php" method="post" id="form-entries">'.
		
		'%s'.
		
		'<div class="two-cols">'.
		'<p class="col checkboxes-helpers"></p>'.
		
		'<p class="col right">'.__('Selected entries action:').' '.
		form::combo('action',$combo_action).
		'<input type="submit" name="save" value="'.__('ok').'" /></p>'.
		form::hidden(array('redir'),
			$p_url.
			'&part=feed'.
			'&tab=entries'.
			'&feed_id='.$feed_id.
			'&user_id='.$user_id.
			'&cat_id='.$cat_id.
			'&status='.$status.
			'&selected='.$selected.
			'&month='.$month.
			'&lang='.$lang.
			'&sortby='.$sortby.
			'&order='.$order.
			'&page='.$page.
			'&nb='.$nb_per_page.
			'&msg=postaction'
		).
		$core->formNonce().
		'</div>'.
		'</form>'
	);
	
	echo '</div>';
}


dcPage::helpBlock('zoneclearFeedServer');
echo $footer.'</body></html>';
?>