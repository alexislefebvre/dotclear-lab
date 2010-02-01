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

dcPage::check('admin');

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'settings';

$zc = new zoneclearFeedServer($core);

# POST
$new_name = isset($_POST['new_name']) ? $_POST['new_name'] : '';
$new_desc = isset($_POST['new_desc']) ? $_POST['new_desc'] : '';
$new_owner = isset($_POST['new_owner']) ? $_POST['new_owner'] : '';
$new_siteurl = isset($_POST['new_siteurl']) ? $_POST['new_siteurl'] : '';
$new_feedurl = isset($_POST['new_feedurl']) ? $_POST['new_feedurl'] : '';
$new_lang = isset($_POST['new_lang']) ? $_POST['new_lang'] : $core->auth->getInfo('user_lang');
$new_tags = isset($_POST['new_tags']) ? $_POST['new_tags'] : '';
$new_cat_id = isset($_POST['new_cat_id']) ? $_POST['new_cat_id'] : '';
$new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '0';
$new_upd_int = isset($_POST['new_upd_int']) ? $_POST['new_upd_int'] : 3600;

$upd_feed_id = isset($_REQUEST['feed_id']) ? $_REQUEST['feed_id'] : null;
$edit_name = isset($_POST['edit_name']) ? $_POST['edit_name'] : '';
$edit_desc = isset($_POST['edit_desc']) ? $_POST['edit_desc'] : '';
$edit_owner = isset($_POST['edit_owner']) ? $_POST['edit_owner'] : '';
$edit_siteurl = isset($_POST['edit_siteurl']) ? $_POST['edit_siteurl'] : '';
$edit_feedurl = isset($_POST['edit_feedurl']) ? $_POST['edit_feedurl'] : '';
$edit_lang = isset($_POST['edit_lang']) ? $_POST['edit_lang'] : $core->auth->getInfo('user_lang');
$edit_tags = isset($_POST['edit_tags']) ? $_POST['edit_tags'] : '';
$edit_cat_id = isset($_POST['edit_cat_id']) ? $_POST['edit_cat_id'] : '';
$edit_status = isset($_POST['edit_status']) ? $_POST['edit_status'] : '0';
$edit_upd_int = isset($_POST['edit_upd_int']) ? $_POST['edit_upd_int'] : 3600;

# Settings
$s =& $core->blog->settings;
$_active = (boolean) $s->zoneclearFeedServer_active;
$_post_status_new = (boolean) $s->zoneclearFeedServer_post_status_new;
$_update_limit = (integer) $s->zoneclearFeedServer_update_limit;
if ($_update_limit < 2) $_update_limit = 10;
$_post_full_tpl = @unserialize($s->zoneclearFeedServer_post_full_tpl);
if (!is_array($_post_full_tpl)) $_post_full_tpl = array();
$_user = (string) $s->zoneclearFeedServer_user;

