<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
dcPage::check('content');

require_once dirname(__FILE__).'/inc/lib.cinecturlink2.list.php';

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'settings';

$C2 = new cinecturlink2($core);

# Settings
$s =& $core->blog->settings;
$root = DC_ROOT.'/'.$s->public_path;
$_active = (boolean) $s->cinecturlink2_active;
$_widthmax = abs((integer) $s->cinecturlink2_widthmax);
$_folder = (string) $s->cinecturlink2_folder;
$_triggeronrandom = (boolean) $s->cinecturlink2_triggeronrandom;
$_public_active = (boolean) $s->cinecturlink2_public_active;
$_public_title = (string) $s->cinecturlink2_public_title;
$_public_description = (string) $s->cinecturlink2_public_description;
$_public_nbrpp = (integer) $s->cinecturlink2_public_nbrpp;
if ($_public_nbrpp < 1) $_public_nbrpp = 10;

# POST
$upd_link_id = isset($_REQUEST['link_id']) ? $_REQUEST['link_id'] : null;
$new_title = isset($_POST['new_title']) ? $_POST['new_title'] : '';
$new_desc = isset($_POST['new_desc']) ? $_POST['new_desc'] : '';
$new_author = isset($_POST['new_author']) ? $_POST['new_author'] : '';
$new_url = isset($_POST['new_url']) ? $_POST['new_url'] : '';
$new_category = isset($_POST['new_category']) ? $_POST['new_category'] : '';
$new_lang = isset($_POST['new_lang']) ? $_POST['new_lang'] : $core->auth->getInfo('user_lang');
$new_image = isset($_POST['new_image']) ? $_POST['new_image'] : '';
$new_note = isset($_POST['new_note']) ? $_POST['new_note'] : 10;
$new_cattitle = isset($_POST['new_cattitle']) ? $_POST['new_cattitle'] : '';
$new_catdesc = isset($_POST['new_catdesc']) ? $_POST['new_catdesc'] : '';

