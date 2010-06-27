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

class zoneclearFeedServerFeedsList extends adminGenericList
{
	public function feedsDisplay($page,$nb_per_page,$url)
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('There is no feed').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			
			$pager->base_url = $url;
			
			$html_block =
			'<table class="clear">'.
			'<thead>'.
			'<tr>'.
			'<th class="nowrap" colspan="2">'.__('Name').'</th>'.
			'<th>'.__('Feed').'</th>'.
			'<th>'.__('Lang').'</th>'.
			'<th>'.__('Tags').'</th>'.
			'<th>'.__('Frequency').'</th>'.
			'<th class="nowrap">'.__('Last update').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Owner').'</th>'.
			'<th>'.__('Entries').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>%s</tbody>'.
			'</table>';
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page)
			{
				echo $this->feedsLine($url,$iter);

				if ($this->rs->isEnd())
				{
					break;
				}
				else
				{
					$this->rs->moveNext();
				}
				$iter++;
			}
			echo $blocks[1];
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function feedsLine($url,$loop)
	{
		$combo_status = zoneclearFeedServer::getAllStatus();
		$combo_upd_int = zoneclearFeedServer::getAllUpdateInterval();
		$status = $this->rs->feed_status ? 
			'<img src="images/check-on.png" alt="enable" />' :
			'<img src="images/check-off.png" alt="disable" />';
		$category = $this->rs->cat_id ? $this->rs->cat_title : __('none');
		
		$entries_count = $this->rs->zc->getPostsByFeed(array('feed_id'=>$this->rs->feed_id),true)->f(0);
		
		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('feeds[]'),$this->rs->feed_id,0).
		'</td>'.
		'<td class="nowrap">'.
		'<a href="plugin.php?p=zoneclearFeedServer&amp;part=feed&amp;feed_id='.$this->rs->feed_id.'" title="'.__('Edit').'">'.
		html::escapeHTML($this->rs->feed_name).'</a>'.
		"</td>\n".
		'<td class="maximal nowrap">'.
		'<a href="'.$this->rs->feed_feed.'" title="'.html::escapeHTML($this->rs->feed_desc).'">'.$this->rs->feed_feed.'</a>'.
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_lang).
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_tags).
		"</td>\n".
		'<td class="nowrap">'.
		array_search($this->rs->feed_upd_int,$combo_upd_int).
		"</td>\n".
		'<td class="nowrap">'.
		($this->rs->feed_upd_last < 1 ? 
			__('never') :
			dt::str(__('%Y-%m-%d %H:%M'),$this->rs->feed_upd_last,$this->rs->zc->core->auth->getInfo('user_tz'))
		).
		"</td>\n".
		'<td>'.
		html::escapeHTML($category).
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_owner).
		"</td>\n".
		'<td class="nowrap">'.
		($entries_count ? 
			'<a href="plugin.php?p=zoneclearFeedServer&amp;part=feed&amp;tab=entries&amp;feed_id='.$this->rs->feed_id.'" title="'.__('View entries').'">'.$entries_count.'</a>' :
			$entries_count
		).
		"</td>\n".
		'<td>'.
		$status.
		"</td>\n".
		'</tr>'."\n";
	}
}

# Actions
$feeds_action = '';

