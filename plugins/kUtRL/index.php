<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Settings
$s =& $core->blog->settings;
$_active = (boolean) $s->kutrl_active;
$_admin_service = (string) $s->kutrl_admin_service;
$_tpl_service = (string) $s->kutrl_tpl_service;
$_wiki_service = (string) $s->kutrl_wiki_service;
$_limit_to_blog = (boolean) $s->kutrl_limit_to_blog;

# Vars
$img_green = '<img src="images/check-on.png" alt="ok" />';
$img_red = '<img src="images/check-off.png" alt="fail" />';
$default_tab = !$_active ? 'settings' : 'new';
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : $default_tab;
$msg = '';

# List filters and controls and params
require_once dirname(__FILE__).'/inc/lib.kutrl.index.list.php';

$show_filters = false;
$urlsrv = !empty($_GET['urlsrv']) ? $_GET['urlsrv'] : '';
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'kut_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

# Combos
$combo_action = array(
	__('delete short link') => 'kutrl_list_delete'
);

$sortby_combo = array(
	__('Date') => 'kut_dt',
	__('Long link') => 'kut_url',
	__('Short link') => 'kut_hash'
);

$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$services_combo = array();
foreach($core->kutrlServices as $service_id => $service)
{
	$o = new $service($core);
	$services_combo[__($o->name)] = $o->id;
}
$ext_services_combo = array_merge(array(__('disabled')=>''),$services_combo);
$lst_services_combo = array_merge(array('-'=>''),$services_combo);

# Params for list
$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

if ($sortby != '' && in_array($sortby,$sortby_combo))
{
	if ($urlsrv != '' && in_array($urlsrv,$lst_services_combo))
		$params['kut_type'] = $urlsrv;

	if ($order != '' && in_array($order,$order_combo))
		$params['order'] = $sortby.' '.$order;

	if ($sortby != 'kut_dt' || $order != 'desc' || $urlsrv != '')
		$show_filters = true;
}

$pager_base_url = 
	$p_url.
	'&amp;tab=list'.
	'&amp;urlsrv='.$urlsrv.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

