<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

require_once dirname(__FILE__).'/inc/lib.miniurl.list.php';

# List filters and controls  and params
$show_filters = false;
$urltype = !empty($_GET['urltype']) ? $_GET['urltype'] : '';
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'miniurl_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

$combo_action = array(
	__('delete short link') => 'miniurl_delete',
	__('reset short link counter') => 'miniurl_counter_reset'
);

$urltype_combo = array(
	__('All') => '',
	__('Normal') => 'miniurl',
	__('Custom') => 'customurl'
);

$sortby_combo = array(
__('Date') => 'miniurl_dt',
__('Long link') => 'miniurl_str',
__('Short link') => 'miniurl_id',
__('Follow') => 'miniurl_counter',
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

if ($sortby != '' && in_array($sortby,$sortby_combo)) {

	if ($urltype != '' && in_array($urltype,$urltype_combo))
		$params['miniurl_type'] = $urltype;

	if ($order != '' && in_array($order,$order_combo))
		$params['order'] = $sortby.' '.$order;

	if ($sortby != 'miniurl_dt' || $order != 'desc' || $urltype != '')
		$show_filters = true;
}

$msg = $str = '';
$custom = null;
$type = 'miniurl';
$tab = !$core->blog->settings->miniurl_active ? 'adm' : 'new';
$tab = isset($_REQUEST['t']) ? $_REQUEST['t'] : $tab;

# mini url object 
$autoshorturl = (boolean) $core->blog->settings->miniurl_core_autoshorturl;
$protocols = $core->blog->settings->miniurl_protocols;
$protocols = !$protocols ? '' : explode(',',$protocols);
$onlyblog = (boolean) $core->blog->settings->miniurl_only_blog;

$O = new dcMiniUrl($core,$autoshorturl,$protocols,$onlyblog);

# Save settings
if (!empty($_POST['settings'])) {
	try {
		$core->blog->settings->setNamespace('miniurl');
		$core->blog->settings->put('miniurl_active',
			isset($_POST['miniurl_active']),
			'boolean',
			'Enabled miniurl plugin',
			true,false);
		$core->blog->settings->put('miniurl_public_active',
			isset($_POST['miniurl_public_active']),
			'string',
			'Enabled miniurl public page',
			true,false);
		$core->blog->settings->put('miniurl_public_autoshorturl',
			isset($_POST['miniurl_public_autoshorturl']),
			'string',
			'Enabled miniurl auto shorturl on public urls',
			true,false);
		$core->blog->settings->put('miniurl_core_autoshorturl',
			isset($_POST['miniurl_core_autoshorturl']),
			'string',
			'Enabled miniurl auto shorturl on contents',
			true,false);
		$core->blog->settings->put('miniurl_only_blog',
			isset($_POST['miniurl_only_blog']),
			'boolean',
			'Limited miniurl shorturl to current blog',
			true,false);
		$core->blog->settings->put('miniurl_protocols',
			$_POST['miniurl_protocols'],
			'string',
			'Allowed miniurl protocols',
			true,false);

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=adm&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Create a new link
if (isset($_POST['str'])) {

	try {
		$str = trim($core->con->escape($_POST['str']));

		if (empty($str))
			throw new Exception(__('Nothing to shorten'));

		if (!$O->isLonger($str))
			throw new Exception(__('This link is too short'));

		if ($O->isMini($str))
			throw new Exception(__('This link is already a short link'));

		if ($core->blog->settings->miniurl_only_blog && !$O->isBlog($str))
			throw new Exception(__('This link is not a blog link'));

		if (!$O->isAllowed($str))
			throw new Exception(__('This type of link is not allowed'));

		if (isset($_POST['custom']) && !empty($_POST['custom'])) {

			$id = text::tidyURL($_POST['custom']);
			$exists = $O->str($type,$id);
			if (-1 != $exists)
				throw new Exception(__('This short link is already taken'));

			$type = 'customurl';
			$exists = $O->str($type,$id);
			if (-1 != $exists)
				throw new Exception(__('This custom short link is already taken'));

			$custom = $id;
		}

		$id = $O->create($type,$str,$custom);

		if ($id != -1) {
			$url = $core->blog->url.$core->url->getBase('miniUrl').'/'.$id;
			$msg = sprintf(__('Short link for %s is %s'),html::escapeHTML($str),'<a href="'.$url.'">'.$url.'</a>');
		}
		else
			throw new Exception(__('Failed to create short link'));
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
		$tab = 'new';
	}
}

# Delete  short URLs
if (isset($_POST['action']) && $_POST['action'] == 'miniurl_delete') {

	try {
		foreach($_POST['entries'] as $k => $id) {
			$O->delete($_POST['urltypes'][$k],$id);
		}

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=list&urltype='.$urltype.'&sortby='.$sortby.'&order='.$order.'&nb='.$nb_per_page.'&page='.$page.'&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Reset short URLs  counters
if (isset($_POST['action']) && $_POST['action'] == 'miniurl_counter_reset') {

	try {
		foreach($_POST['entries'] as $k => $id) {
			$O->counter($_POST['urltypes'][$k],$id,'reset');
		}

		$core->blog->triggerBlog();
		http::redirect($p_url.'&t=list&urltype='.$urltype.'&sortby='.$sortby.'&order='.$order.'&nb='.$nb_per_page.'&page='.$page.'&done=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$pager_base_url = $p_url.
	'&amp;t=list'.
	'&amp;urltype='.$urltype.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

$header = 
	dcPage::jsToolBar().
	dcPage::jsLoad('js/_posts_list.js').
	dcPage::jsPageTabs($tab);

if (!empty($msg))
	$msg = '<p class="message">'.$msg.'</p>';

try {
	$miniurls = $O->getMiniUrls($params);
	$counter = $O->getMiniUrls($params,true);
	$miniurls_list = new adminListMiniUrl($core,$miniurls,$counter->f(0),$pager_base_url);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

?>
<html>
 <head>
  <title><?php echo __('Links shortener'); ?></title>
  <?php echo $header; ?>
 </head>
 <body>
  <h2><?php echo html::escapeHTML($core->blog->name).
   ' &rsaquo; '.__('Links shortener'); ?></h2>
  <?php echo $msg; ?>

  <div class="multi-part" id="new" title="<?php echo __('Shorten link'); ?>">
   <form action="plugin.php" method="post">

    <p class="classic"><label for="str">
     <?php echo __('Long link:'); ?></label>
     <?php echo form::field('str',100,255,''); ?>
    </p>

    <p class="classic"><label for="custom">
     <?php echo __('Custom short link:'); ?> *</label>
     <?php echo form::field('custom',50,32,''); ?>
    </p>

    <p class="form-note">* 
     <?php echo __('Only for custom short link.'); ?>
    </p>

    <p class="classic">
     <input type="submit" name="submit" id="submit" 
      value="<?php echo __('Create short link'); ?>" />
     <?php echo 
      form::hidden(array('p'),'dcMiniUrl').
      form::hidden(array('t'),'list').
      $core->formNonce();
	 ?>
    </p>
   </form>
  </div>

  <div class="multi-part" id="list" title="<?php echo __('List'); ?>">

<?php if (!$show_filters) { 
   echo dcPage::jsLoad('js/filter-controls.js'); ?>
   <p>
    <a id="filter-control" class="form-control" href="#">
    <?php echo __('Filters'); ?></a>
   </p>
<?php } ?>

   <form action="<?php echo $p_url; ?>&amp;t=list" method="get" id="filters-form">
    <fieldset><legend><?php echo __('Filters'); ?></legend>
     <div class="three-cols">
      <div class="col">
       <label>
        <?php echo __('Type:').form::combo('urltype',$urltype_combo,$urltype); ?>
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
       <p>
        <label class="classic">
         <?php echo form::field('nb',3,3,$nb_per_page).' '.__('Entries per page'); ?>
        </label> 
        <input type="submit" value="<?php echo __('filter'); ?>" />
        <?php echo
		 form::hidden(array('p'),'dcMiniUrl').
	     form::hidden(array('t'),'list');
		?>
        </p>
      </div>
     </div>
     <br class="clear" />
    </fieldset>
   </form>
   <form action="plugin.php" method="post" id="form-actions">
    <?php $miniurls_list->display($page,$nb_per_page,$pager_base_url); ?>

	<div class="two-cols">
     <p class="col checkboxes-helpers"></p>
     <p class="col right">
      <?php 
       echo __('Selected links action:').' '.
       form::combo(array('action'),$combo_action).
       '<input type="submit" value="'.__('ok').'" />'.
       form::hidden(array('urltype'),$urltype).
       form::hidden(array('sortby'),$sortby).
       form::hidden(array('order'),$order).
       form::hidden(array('page'),$page).
       form::hidden(array('nb'),$nb_per_page).
       form::hidden(array('p'),'dcMiniUrl').
       form::hidden(array('t'),'list').
       $core->formNonce();
      ?>
     </p>
    </div>
   </form>
  </div>

  <div class="multi-part" id="adm" title="<?php echo __('Settings'); ?>">
   <form method="post" action="plugin.php">

    <h2><?php echo __('Settings'); ?></h2>

    <p><label class="classic"><?php echo
	 form::checkbox(array('miniurl_active'),'1',
	  $core->blog->settings->miniurl_active).' '.
     __('Enable plugin'); ?>
	</label></p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('miniurl_public_active'),'1',
	  $core->blog->settings->miniurl_public_active).' '.
     __('Enable public page'); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Allowed users to shortcut their links on public side'); ?>
    </p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('miniurl_public_autoshorturl'),'1',
	  $core->blog->settings->miniurl_public_autoshorturl).' '.
     __('Enable auto shorturl on public urls'); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Shortcut links automatically when using template value like "EntryMiniUrl"'); ?>
    </p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('miniurl_core_autoshorturl'),'1',
	  $core->blog->settings->miniurl_core_autoshorturl).' '.
     __('Enable auto shorturl on contents'); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Shortcut links automatically found in contents when creating entry or comment'); ?>
    </p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('miniurl_only_blog'),'1',
	  $core->blog->settings->miniurl_only_blog).' '.
     __('Limit shorturl to current blog'); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Only link started with this blog url could be shortened'); ?>
    </p>

    <p><label class="classic">
	 <?php echo __('Allowed protocols'); ?><br />
     <?php echo form::field(array('miniurl_protocols'),50,255,
	  $core->blog->settings->miniurl_protocols); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Use comma seperated list like: "http:,https:,ftp:"'); ?>
    </p>

    <p>
     <input type="submit" name="settings" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'dcMiniUrl').
      form::hidden(array('t'),'adm').
      $core->formNonce();
     ?>
	</p>
   </form>

<?php if (preg_match('/index\.php/',$core->blog->url)) { ?>

   <h2><?php echo __('Note this'); ?></h2>
   <?php if ($core->auth->isSuperAdmin()) { ?>
    <p>
	 <?php echo __('If you use this plugin then uninstall it, note that you loose all short links'); ?>
    </p>
   <?php } ?>
   <p>
    <?php echo __("We recommand that you use a rewrite engine in order to remove 'index.php' from your blog's URLs"); ?><br />
    <a href="http://fr.dotclear.org/documentation/2.0/usage/blog-parameters">
     <?php echo __('You can find more about this on the Dotclear Documentation'); ?>
	</a>
   </p>
<?php } ?>

  </div>

  <hr class="clear"/>
  <p class="right">
   dcMiniUrl - <?php echo $core->plugins->moduleInfo('dcMiniUrl','version'); ?>&nbsp;
   <img alt="dcMiniUrl" src="index.php?pf=dcMiniUrl/icon.png" />
  </p>
 </body>
</html>