try
{
	# Update settings
	if (!empty($_POST['settings']))
	{
		$_active = !empty($_POST['_active']);
		$_public_active = !empty($_POST['_public_active']);
		$_public_title = (string) $_POST['_public_title'];
		$_public_description = (string) $_POST['_public_description'];
		$_public_nbrpp = (integer) $_POST['_public_nbrpp'];
		if ($_public_nbrpp < 1) $_public_nbrpp = 10;
		$_widthmax = abs((integer) $_POST['_widthmax']);
		$_folder = (string) files::tidyFileName($_POST['_folder']);
		$_triggeronrandom = !empty($_POST['_triggeronrandom']);
		if (empty($_folder))
		{
			throw new Exception(__('You must provide a specific folder for images.'));
		}
		cinecturlink2::test_folder($root,$_folder,true);

		$s->setNamespace('cinecturlink2');
		$s->put('cinecturlink2_active',$_active);
		$s->put('cinecturlink2_public_active',$_public_active);
		$s->put('cinecturlink2_public_title',$_public_title);
		$s->put('cinecturlink2_public_description',$_public_description);
		$s->put('cinecturlink2_public_nbrpp',$_public_nbrpp);
		$s->put('cinecturlink2_widthmax',$_widthmax);
		$s->put('cinecturlink2_folder',$_folder);
		$s->put('cinecturlink2_triggeronrandom',$_triggeronrandom);
		$s->setNamespace('system');

		$core->blog->triggerBlog();

		http::redirect('plugin.php?p=cinecturlink2&tab=settings&save=1');
	}

	# Update categories
	if (!empty($_POST['updcats']))
	{
		$act = '';
		# Update categories order
		if (empty($_POST['cats_order']) && !empty($_POST['catpos']))
		{
			$order = $_POST['catpos'];
			asort($order);
			$order = array_keys($order);
		}
		elseif (!empty($_POST['cats_order']))
		{
			$order = explode(',',$_POST['cats_order']);
		}
		if (!empty($order))
		{
			foreach ($order as $pos => $id)
			{
				$pos = ((integer) $pos)+1;
				if (empty($pos) || empty($id)) continue;

				$cur = $core->con->openCursor($C2->table.'_cat');
				$cur->cat_pos = $pos;
				$C2->updCategory($id,$cur);
			}
			$act .= '&ordercat=1';
		}

		# Delete categories
		if (!empty($_POST['delcat']))
		{
			foreach($_POST['delcat'] as $cat_id)
			{
				$C2->delCategory($cat_id);
			}
			$act .= '&delcat=1';
		}
		http::redirect('plugin.php?p=cinecturlink2&tab=cats'.$act);
	}

	# Create new category
	if (!empty($_POST['newcat']))
	{
		if (empty($new_cattitle))
		{
			throw new Exception(__('You must provide a title.'));
		}
		$exists = $C2->getCategories(array('cat_title'=>$new_cattitle),true)->f(0);
		if ($exists)
		{
			throw new Exception(__('Category with same name already exists.'));
		}
		$cur = $core->con->openCursor($C2->table.'_cat');
		$cur->cat_title = $new_cattitle;
		$cur->cat_desc = $new_catdesc;

		$C2->addCategory($cur);

		http::redirect('plugin.php?p=cinecturlink2&tab=cats&newcat=1');
	}

	# Delete links
	if (!empty($_POST['links']) && $_POST['action'] == 'delete')
	{
		foreach($_POST['links'] as $link_id)
		{
			$C2->delLink($link_id);
		}
		http::redirect('plugin.php?p=cinecturlink2&tab=links&dellink=1');
	}

	# Move to right tab
	if (!empty($_POST['links']) && $_POST['action'] == 'changecat')
	{
		$default_tab = 'changecatlinks';
	}

	# Update category for a grou of links
	if (!empty($_POST['links']) && !empty($_POST['updlinkcat']))
	{
		foreach($_POST['links'] as $link_id)
		{
			$cur = $core->con->openCursor($C2->table);
			$cur->cat_id = abs((integer) $_POST['upd_category']);
			$C2->updLink($link_id,$cur);
		}
		http::redirect('plugin.php?p=cinecturlink2&tab=links&updlink=1');
	}

	# Move to right tab
	if (!empty($_POST['links']) && $_POST['action'] == 'changenote')
	{
		$default_tab = 'changenotelinks';
	}

	# Update note for a group of links
	if (!empty($_POST['links']) && !empty($_POST['updlinknote']))
	{
		foreach($_POST['links'] as $link_id)
		{
			$cur = $core->con->openCursor($C2->table);
			$cur->link_note = abs((integer) $_POST['upd_note']);
			$C2->updLink($link_id,$cur);
		}
		http::redirect('plugin.php?p=cinecturlink2&tab=links&updlink=1');
	}

	if (!empty($_POST['newlink']))
	{
		if (empty($new_title))
		{
			throw new Exception(__('You must provide a title.'));
		}
		if (empty($new_author))
		{
			throw new Exception(__('You must provide an author.'));
		}
		if (!preg_match('/http:\/\/.+/',$new_image))
		{
			throw new Exception(__('You must provide a link to an image.'));
		}

		$cur = $core->con->openCursor($C2->table);
		$cur->link_title = $new_title;
		$cur->link_desc = $new_desc;
		$cur->link_author = $new_author;
		$cur->link_url = $new_url;
		$cur->cat_id = $new_category == '' ? null : $new_category;
		$cur->link_lang = $new_lang;
		$cur->link_img = $new_image;
		$cur->link_note = $new_note;

		# Create link
		if (empty($upd_link_id))
		{
			$exists = $C2->getLinks(array('link_title'=>$new_title),true)->f(0);
			if ($exists)
			{
				throw new Exception(__('Link with same name already exists.'));
			}
			$C2->addLink($cur);
			http::redirect('plugin.php?p=cinecturlink2&tab=links&newlink=1');
		}
		# Update link
		else
		{
			$exists = $C2->getLinks(array('link_id'=>$upd_link_id),true)->f(0);
			if (!$exists)
			{
				throw new Exception(__('Unknown link.'));
			}
			$C2->updLink($upd_link_id,$cur);
			http::redirect('plugin.php?p=cinecturlink2&tab=links&updlink=1');
		}
	}
	cinecturlink2::test_folder($root,$_folder);
}
catch(Exception $e)
{
	$core->error->add($e->getMessage());
}