# Delete links from list
if (isset($_POST['action']) && $_POST['action'] == 'kutrl_list_delete')
{
	try
	{
		$l = new kutrlLog($core);
		foreach($_POST['entries'] as $k => $id)
		{
			$rs = $l->getLogs(array('kut_id'=>$id));
			if ($rs->isEmpty()) continue;

			if(!isset($core->kutrlServices[$rs->kut_type])) continue;

			$o = new $core->kutrlServices[$rs->kut_type]($core);
			$o->remove($rs->kut_url);
		}

		$core->blog->triggerBlog();
		http::redirect($p_url.'&tab=list&urlsrv='.$urlsrv.'&sortby='.$sortby.'&order='.$order.'&nb='.$nb_per_page.'&page='.$page.'&updlist=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Save settings
if (!empty($_POST['settings']))
{
	try
	{
		$_active = isset($_POST['_active']);
		$_admin_service = $_POST['_admin_service'];
		$_tpl_service = $_POST['_tpl_service'];
		$_wiki_service = $_POST['_wiki_service'];
		$_limit_to_blog = isset($_POST['_limit_to_blog']);

		$s->setNamespace('kUtRL');
		$s->put('kutrl_active',$_active);
		$s->put('kutrl_admin_service',$_admin_service);
		$s->put('kutrl_tpl_service',$_tpl_service);
		$s->put('kutrl_wiki_service',$_wiki_service);
		$s->put('kutrl_limit_to_blog',$_limit_to_blog);
		$s->setNamespace('system');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&tab=settings&updsettings=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Save services settings
if (!empty($_POST['services']))
{
	try
	{
		foreach($core->kutrlServices as $service_id => $service)
		{
			$o = new $service($core);
			$o->saveSettings();
		}
		$core->blog->triggerBlog();
		http::redirect($p_url.'&tab=services&updservices=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Create a new link
if (isset($_POST['str'])) {

	try {
		if (!isset($core->kutrlServices[$_admin_service]))
			throw new Exception('Unknow service');

		$kut = new $core->kutrlServices[$_admin_service]($core,$_limit_to_blog);

		$url = trim($core->con->escape($_POST['str']));
		$hash = empty($_POST['custom']) ? null : $_POST['custom'];

		if (empty($url))
			throw new Exception(__('There is nothing to shorten.'));

		if (!$kut->testService())
			throw new Exception(__('Service is not well configured.'));

		if (null !== $hash && !$kut->allow_customized_hash)
			throw new Exception(__('This service does not allowed custom hash.'));

		if (!$kut->isValidUrl($url))
			throw new Exception(__('This link is not a valid URL.'));

		if (!$kut->isLongerUrl($url))
			throw new Exception(__('This link is too short.'));

		if (!$kut->isProtocolUrl($url))
			throw new Exception(__('This type of link is not allowed.'));

		if ($_limit_to_blog && !$kut->isBlogUrl($url))
			throw new Exception(__('Short links are limited to this blog URL.'));

		if ($kut->isServiceUrl($url))
			throw new Exception(__('This link is already a short link.'));

		if (false !== ($rs = $kut->isKnowUrl($url)))
		{
			$url = $rs->url;
			$new_url = $kut->url_base.$rs->hash;
			$msg = sprintf(__('Short link for %s is %s'),html::escapeHTML($url),'<a href="'.$new_url.'">'.$new_url.'</a>');
		}
		else
		{
			if (false === ($rs = $kut->hash($url,$hash)))
			{
				throw new Exception(__('Failed to create short link.'));
			}
			else
			{
				$url = $rs->url;
				$new_url = $kut->url_base.$rs->hash;
				$msg = sprintf(__('Short link for %s is %s'),html::escapeHTML($url),'<a href="'.$new_url.'">'.$new_url.'</a>');
			}
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
		$tab = 'new';
	}
}

try
{
	$log = new kutrlLog($core);

	$list_all = $log->getLogs($params);
	$list_counter = $log->getLogs($params,true)->f(0);
	$list_current = new kutrlIndexList($core,$list_all,$list_counter,$pager_base_url);
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (!empty($msg)) $msg = '<p class="message">'.$msg.'</p>';
$title = 'kUtRL, '.__('Links shortener');
?>
<html>
 <head>
  <title><?php echo __('kUtRL'); ?></title>
  <?php echo 
	dcPage::jsToolBar().
	dcPage::jsLoad('js/_posts_list.js').
	dcPage::jsPageTabs($default_tab);
  ?>
  <script type="text/javascript">
    $(function() {
	<?php
	foreach($core->kutrlServices as $service_id => $service)
	{
		echo "\$('#".$service_id."-options-title').toggleWithLegend(\$('#".$service_id."-options-content'),{cookie:'dcx_kutrl_admin_".$service_id."_options'});\n";
	}
	?>
    });
  </script>
  <style type="text/css">
  .titleKutrl {
	margin: -20px;
	text-align:center;
  }
  .titleKutrl a {
	border:none;
	text-decoration: none;
  }
  </style>
 </head>
 <body>
 <h2><?php echo $title; ?></h2>
  <?php echo $msg; ?>

  <div class="multi-part" id="new" title="<?php echo __('Shorten link'); ?>">
  
  <?php if (!isset($core->kutrlServices[$_admin_service])) { ?>
  
	<p><?php echo __('You must set an admin service.'); ?></p>
	
  <?php } else { $kut = new $core->kutrlServices[$_admin_service]($core,$_limit_to_blog); ?>

   <form action="plugin.php" method="post">

    <p class="classic"><label for="str">
     <?php echo __('Long link:'); ?></label>
     <?php echo form::field('str',100,255,''); ?>
    </p>

	<?php  if ($kut->allow_customized_hash) { ?>

    <p class="classic"><label for="custom">
     <?php echo __('Custom short link:'); ?> *</label>
     <?php echo form::field('custom',50,32,''); ?>
    </p>
    <p class="form-note">* 
     <?php echo __('Only if you want a custom short link.'); ?>
    </p>

	<?php } ?>

    <p class="classic">
     <input type="submit" name="submit" id="submit" 
      value="<?php echo __('Create short link'); ?>" />
     <?php echo 
      form::hidden(array('p'),'kUtRL').
      form::hidden(array('t'),'list').
      $core->formNonce();
	 ?>
    </p>
   </form>
   
  <?php } ?>
  </div>

  <div class="multi-part" id="settings" title="<?php echo __('Settings'); ?>">
   <form method="post" action="plugin.php">

   <?php 
   if (isset($_GET['updsettings']))
	echo '<p class="message">'.__('Configuration successfully updated').'</p>';
   ?>

    <p><label class="classic"><?php echo
	 form::checkbox(array('_active'),'1',$_active).' '.
     __('Enable plugin'); ?>
	</label></p>

    <p><label class="classic"><?php echo
	 form::checkbox(array('_limit_to_blog'),'1',$_limit_to_blog).' '.
     __('Limit short link to current blog'); ?>
	</label></p>
    <p class="form-note">
      <?php echo __('Only link started with this blog URL could be shortened.'); ?>
    </p>

    <p><label class="classic">
	 <?php 
	 echo __('Administration:').'<br />';
     echo form::combo(array('_admin_service'),$services_combo,$_admin_service); ?>
	</label>
	</p>
    <p class="form-note">
      <?php echo __('Service to use in this admin page.'); ?>
    </p>

    <p><label class="classic">
	 <?php 
	 echo __('Templates:').'<br />';
     echo form::combo(array('_tpl_service'),$ext_services_combo,$_tpl_service); ?>
	</label>
	</p>
    <p class="form-note">
      <?php echo __('Shorten links automatically when using template value like "EntryKutrl".'); ?>
    </p>

    <p><label class="classic">
	 <?php 
	 echo __('Contents:').'<br />';
     echo form::combo(array('_wiki_service'),$ext_services_combo,$_wiki_service); ?>
	</label>
	</p>
    <p class="form-note">
      <?php echo __('Shorten links automatically found in contents using wiki synthax.'); ?>
    </p>

    <p>
     <input type="submit" name="settings" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'kUtRL').
      form::hidden(array('tab'),'settings').
      $core->formNonce();
     ?>
	</p>
   </form>
   
  </div>

  <div class="multi-part" id="services" title="<?php echo __('Services'); ?>">
   <form method="post" action="plugin.php">
   
   <?php 
   if (isset($_GET['updservices']))
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
   ?>

	<?php
	foreach($core->kutrlServices as $service_id => $service)
	{
		$o = new $service($core);
		
		echo '<h2 id="'.$service_id.'-options-title">'.$o->name.'</h2>';

		if (isset($_GET['updservices']))
		{
			echo '<p><em>'.(
			$o->testService() ?
				$img_green.' '.sprintf(__('%s API is well configured and runing.'),$o->name) :
				$img_red.' '.sprintf(__('%s API is not well configured or not online.'),$o->name)
			).'</em></p>';
		}
		echo '<div id="'.$service_id.'-options-content">';

		if (!empty($o->home))
		{ 
			echo '<p><a title="'.__('homepage').'" href="'.$o->home.'">'.sprintf(__('Learn more about %s.'),$o->name).'</a></p>';
		}

		$o->settingsForm();

		echo '</div>';
	}
	?>
   
    <p>
     <input type="submit" name="services" value="<?php echo __('Save'); ?>" />
     <?php echo 
      form::hidden(array('p'),'kUtRL').
      form::hidden(array('tab'),'services').
      $core->formNonce();
     ?>
	</p>
   </form>
   </div>

  <div class="multi-part" id="list" title="<?php echo __('Know links'); ?>">

   <?php 
   if (isset($_GET['updlist']))
	echo '<p class="message">'.__('List successfully updated.').'</p>';
   ?>

<?php if (!$show_filters) { 
   echo dcPage::jsLoad('js/filter-controls.js'); ?>
   <p>
    <a id="filter-control" class="form-control" href="#">
    <?php echo __('Filters'); ?></a>
   </p>
<?php } ?>

   <form action="<?php echo $p_url; ?>&amp;tab=list" method="get" id="filters-form">
    <fieldset><legend><?php echo __('Filters'); ?></legend>
     <div class="three-cols">
      <div class="col">
       <label>
        <?php echo __('Service:').form::combo('urlsrv',$lst_services_combo,$urlsrv); ?>
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
		 form::hidden(array('p'),'kUtRL').
	     form::hidden(array('tab'),'list');
		?>
        </p>
      </div>
     </div>
     <br class="clear" />
    </fieldset>
   </form>
   <form action="plugin.php" method="post" id="form-actions">
    <?php $list_current->display($page,$nb_per_page,$pager_base_url); ?>

	<div class="two-cols">
     <p class="col checkboxes-helpers"></p>
     <p class="col right">
      <?php 
       echo __('Selected links action:').' '.
       form::combo(array('action'),$combo_action).
       '<input type="submit" value="'.__('ok').'" />'.
       form::hidden(array('urlsrv'),$urlsrv).
       form::hidden(array('sortby'),$sortby).
       form::hidden(array('order'),$order).
       form::hidden(array('page'),$page).
       form::hidden(array('nb'),$nb_per_page).
       form::hidden(array('p'),'kUtRL').
       form::hidden(array('tab'),'list').
       $core->formNonce();
      ?>
     </p>
    </div>
   </form>
  </div>
  
  <?php echo dcPage::helpBlock('kUtRL'); ?>

  <hr class="clear"/>
  <p class="right">
   kUtRL - <?php echo $core->plugins->moduleInfo('kUtRL','version'); ?>&nbsp;
   <img alt="kUtRL" src="index.php?pf=kUtRL/icon.png" />
  </p>
 <h2 class="titleKutrl"><a title="<?php echo $title; ?> | http://kutrl.fr" href="http://kutrl.fr"><img alt="<?php echo $title; ?> | http://kutrl.fr" src="index.php?pf=kUtRL/inc/img/kutrl_logo.png" /></a></h2>
 </body>
</html>