try
{
	# Update settings
	if (!empty($_POST['settings']))
	{
		$limit = abs((integer) $_POST['_update_limit']);
		if ($limit < 2) $limit = 10;
		$s->setNamespace('zoneclearFeedServer');
		$s->put('zoneclearFeedServer_active',!empty($_POST['_active']));
		$s->put('zoneclearFeedServer_post_status_new',!empty($_POST['_post_status_new']));
		$s->put('zoneclearFeedServer_update_limit',$limit);
		$s->put('zoneclearFeedServer_post_full_tpl',serialize($_POST['_post_full_tpl']));
		$s->put('zoneclearFeedServer_user',$_POST['_user']);
		$s->setNamespace('system');

		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=zoneclearFeedServer&tab=settings&save=1');
	}
	
	# Edit feed
	if (!empty($_POST['editfeed']))
	{
		if (!$zc->getFeeds(array('id'=>$upd_feed_id),true)->f(0))
		{
			throw new Exception(__('Unknown record.'));
		}
		if (empty($edit_name))
		{
			throw new Exception(__('You must provide a name.'));
		}
		if (empty($edit_owner))
		{
			throw new Exception(__('You must provide an owner.'));
		}
		if (!zoneclearFeedServer::validateURL($edit_siteurl))
		{
			throw new Exception(__('You must provide valid site URL.'));
		}
		if (!zoneclearFeedServer::validateURL($edit_feedurl))
		{
			throw new Exception(__('You must provide valid feed URL.'));
		}
		$cat_id = $core->blog->getCategory($edit_cat_id);
		if ($edit_cat_id != '' && !$cat_id)
		{
			throw new Exception(__('You must provide valid category.'));
		}
		
		$cur = $zc->openCursor();
		$cur->name = $edit_name;
		$cur->desc = $edit_desc;
		$cur->owner = $edit_owner;
		$cur->url = $edit_siteurl;
		$cur->feed = $edit_feedurl;
		$cur->lang = $edit_lang;
		$cur->tags = $edit_tags;
		$cur->cat_id = $edit_cat_id != '' ? (integer) $edit_cat_id : null;
		$cur->status = (integer) $edit_status;
		$cur->upd_int = (integer) $edit_upd_int;

		$zc->updFeed($upd_feed_id,$cur);
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=editfeed&feed_id='.$upd_feed_id.'&save=1');
	}
	
	# Add feed
	if (!empty($_POST['newfeed']))
	{
		if ($zc->getFeeds(array('feed'=>$new_feedurl),true)->f(0))
		{
			throw new Exception(__('Record with same feed URL already exists.'));
		}
		if (empty($new_name))
		{
			throw new Exception(__('You must provide a name.'));
		}
		if (empty($new_owner))
		{
			throw new Exception(__('You must provide an owner.'));
		}
		if (!zoneclearFeedServer::validateURL($new_siteurl))
		{
			throw new Exception(__('You must provide valid site URL.'));
		}
		if (!zoneclearFeedServer::validateURL($new_feedurl))
		{
			throw new Exception(__('You must provide valid feed URL.'));
		}
		$cat_id = $core->blog->getCategory($new_cat_id);
		if ($new_cat_id != '' && !$cat_id)
		{
			throw new Exception(__('You must provide valid category.'));
		}

		$cur = $zc->openCursor();
		$cur->name = $new_name;
		$cur->desc = $new_desc;
		$cur->owner = $new_owner;
		$cur->url = $new_siteurl;
		$cur->feed = $new_feedurl;
		$cur->lang = $new_lang;
		$cur->tags = $new_tags;
		$cur->cat_id = $new_cat_id != '' ? (integer) $new_cat_id : null;
		$cur->status = (integer) $new_status;
		$cur->upd_int = (integer) $new_upd_int;

		$upd_feed_id = $zc->addFeed($cur);
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=editfeed&feed_id='.$upd_feed_id.'&save=1');
	}

	# Delete posts
	if (!empty($_POST['feeds']) && $_POST['action'] == 'deletepost')
	{
		$types = array(
			'zoneclearfeed_url',
			'zoneclearfeed_author',
			'zoneclearfeed_site',
			'zoneclearfeed_sitename',
			'zoneclearfeed_id'
		);
		foreach($_POST['feeds'] as $feed_id)
		{
			$posts = $zc->getPostsByFeed(array('feed_id'=>$feed_id));
			while($posts->fetch())
			{
				$core->blog->delPost($posts->post_id);
				$core->con->execute(
					'DELETE FROM '.$core->prefix.'meta '.
					'WHERE post_id = '.$posts->post_id.' '.
					'AND meta_type '.$core->con->in($types).' '
				);
			}
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}

	# Delete feeds
	if (!empty($_POST['feeds']) && $_POST['action'] == 'deletefeed')
	{
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->delFeed($feed_id);
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}

	# Enable feeds
	if (!empty($_POST['feeds']) && $_POST['action'] == 'disablefeed')
	{
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->enableFeed($feed_id,false);
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}

	# Disable feeds
	if (!empty($_POST['feeds']) && $_POST['action'] == 'enablefeed')
	{
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->enableFeed($feed_id,true);
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}

	# Move to right tab
	if (!empty($_POST['feeds']) && $_POST['action'] == 'changecat')
	{
		$default_tab = 'changecat';
	}

	# Update category for a group of feeds
	if (!empty($_POST['feeds']) && !empty($_POST['updfeedcat']))
	{
		foreach($_POST['feeds'] as $feed_id)
		{
			$cur = $zc->openCursor();
			$cur->cat_id = abs((integer) $_POST['upd_cat_id']);
			$zc->updFeed($feed_id,$cur);
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}

	# Update category for a group of feeds
	if (!empty($_POST['feeds']) && $_POST['action'] == 'resetupdlast')
	{
		foreach($_POST['feeds'] as $feed_id)
		{
			$cur = $zc->openCursor();
			$cur->upd_last = 0;
			$zc->updFeed($feed_id,$cur);
		}
		http::redirect('plugin.php?p=zoneclearFeedServer&tab=feedslist&save=1');
	}
}
catch(Exception $e)
{
	$core->error->add($e->getMessage());
}

# Combos
$combo_admins = $zc->getAllBlogAdmins();
$combo_langs = l10n::getISOcodes(true);
$combo_status = $zc->getAllStatus();
$combo_upd_int = $zc->getAllUpdateInterval();
$combo_sortby = array(
	__('Date') => 'upddt',
	__('Name') => 'name',
	__('frequency') => 'upd_int',
	__('Date of update') => 'last_upd',
	__('Status') => 'status'
);
$combo_order = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$combo_feeds_action = array(
	__('delete related posts') => 'deletepost',
	__('delete feed (whitout related posts)') => 'deletefeed',
	__('change category') => 'changecat',
	__('disable feed update') => 'disablefeed',
	__('enable feed update') => 'enablefeed',
	__('Reset last update') => 'resetupdlast'
);
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

# prepared lists
$show_filters = false;
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'upddt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
{
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

if ($sortby != '' && in_array($sortby,$combo_sortby))
{
	if ($order != '' && in_array($order,$combo_order))
		$params['order'] = $sortby.' '.$order;

	if ($sortby != 'upddt' || $order != 'desc')
		$show_filters = true;
}

$pager_base_url = $p_url.
	'&amp;tab=feedslist'.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

try
{
	$feeds = $zc->getFeeds($params);
	$feeds_counter = $zc->getFeeds($params,true)->f(0);
	$feeds_list = new zoneclearFeedServerLists($core,$feeds,$feeds_counter,$pager_base_url);
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

# Header
echo '
<html><head><title>'.__('Zoneclear feed server').'</title>'.
dcPage::jsToolBar().
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsPageTabs($default_tab).'
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Zoneclear feed server').'</h2>';

# Settings
echo '
<div class="multi-part" id="settings" title="'.__('Options').'">';

if ($default_tab == 'settings' && isset($_GET['save'])) { 
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

echo '
<form method="post" action="plugin.php">
<h2>'.__('General').'</h2>
<p><label class="classic">'.form::checkbox(array('_active'),'1',$_active).' '.__('Enable plugin').'</label></p>
<p><label class="classic">'.form::checkbox('_post_status_new',1,$_post_status_new).' '.__('Publish new feed posts').'</label></p>
<p><label class="classic">'.__('Number of feeds to update at one time:').'<br />'.
form::field('_update_limit',6,4,$_update_limit).'</label></p>
<p><label class="classic">'.__('Owner of entries created by zoneclearFeedServer:').'<br />'.
form::combo(array('_user'),$combo_admins,$_user).'</label></p>
<h2>'.__('Entries').'</h2>';

$types = array(
	__('home page') => 'default',
	__('post pages') => 'post',
	__('tags pages') => 'tag',
	__('archives pages') => 'archive',
	__('category pages') => 'category'
);
foreach($types as $k => $v)
{
	echo '
	<p><label class="classic">'.
	form::checkbox(array('_post_full_tpl[]'),$v,in_array($v,$_post_full_tpl)).' '.
	sprintf(__('Show full content on %s'),__($k)).'</label></p>';
}
echo '
<p class="clear">'.
form::hidden(array('p'),'zoneclearFeedServer').
form::hidden(array('tab'),'settings').
$core->formNonce().'
<input type="submit" name="settings" value="'.__('save').'" /></p>
</form>
</div>';

# Add feed
echo '
<div class="multi-part" id="newfeed" title="'.__('New feed').'">
<form method="post" action="plugin.php">
<table>
<tr><td>'.__('Name:').'</td><td>'.form::field('new_name',60,255,$new_name).'</td></tr>
<tr><td>'.__('Description:').'</td><td>'.form::field(array('new_desc'),60,255,$new_desc).'</td></tr>
<tr><td>'.__('Owner:').'</td><td>'.form::field(array('new_owner'),60,255,$new_owner).'</td></tr>
<tr><td>'.__('Site URL:').'</td><td>'.form::field(array('new_siteurl'),60,255,$new_siteurl).'</td></tr>
<tr><td>'.__('Feed URL:').'</td><td>'.form::field(array('new_feedurl'),60,255,$new_feedurl).'</td></tr>
<tr><td>'.__('Lang:').'</td><td>'.form::combo(array('new_lang'),$combo_langs,$new_lang).'</td></tr>
<tr><td>'.__('Tags:').'</td><td>'.form::field(array('new_tags'),60,255,$new_tags).'</td></tr>
<tr><td>'.__('Category:').'</td><td>'.form::combo(array('new_cat_id'),$combo_categories,$new_cat_id).'</td></tr>
<tr><td>'.__('Status:').'</td><td>'.form::combo(array('new_status'),$combo_status,$new_status).'</td></tr>
<tr><td>'.__('Update:').'</td><td>'.form::combo(array('new_upd_int'),$combo_upd_int,$new_upd_int).'</td></tr>
</td></tr>
</table>

<p class="clear">'.
form::hidden(array('p'),'zoneclearFeedServer').
form::hidden(array('tab'),'newfeed').
$core->formNonce().
'<input type="submit" name="newfeed" value="'.__('save').'" /></p>
</form>
</div>';

# Edit feed
if (null !== $upd_feed_id)
{
	$upd_rs = $zc->getFeeds(array('id'=>$upd_feed_id));
	$upd_feed_id = null;
	if (!$upd_rs->isEmpty())
	{
		$edit_name = $upd_rs->name;
		$edit_desc = $upd_rs->desc;
		$edit_owner = $upd_rs->owner;
		$edit_siteurl = $upd_rs->url;
		$edit_feedurl = $upd_rs->feed;
		$edit_lang = $upd_rs->lang;
		$edit_tags = $upd_rs->tags;
		$edit_cat_id = $upd_rs->cat_id;
		$edit_status = $upd_rs->status;
		$edit_upd_int = $upd_rs->upd_int;
		$upd_feed_id = $upd_rs->id;
	}
	echo '
	<div class="multi-part" id="editfeed" title="'.__('Edit feed').'">';

	if ($default_tab == 'editfeed' && isset($_GET['save'])) { 
		echo '<p class="message">'.__('Configuration successfully saved').'</p>';
	}

	echo '
	<form method="post" action="plugin.php">
	<table>
	<tr><td>'.__('ID:').'</td><td>'.$upd_feed_id.'</td></tr>
	<tr><td>'.__('Name:').'</td><td>'.form::field('edit_name',60,255,$edit_name).'</td></tr>
	<tr><td>'.__('Description:').'</td><td>'.form::field(array('edit_desc'),60,255,$edit_desc).'</td></tr>
	<tr><td>'.__('Owner:').'</td><td>'.form::field(array('edit_owner'),60,255,$edit_owner).'</td></tr>
	<tr><td>'.__('Site URL:').'</td><td>'.form::field(array('edit_siteurl'),60,255,$edit_siteurl).'</td></tr>
	<tr><td>'.__('Feed URL:').'</td><td>'.form::field(array('edit_feedurl'),60,255,$edit_feedurl).'</td></tr>
	<tr><td>'.__('Lang:').'</td><td>'.form::combo(array('edit_lang'),$combo_langs,$edit_lang).'</td></tr>
	<tr><td>'.__('Tags:').'</td><td>'.form::field(array('edit_tags'),60,255,$edit_tags).'</td></tr>
	<tr><td>'.__('Category:').'</td><td>'.form::combo(array('edit_cat_id'),$combo_categories,$edit_cat_id).'</td></tr>
	<tr><td>'.__('Status:').'</td><td>'.form::combo(array('edit_status'),$combo_status,$edit_status).'</td></tr>
	<tr><td>'.__('Update:').'</td><td>'.form::combo(array('edit_upd_int'),$combo_upd_int,$edit_upd_int).'</td></tr>
	</td></tr>
	</table>

	<p class="clear">'.
	form::hidden(array('feed_id'),$upd_feed_id).
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('tab'),'editfeed').
	$core->formNonce().
	'<input type="submit" name="editfeed" value="'.__('save').'" /></p>
	</form>
	</div>';
}

# Change category for links
if (!empty($_POST['feeds']) && $_POST['action'] == 'changecat')
{
	echo '
	<div class="multi-part" id="changecat" title="'.__('Change feeds category').'">
	<form method="post" action="plugin.php">
	<p>'.__('This changes category for all selected feeds.').'</p>';

	foreach($_POST['feeds'] as $feed_id)
	{
		echo
		'<p><label class="classic">'.
		form::checkbox(array('feeds[]'),$feed_id,1).' '.
		$zc->getFeeds(array('id'=>$feed_id))->f('name').
		'</label></p>';
	}

	echo '
	<p>'.__('Select a category:').' '.
	form::combo(array('upd_cat_id'),$combo_categories,'').' 
	<input type="submit" name="updfeedcat" value="ok" />'.
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('tab'),'feeds').
	$core->formNonce().'
	</p>
	</form>
	</div>';
}

# Feeds list
echo '
<div class="multi-part" id="feedslist" title="'.__('Feeds list').'">';

if ($default_tab == 'feedslist' && isset($_GET['save'])) { 
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

if (!$show_filters)
{ 
   echo 
   dcPage::jsLoad('js/filter-controls.js').'
   <p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
}
echo '
<form action="plugin.php?p=zoneclearFeedServer&amp;tab=feedslist" method="get" id="filters-form">
<fieldset><legend>'.__('Filters').'</legend>
<div class="three-cols">
<div class="col">
<label>'.__('Order by:').form::combo('sortby',$combo_sortby,$sortby).'</label> 
</div>
<div class="col">
<label>'.__('Sort:').form::combo('order',$combo_order,$order).'</label>
</div>
<div class="col">
<p>
<label class="classic">'.
form::field('nb',3,3,$nb_per_page).' '.__('Entries per page').'
</label> 
<input type="submit" value="'.__('filter').'" />'.
form::hidden(array('p'),'zoneclearFeedServer').
form::hidden(array('tab'),'feedslist').'
</p>
</div>
</div>
<br class="clear" />
</fieldset>
</form>
<form action="plugin.php" method="post" id="form-actions">';

$feeds_list->feedsDisplay($page,$nb_per_page,$pager_base_url);

echo '
<div class="two-cols">
<p class="col checkboxes-helpers"></p>
<p class="col right">'.__('Selected feeds action:').' '.
form::combo(array('action'),$combo_feeds_action).'
<input type="submit" value="'.__('ok').'" />'.
form::hidden(array('sortby'),$sortby).
form::hidden(array('order'),$order).
form::hidden(array('page'),$page).
form::hidden(array('nb'),$nb_per_page).
form::hidden(array('p'),'zoneclearFeedServer').
form::hidden(array('tab'),'feedslist').
$core->formNonce().'
</p>
</div>
</form>
</div>';

# Footer
echo '
<hr class="clear"/>
<p class="right">
zoneclearFeedServer - '.$core->plugins->moduleInfo('zoneclearFeedServer','version').'&nbsp;
<img alt="'.__('zoneclearFeedServer').'" src="index.php?pf=zoneclearFeedServer/icon.png" />
</p></body></html>';
?>