$show_filters = false;
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'link_upddt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
{
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

$sortby_combo = array(
__('Date') => 'link_upddt',
__('Title') => 'link_title',
__('Category') => 'cat_title',
__('My rating') => 'link_note',
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

if ($sortby != '' && in_array($sortby,$sortby_combo))
{
	if ($order != '' && in_array($order,$order_combo))
		$params['order'] = $sortby.' '.$order;

	if ($sortby != 'link_upddt' || $order != 'desc')
		$show_filters = true;
}

$categories = $C2->getCategories();

$categories_combo = array('-'=> '');
while($categories->fetch())
{
	$cat_title = html::escapeHTML($categories->cat_title);
	$categories_combo[$cat_title] = $categories->cat_id;
}

$allowed_medias = array('png','jpg','gif','bmp','jpeg');
$core->media = new dcMedia($core);
$core->media->chdir($_folder);
$core->media->getDir();
$dir =& $core->media->dir;
$medias_combo = array('-'=>'');
foreach($dir['files'] as $file)
{
	if (!in_array(files::getExtension($file->relname),$allowed_medias)) continue;
	$medias_combo[$file->media_title] = $file->file_url;
}

$langs_combo = l10n::getISOcodes(true);
$links_action_combo = array(
	__('delete') => 'delete',
	__('change category') => 'changecat',
	__('change my rating') => 'changenote'
);
$notes_combo = array();
for ($i=0;$i<21;$i++)
{
	$notes_combo[$i.'/20'] = $i;
}

$pager_base_url = $p_url.
	'&amp;tab=links'.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

try
{
	$links = $C2->getLinks($params);
	$links_counter = $C2->getLinks($params,true)->f(0);
	$links_list = new adminListCinecturlink2($core,$links,$links_counter,$pager_base_url);
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

# Header
echo '
<html><head><title>'.__('Cinecturlink 2').'</title>'.
dcPage::jsToolBar().
dcPage::jsToolMan().
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsLoad('index.php?pf=cinecturlink2/js/cinecturlink2.js').
dcPage::jsPageTabs($default_tab).
"<script type=\"text/javascript\"> 
//<![CDATA[
\$(function(){if(!document.getElementById){return;} 
 \$('#newlinksearch').openGoogle('".$core->auth->getInfo('user_lang')."','#new_title'); 
 \$('#newimagesearch').openAmazon('".$core->auth->getInfo('user_lang')."','#new_title'); 
 \$('#newimageselect').fillLink('#new_image'); 
});

var dragsort = ToolMan.dragsort();
\$(function(){
 dragsort.makeTableSortable($('#links-list-cat').get(0),
 dotclear.sortable.setHandle,dotclear.sortable.saveOrder);
});

dotclear.sortable = {
  setHandle: function(item) {
	var handle = $(item).find('td.handle').get(0);
	while (handle.firstChild) {
		handle.removeChild(handle.firstChild);
	}
	
	item.toolManDragGroup.setHandle(handle);
	handle.className = handle.className+' handler';
  },
  
  saveOrder: function(item) {
	var group = item.toolManDragGroup;
	var order = document.getElementById('cats_order');
	group.register('dragend', function() {
		order.value = '';
		items = item.parentNode.getElementsByTagName('tr');
		
		for (var i=0; i<items.length; i++) {
			order.value += items[i].id.substr(2)+',';
		}
	});
  }
};
//]]>\n".
"</script>\n".'
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
__('Cinecturlink 2').'</h2>';

# Settings
echo '
<div class="multi-part" id="settings" title="'.__('Options').'">';

if (isset($_GET['save'])) {
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';
}

echo '
<form method="post" action="plugin.php">
<h2>'.__('General').'</h2>
<p><label class="classic">'.
form::checkbox(array('_active'),'1',$_active).' 
'.__('Enable plugin').'</label></p>
<p><label class="classic">'.__('Maximum width of images (in pixel):').'<br />'.
form::field(array('_widthmax'),10,4,$_widthmax).'</label></p>
<p><label class="classic">'.__('Public folder of images (under public folder of blog):').'<br />'.
form::field(array('_folder'),60,64,$_folder).'</label></p>
<h2>'.__('Widget').'</h2>
<p><label class="classic">'.
form::checkbox(array('_triggeronrandom'),'1',$_triggeronrandom).' 
'.__('Update cache when use "Random" or "Number of view" order on widget (Need reload of widgets on change)').'</label></p>
<p class="form-note">'.__('This increases the random effect, but updates the cache of the blog whenever the widget is displayed, which reduces the perfomances of your blog.').'</p>
<h2>'.__('Public page').'</h2>
<p><label class="classic">'.
form::checkbox(array('_public_active'),'1',$_public_active).' 
'.__('Enable public page').'</label></p>
<p class="form-note">'.sprintf(__('Public page has url: %s'),'<a href="'.$core->blog->url.$core->url->getBase('cinecturlink2').'" title="public page">'.$core->blog->url.$core->url->getBase('cinecturlink2').'</a>').'</p>
<p><label class="classic">'.__('Title of the public page:').'<br />'.
form::field(array('_public_title'),60,255,$_public_title).'</label></p>
<p><label class="classic">'.__('Description of the public page:').'<br />'.
form::field(array('_public_description'),60,255,$_public_description).'</label></p>
<p><label class="classic">'.sprintf(__('Limit to %s entries per page on pulic page.'),form::field(array('_public_nbrpp'),5,10,$_public_nbrpp)).'</label></p>
<p>'.
form::hidden(array('p'),'cinecturlink2').
form::hidden(array('tab'),'settings').
$core->formNonce().'
<input type="submit" name="settings" value="'.__('save').'" />
</p>
</form>
<h2>'.__('Informations').'</h2>
<ul>
<li>'.__('Once the extension has been configured and your links have been created, you can place one of the cinecturlink widgets in the sidebar.').'</li>
<li>'.sprintf(__('In order to open links in new window you can use plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/externalLinks">External Links</a>').'</li>
<li>'.sprintf(__('In order to change URL of public page you can use plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/myUrlHandlers">My URL handlers</a>').'</li>
<li>'.sprintf(__('You can add public pages of cinecturlink to the plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/sitemaps">sitemaps</a>').'</li>
<li>'.sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/rateIt">Rate it</a>').'</li>
<li>'.sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'),'<a href="http://lab.dotclear.org/wiki/plugin/activityReport">Activity report</a>').'</li>
</ul>
</div>';