# Delete posts
if ($action == 'deletepost' && !empty($_POST['feeds']))
{
	try
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
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Delete feeds
if ($action == 'deletefeed' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->delFeed($feed_id);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Enable feeds
if ($action == 'enablefeed' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->enableFeed($feed_id,true);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Disable feeds
if ($action == 'disablefeed' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->enableFeed($feed_id,false);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Update (check) feeds
if ($action == 'updatefeed' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$zc->checkFeedsUpdate($feed_id);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Move to right part
if ($action == 'changecat' && !empty($_POST['feeds']))
{
	$feeds_action = 'changecat';
}

# Update category for a group of feeds
if ($action == 'updfeedcat' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$cur = $zc->openCursor();
			$cur->cat_id = abs((integer) $_POST['upd_cat_id']);
			$zc->updFeed($feed_id,$cur);
		}
		http::redirect($p_url.'&part=feeds&msg=changecat');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Move to right part
if ( $action == 'changeint' && !empty($_POST['feeds']))
{
	$feeds_action = 'changeint';
}

# Update interval for a group of feeds
if ($action == 'updfeedint' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$cur = $zc->openCursor();
			$cur->feed_upd_int = abs((integer) $_POST['upd_upd_int']);
			$zc->updFeed($feed_id,$cur);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Set 0 last update for a group of feeds
if ($action == 'resetupdlast' && !empty($_POST['feeds']))
{
	try {
		foreach($_POST['feeds'] as $feed_id)
		{
			$cur = $zc->openCursor();
			$cur->feed_upd_last = 0;
			$zc->updFeed($feed_id,$cur);
		}
		http::redirect($p_url.'&part=feeds&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Combos
$combo_langs = l10n::getISOcodes(true);
$combo_status = $zc->getAllStatus();
$combo_upd_int = $zc->getAllUpdateInterval();
$combo_sortby = array(
	__('Date') => 'feed_upddt',
	__('Name') => 'lowername',
	__('frequency') => 'feed_upd_int',
	__('Date of update') => 'feed_upd_last',
	__('Status') => 'feed_status'
);
$combo_order = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$combo_feeds_action = array(
	__('change category') => 'changecat',
	__('change update interval') => 'changeint',
	__('disable feed update') => 'disablefeed',
	__('enable feed update') => 'enablefeed',
	__('Reset last update') => 'resetupdlast',
	__('Update (check) feed') => 'updatefeed',
	__('delete related posts') => 'deletepost',
	__('delete feed (without related posts)') => 'deletefeed'
);
$combo_categories = array('-'=>'');
try
{
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
while ($categories->fetch())
{
	$combo_categories[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
		html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

# Prepared lists
$show_filters = false;
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'feed_upddt';
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
	{
		$params['order'] = $sortby.' '.$order;
	}
	if ($sortby != 'feed_upddt' || $order != 'desc')
	{
		$show_filters = true;
	}
}

$pager_base_url = $p_url.
	'&amp;part=feeds'.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

try
{
	$feeds = $zc->getFeeds($params);
	$feeds_counter = $zc->getFeeds($params,true)->f(0);
	$feeds_list = new zoneclearFeedServerFeedsList($core,$feeds,$feeds_counter,$pager_base_url);
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (!$show_filters)
{
	$header .= dcPage::jsLoad('js/filter-controls.js');
}

echo '
<html>
<head><title>'.__('Feeds server').'</title>'.$header.
dcPage::jsLoad('index.php?pf=zoneclearFeedServer/js/feeds.js').
'</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; '.__('Feeds').
' - <a class="button" href="'.$p_url.'&amp;part=feed">'.__('New feed').'</a>'.
'</h2>'.$msg;

# Category form
if ($feeds_action == 'changecat')
{
	echo '
	<form method="post" action="'.$p_url.'">
	<p>'.__('This changes category for all selected feeds.').'</p>';
	
	foreach($_POST['feeds'] as $feed_id)
	{
		echo
		'<p><label class="classic">'.
		form::checkbox(array('feeds[]'),$feed_id,1).' '.
		$zc->getFeeds(array('feed_id'=>$feed_id))->f('feed_name').
		'</label></p>';
	}
	
	echo '
	<p>'.__('Select a category:').' '.
	form::combo(array('upd_cat_id'),$combo_categories,'').' 
	<input type="submit" name="updfeedcat" value="ok" />'.
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('action'),'updfeedcat').
	form::hidden(array('part'),'feeds').
	$core->formNonce().'
	</p>
	</form>
	<p><a href="'.$p_url.'&amp;part=feeds">'.__('back').'</a></p>';
}
# Interval form
elseif ($feeds_action == 'changeint')
{
	echo '
	<form method="post" action="'.$p_url.'">
	<p>'.__('This changes interval of updates for all selected feeds.').'</p>';
	
	foreach($_POST['feeds'] as $feed_id)
	{
		echo
		'<p><label class="classic">'.
		form::checkbox(array('feeds[]'),$feed_id,1).' '.
		$zc->getFeeds(array('feed_id'=>$feed_id))->f('feed_name').
		'</label></p>';
	}
	
	echo '
	<p>'.__('Select a frequency:').' '.
	form::combo(array('upd_upd_int'),$combo_upd_int,'').' 
	<input type="submit" name="updfeedint" value="ok" />'.
	form::hidden(array('p'),'zoneclearFeedServer').
	form::hidden(array('action'),'updfeedint').
	form::hidden(array('part'),'feeds').
	$core->formNonce().'
	</p>
	</form>
	<p><a href="'.$p_url.'&amp;part=feeds">'.__('back').'</a></p>';
}
# Feed list
else
{
	if ($core->error->flag())
	{
		echo '<p>'.__('An error occured when try to get list of feeds').'</p>';
	}
	else
	{
		if (!$show_filters) 
		{
			echo '<p><a id="filter-control" class="form-control" href="#">'.
			__('Filters').'</a></p>';
		}
		
		echo 
		'<form action="'.$p_url.'&amp;part=feeds" method="get" id="filters-form">
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
		form::hidden(array('part'),'feeds').'
		</p>
		</div>
		</div>
		<br class="clear" />
		</fieldset>
		</form>
		<form action="'.$p_url.'&amp;part=feeds" method="post" id="form-actions">';
		
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
		form::hidden(array('part'),'feeds').
		$core->formNonce().'
		</p>
		</div>
		</form>';
	}
}

dcPage::helpBlock('zoneclearFeedServer');
echo $footer.'</body></html>';
?>