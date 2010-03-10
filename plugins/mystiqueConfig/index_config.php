<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# This file is hugely inspired from blowupConfig admin page
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/../mystiqueConfig/lib/class.mystique.config.php';

$sharethis_list = array(
	"twitter"=>"Twitter",
	"digg"=>"Digg",
	"facebook"=>"FaceBook",
	"delicious"=>"del.icio.us",
	"stumbleupon"=>"StumbleUpon",
	"google_bookmark"=>"Google Bookmark",
	"linkedin"=>"LinkedIn",
	"yahoobuzz"=>"Yahoo Buzz",
	"technocrati"=>"Technocrati"
);


if (!empty($_POST))
{
	try
	{
		$twitter_account = html::escapeHTML($_POST['twitter_account']);
		$twitter_enabled=isset($_POST['twitter_enabled'])?"1":"0";
		$sharethis_enabled=isset($_POST['sharethis_enabled'])?"1":"0";
		$sharethis_modules = array();
		foreach ($sharethis_list as $k=>$v) {
			$sharethis_modules[$k] = isset($_POST['sharethis_module_'.$k])?"1":"0";
		}
		
		$core->blog->settings->addNamespace('mystique');
		$core->blog->settings->mystique->put('mystique_twitter_enabled',$twitter_enabled);
		$core->blog->settings->mystique->put('mystique_twitter_account',$twitter_account);
		$core->blog->settings->mystique->put('mystique_sharethis_enabled',$sharethis_enabled);
		$core->blog->settings->mystique->put('mystique_sharethis_modules',serialize($sharethis_modules));
		$core->blog->triggerBlog();
		http::redirect($p_url.'&m=config&upd=1');
		exit;
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>


?>
<html>
<head>
  <title><?php echo __('Mystique configuration'); ?></title>
  <?php echo dcPage::jsPageTabs("settings"); ?>
  <link rel='stylesheet' href='index.php?pf=mystiqueConfig/css/admin.css' type='text/css' media='all' />
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="blog_theme.php">'.__('Blog aspect').'</a> &rsaquo; '.__('Mystique configuration').'</h2>'.
'<p><a class="back" href="blog_theme.php">'.__('back').'</a></p>'.

'<p><a href="plugin.php?p=mystiqueConfig" class="multi-part">'.__('Layout').'</a></p>';


$twitter_enabled=$core->blog->settings->mystique->mystique_twitter_enabled;
$twitter_account=$core->blog->settings->mystique->mystique_twitter_account;
$sharethis_enabled=$core->blog->settings->mystique->mystique_sharethis_enabled;
$sharethis_modules = unserialize($core->blog->settings->mystique->mystique_sharethis_modules);

if (!is_array($sharethis_modules)) {
	$sharethis_modules = array();
	foreach($sharethis_list as $k => $v) {
		$sharethis_modules[$k]=0;
	}
}


echo '<div id="settings" class="multi-part" title="'.__('Settings').'">'.
	'<form id="theme_config" action="'.$p_url.'" method="post" enctype="multipart/form-data">';
echo '<fieldset><legend>'.__('Twitter').'</legend>'.
	'<p>'.form::checkbox('twitter_enabled',1,$twitter_enabled).'&nbsp;<label class="classic">'.__('Enable twitter top link').' '.
	'</label></p>'.
	'<p class="field"><label>'.__('Twitter account:').' '.
	form::field('twitter_account',20,20,$twitter_account).'</label></p>'.
	'</fieldset>';
echo '<fieldset><legend>'.__('ShareThis Options').'</legend>'.
	'<p>'.form::checkbox('sharethis_enabled',1,$sharethis_enabled).'&nbsp;<label class="classic">'.__('Enable ShareThis').
	'</label></p>';
foreach ($sharethis_list as $k=>$v) {
	echo '<p style="margin-left:3em;">'.form::checkbox('sharethis_module_'.$k,1,$sharethis_modules[$k]).'&nbsp;<label class="classic">'.sprintf(__('Enable ShareThis - %s'),$v).' '.
	'</label></p>';
}
	echo '</fieldset>'.
	'<p class="clear"><input type="submit" value="'.__('save').'" />'.
	$core->formNonce().
	form::hidden('p','mystiqueConfig').
	form::hidden('m','config').
	'</p>'.
	'</form></div>';
?>

</body>
</html>