# List categories
echo '
<div class="multi-part" id="cats" title="'.__('Categories').'">';

if (isset($_GET['newcat'])) {
	echo '<p class="message">'.__('Category successfully created').'</p>';
}
if (isset($_GET['delcat'])) {
	echo '<p class="message">'.__('Categories successfully deleted').'</p>';
}
if (isset($_GET['ordercat'])) {
	echo '<p class="message">'.__('Categories successfully reordered').'</p>';
}

if ($categories->isEmpty())
{
	echo '<p>'.__('There is no category').'</p>';
}
else
{
	echo '
	<form method="post" action="plugin.php">
	<table class="maximal dragable">
	<thead><tr><th colspan="2">#</th><th>'.__('name').'</th><th>'.__('description').'</th></tr></thead>
	<tbody id="links-list-cat">';

	$i = 0;
	while($categories->fetch())
	{
		$i++;
		$id = $categories->cat_id;
		$title = html::escapeHTML($categories->cat_title);
		$desc = html::escapeHTML($categories->cat_desc);
		echo '
		<tr class="line" id="l_'.$id.'">
		<td class="minimal handle">'.form::field(array('catpos['.$id.']'),2,5,$i).'</td>
		<td class="minimal">'.form::checkbox(array('delcat[]'),$id).'</td>
		<td class="nowrap">'.$title.'</td>
		<td class="maximal">'.$desc.'</td>
		</tr>';
	}
	echo '
	</tbody>
	</table>
	<p class="form-note">'.__('Check to delete').'</p>
	<p>'.
	form::hidden('cats_order','').
	form::hidden(array('p'),'cinecturlink2').
	form::hidden(array('tab'),'cats').
	$core->formNonce().'
	<input type="submit" name="updcats" value="'.__('save').'" />
	</p>
	</form>';
}
echo '
</div>';

