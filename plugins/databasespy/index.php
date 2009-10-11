<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of databasespy, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
dcPage::checkSuper();

$p_url = 'plugin.php?p=databasespy';

/** Combos arrays **/
require_once dirname(__FILE__).'/inc/lib.combos.php';

/** Prepare some vars **/

# Load et create dbSpy object
$spy = new dbSpy($core,$combo_settings_install);

# Load dbSpy settings
$settings = $spy->getSettings();

# Parse some $_GET and $_POST
$_m = (!empty($_GET['m']))?$_GET['m']:'summary';
$_t = (!empty($_GET['t']))?$_GET['t']:'';
$_a = (!empty($_POST['action']))?$_POST['action']:'';

# Create page
$P = new libDbSpyPage($settings);

# Page menus
foreach($combo_menu AS $k => $v)
{
	$P->menu($v,$k,$p_url.'&amp;m='.$v);
}

/** Post forms actions **/
# Create table object to work on
if (isset($_POST['table']))
{
	$_T_ = $spy->table($_POST['table']);
}
require_once dirname(__FILE__).'/inc/lib.forms.php';

# Show database request for Post actions
if (isset($_T_) && $settings['show_request'])
{
	$sql_req = $_T_->getSqlRequest();
	foreach($sql_req AS $req)
	{
		$P->setSql($req);
	}
}

/** Prepare admin page **/

# Show error and warning
if (isset($error)) { $P->error($error); }
if (isset($msg)) { $P->warning($msg); }

# Create table object to work on
if (!empty($_t)) { $_T_ = $spy->table($_t); }

/** Construct admin page **/
require_once dirname(__FILE__).'/inc/lib.pages.php';

# Show database request page actions
if (!empty($_t) && $settings['show_request'])
{
	$sql_req = $_T_->getSqlRequest();
	foreach($sql_req AS $req)
	{
		$P->setSql($req);
	}
}

/** Display Result **/
?>
<html>
<head>
 <title><?php echo __('Database Spy'); ?></title>
 <?php echo 
	dcPage::jsToolBar().
	dcPage::jsToolMan().
	dcPage::jsPageTabs('tab_'.$tab);
 ?>
<script type="text/javascript">
$(function() { 
	$(".checkboxes-helpers").each(function() { 
		dotclear.checkboxesHelpers(this); 
	}); 
	dotclear.postsActionsHelper(); 
}); 
</script>
<link rel="stylesheet" type="text/css" href="index.php?pf=databasespy/style.css" />
</head>
<body>
<?php  echo $P->get($_m); ?>
<hr class="clear" />
<?php echo dcPage::helpBlock('databasespy'); ?>
<p class="right">
databasespy - <?php echo $core->plugins->moduleInfo('databasespy','version'); ?> 
<img alt="databasespy" src="index.php?pf=databasespy/icon.png" />
</p>
</body>
</html>