# Create category
echo '
<div class="multi-part" id="newcat" title="'.__('New category').'">
<form method="post" action="plugin.php">
<p><label class="classic">'.__('Title:').'<br />'.
form::field(array('new_cattitle'),60,64,$new_cattitle).'</label></p>
<p><label class="classic">'.__('Description:').'<br />'.
form::field(array('new_catdesc'),60,64,$new_catdesc).'</label></p>
<p>'.
form::hidden(array('p'),'cinecturlink2').
form::hidden(array('tab'),'newcat').
$core->formNonce().'
<input type="submit" name="newcat" value="'.__('save').'" />
</p>
</form>
</div>';

# List links
echo '
<div class="multi-part" id="links" title="'.__('Links').'">';

if (isset($_GET['newlink'])) {
	echo '<p class="message">'.__('Link successfully created').'</p>';
}
if (isset($_GET['dellink'])) {
	echo '<p class="message">'.__('Links successfully deleted').'</p>';
}
if (isset($_GET['updlink'])) {
	echo '<p class="message">'.__('Links successfully updated').'</p>';
}

if ($links->isEmpty())
{
	echo '<p>'.__('There is no link').'</p>';
}
else
{
	if (!$show_filters)
	{ 
	   echo 
	   dcPage::jsLoad('js/filter-controls.js').'
	   <p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
	}
	echo '
	<form action="'.$p_url.'&amp;tab=links" method="get" id="filters-form">
	<fieldset><legend>'.__('Filters').'</legend>
	<div class="three-cols">
	<div class="col">
	<label>'.__('Order by:').form::combo('sortby',$sortby_combo,$sortby).'</label> 
	</div>
	<div class="col">
	<label>'.__('Sort:').form::combo('order',$order_combo,$order).'</label>
	</div>
	<div class="col">
	<p>
	<label class="classic">'.
	form::field('nb',3,3,$nb_per_page).' '.__('Entries per page').'
	</label> 
	<input type="submit" value="'.__('filter').'" />'.
	form::hidden(array('p'),'cinecturlink2').
	form::hidden(array('tab'),'links').'
	</p>
	</div>
	</div>
	<br class="clear" />
	</fieldset>
	</form>
	<form action="plugin.php" method="post" id="form-actions">';

	$links_list->display($page,$nb_per_page,$pager_base_url);

	echo '
	<div class="two-cols">
	<p class="col checkboxes-helpers"></p>
	<p class="col right">'.__('Selected links action:').' '.
	form::combo(array('action'),$links_action_combo).'
	<input type="submit" value="'.__('ok').'" />'.
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	form::hidden(array('p'),'cinecturlink2').
	form::hidden(array('tab'),'links').
	$core->formNonce().'
	</p>
	</div>
	</form>';
}
echo '	
</div>';

# Change category for links
if (!empty($_POST['links']) && $_POST['action'] == 'changecat')
{
	echo '
	<div class="multi-part" id="changecatlinks" title="'.__('Edit links').'">
	<form method="post" action="plugin.php">
	<p>'.__('This changes category for all selected links.').'</p>';

	foreach($_POST['links'] as $link)
	{
		echo
		'<p><label class="classic">'.
		form::checkbox(array('links[]'),$link,1).' '.
		$C2->getLinks(array('link_id'=>$link))->f('link_title').
		'</label></p>';
	}

	echo '
	<p>'.__('Select a category:').' '.
	form::combo(array('upd_category'),$categories_combo,'').' 
	<input type="submit" name="updlinkcat" value="ok" />'.
	form::hidden(array('p'),'cinecturlink2').
	form::hidden(array('tab'),'links').
	$core->formNonce().'
	</p>
	</form>
	</div>';
}

# Change note for links
if (!empty($_POST['links']) && $_POST['action'] == 'changenote')
{
	echo '
	<div class="multi-part" id="changenotelinks" title="'.__('Edit links').'">
	<form method="post" action="plugin.php">
	<p>'.__('This changes my rating for all selected links.').'</p>';

	foreach($_POST['links'] as $link)
	{
		echo
		'<p><label class="classic">'.
		form::checkbox(array('links[]'),$link,1).' '.
		$C2->getLinks(array('link_id'=>$link))->f('link_title').
		'</label></p>';
	}

	echo '
	<p>'.__('Select a rating:').' '.
	form::combo(array('upd_note'),$notes_combo,10).' 
	<input type="submit" name="updlinknote" value="ok" />'.
	form::hidden(array('p'),'cinecturlink2').
	form::hidden(array('tab'),'links').
	$core->formNonce().'
	</p>
	</form>
	</div>';
}

# Create/edit link
$title = __('New link');

if (null !== $upd_link_id)
{
	$upd_rs = $C2->getLinks(array('link_id'=>$upd_link_id));
	$upd_link_id = null;
	if (!$upd_rs->isEmpty())
	{
		$new_title = $upd_rs->link_title;
		$new_desc = $upd_rs->link_desc;
		$new_author = $upd_rs->link_author;
		$new_url = $upd_rs->link_url;
		$new_category = $upd_rs->cat_id;
		$new_lang = $upd_rs->link_lang;
		$new_image = $upd_rs->link_img;
		$new_note = $upd_rs->link_note;
		$upd_link_id = $upd_rs->link_id;
		$title = __('Edit link');
	}
}

echo '
<div class="multi-part" id="newlink" title="'.$title.'">
<form id="newlinkform" method="post" action="plugin.php">
<table>
<tr><td>'.__('Title:').'</td><td>'.form::field('new_title',60,255,$new_title).'</td></tr>
<tr><td>'.__('Description:').'</td><td>'.form::field(array('new_desc'),60,255,$new_desc).'</td></tr>
<tr><td>'.__('Author:').'</td><td>'.form::field(array('new_author'),60,255,$new_author).'</td></tr>
<tr><td>'.__('URL:').'</td><td>'.form::field(array('new_url'),60,255,$new_url).' <a class="button" href="http://google.com" id="newlinksearch">'. __('Search with Google').'</a></td></tr>
<tr><td>'.__('Category:').'</td><td>'.form::combo(array('new_category'),$categories_combo,$new_category).'</td></tr>
<tr><td>'.__('Lang:').'</td><td>'.form::combo(array('new_lang'),$langs_combo,$new_lang).'</td></tr>
<tr><td>'.__('Image URL:').'</td><td>'.form::field('new_image',60,255,$new_image).' <a class="button" href="http://amazon.com" id="newimagesearch">'. __('Search with Amazon').'</a></td></tr>
<tr><td>'.__('or select from repository:').'</td><td>'.form::combo('newimageselect',$medias_combo).'</td></tr>
<tr><td>'.__('My rating:').'</td><td>'.form::combo('new_note',$notes_combo,$new_note).'</td></tr>
</td></tr>
</table>
<p>'.
form::hidden(array('link_id'),$upd_link_id).
form::hidden(array('p'),'cinecturlink2').
form::hidden(array('tab'),'newlink').
$core->formNonce().'
<input type="submit" name="newlink" value="'.__('save').'" />
</p>
</form>
</div>';

# Footer
echo 
dcPage::helpBlock('cinecturlink2').'
<hr class="clear"/>
<p class="right">
cinecturlink2 - '.$core->plugins->moduleInfo('cinecturlink2','version').'&nbsp;
<img alt="'.__('cinecturlink2').'" src="index.php?pf=cinecturlink2/icon.png" />
</p></body></html>';
?>