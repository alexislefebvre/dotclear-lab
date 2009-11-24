<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$can_install = $core->auth->isSuperAdmin();
$themes_dir = path::fullFromRoot($core->blog->settings->gallery->gallery_themes_path,DC_ROOT);
$is_writable = is_dir($themes_dir) && is_writable($themes_dir);
if (!$can_install)
	return;

require dirname(__FILE__)."/class.dc.gallery.themeadapter.php";

$core->gallery = new dcGallery($core);
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);

function installGalTheme($zip_file)
{
	$zip = new fileUnzip($zip_file);
	$zip->getList(false,'#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');
	
	$zip_root_dir = $zip->getRootDir();
	$define = '';
	if ($zip_root_dir == false || $zip_root_dir == 'gal_simple' || substr($zip_root_dir,0,4) != 'gal_') {
		throw new Exception (__('Theme is invalid'));
	} else {
		$target = dirname($zip_file);
		$destination = $target.'/'.$zip_root_dir;
	}
	
	if ($zip->isEmpty()) {
		unlink($zip_file);
		throw new Exception(__('Empty module zip file.'));
	}
	
	$ret_code = 1;
	
	if (is_dir($destination))
	{
		throw new Exception(sprintf(__('Theme %s already exists, delete it first.'),
			html::escapeHTML(substr($zip_root_dir,4))));
	}
	$zip->unzipAll($target);
	unlink($zip_file);
	return $ret_code;
}

if (!empty($_POST['adapt_theme'])) {
	$dc_theme = (!empty($_POST['dctheme']))?$_POST['dctheme']:"";
	$overwrite = (isset($_POST['themeoverwrite']))?true:false;
	$themeadapter = new dcGalleryThemeAdapter($core,$dc_theme);
	if($themeadapter->generateAllTemplates($overwrite))
		http::redirect('plugin.php?p=gallery&m=maintenance&adapted=1');
} elseif ($can_install && $is_writable && ((!empty($_POST['upload_pkg']) && !empty($_FILES['pkg_file'])) ||
	(!empty($_POST['fetch_pkg']) && !empty($_POST['pkg_url']))))
{
	try
	{
		if (empty($_POST['your_pwd']) || !$core->auth->checkPassword(crypt::hmac(DC_MASTER_KEY,$_POST['your_pwd']))) {
			throw new Exception(__('Password verification failed'));
		}
		
		if (!empty($_POST['upload_pkg']))
		{
			files::uploadStatus($_FILES['pkg_file']);
			
			$dest = $themes_dir.'/'.$_FILES['pkg_file']['name'];
			if (!move_uploaded_file($_FILES['pkg_file']['tmp_name'],$dest)) {
				throw new Exception(__('Unable to move uploaded file.'));
			}
		}
		else
		{
			$url = urldecode($_POST['pkg_url']);
			$dest = $themes_dir.'/'.basename($url);
			
			try
			{
				$client = netHttp::initClient($url,$path);
				$client->setUserAgent('Dotclear - http://www.dotclear.net/');
				$client->useGzip(false);
				$client->setPersistReferers(false);
				$client->setOutput($dest);
				$client->get($path);
			}
			catch( Exception $e)
			{
				throw new Exception(__('An error occurred while downloading the file.'));
			}
			
			unset($client);
		}
		
		$ret_code = installGalTheme($dest);
		http::redirect('plugin.php?p=gallery&m=maintenance&installed=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
} elseif ($is_writable && !empty($_POST['remove'])) {
	$entries = $_POST['themes'];
	try {
		foreach ($entries as $entry) {
			$e='gal_'.trim(strtr($entry,"./",""));
			if ($e !== 'gal_' && $e != 'gal_simple') {
				if (!files::deltree($themes_dir.'/'.$e)) {
					throw new Exception(sprintf(__('Could not remove theme %s'),
					html::escapeHTML($entry)));
				}
			}
		}
		http::redirect('plugin.php?p=gallery&m=maintenance&deleted=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}



?>
<html>
<head>
  <title><?php echo __('Maintenance'); ?></title>
  <?php echo dcPage::jsPageTabs("maintenance");
  ?>
  <script type="text/javascript">
  //<![CDATA[
  dotclear.msg.confirm_delete_themes = '<?php echo __('Are you sure you want to delete selected themes ?'); ?>';
  $(function() {
		$('input[name="remove"]').click(function() {
				return window.confirm(dotclear.msg.confirm_delete_themes);
		});
		});
  //]]>
  </script>
</head>
<body>

<?php
if (!empty($_GET['adapted'])) {
		echo '<p class="message">'.__('The theme has been successfully adapted.').'</p>';
}
if (!empty($_GET['installed'])) {
		echo '<p class="message">'.__('The theme has been successfully installed.').'</p>';
}
if (!empty($_GET['deleted'])) {
		echo '<p class="message">'.__('The theme has been successfully deleted.').'</p>';
}


$dcthemes = $core->themes->getModules();
$dcthemes_combo = array();
foreach ($dcthemes as $k => $v) {
	if ($k != 'simple')
		$dcthemes_combo[$v['name']]=$k;
}

$galthemes_combo = $core->gallery->getThemes();
/*if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}
*/

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('Maintenance').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
echo '<div class="multi-part" id="maintenance" title="'.__('Maintenance').'">';
echo '<form action="plugin.php" method="post" id="theme_adapter">'.
	'<fieldset><legend>'.__('Theme Adapter').'</legend>';
echo '<p>'.__('This section enables to adapt gallery themes to a Dotclear theme, if needed.')."</p>".
'<p>'.__('<strong>Caution :</strong> use the theme adapter only if you experience some layout problems when viewing galleries or images on your blog.')."</p>".
	'<p><label class="classic">'.__('Dotclear theme').' : </label>'.form::combo('dctheme',$dcthemes_combo,$core->blog->settings->system->theme).'</p>'.
	'<p><label class="classic">'.__('Force template regeneration').'</label>&nbsp;'.form::checkbox('themeoverwrite',false,false).
	'<p><input type="submit" name="adapt_theme" value="'.__('Adapt').'" /></p>'.
	form::hidden('p','gallery').
	form::hidden('m','maintenance').$core->formNonce().'</p>';
echo '</fieldset></form>';
if ($is_writable) {
echo '<form action="plugin.php" method="post" id="theme_deleter">'.
	'<fieldset><legend>'.__('Gallery Themes').'</legend>'.
	'<table class="clear"><tr>'.
	'<th colspan="2">'.__('Theme').'</th></tr>';

	foreach ($galthemes_combo as $theme) {
		if ($theme == 'simple'	)
			echo '<tr><td>'.form::checkbox(array('themes[]'),$theme,'','','',true).'</td><td>'.$theme.'</td></tr>';
		else
			echo '<tr><td>'.form::checkbox(array('themes[]'),$theme).'</td><td>'.$theme.'</td></tr>';
	}
	echo '</table>'.
	'<input type="submit" name="remove" value="'.__('Uninstall selected themes').'" />'.
	form::hidden('p','gallery').
	form::hidden('m','maintenance').
	$core->formNonce().'</p>';
echo '</fieldset></form>';
echo '<form action="plugin.php" method="post" id="theme_uploader"  enctype="multipart/form-data">'.
	'<fieldset><legend>'.__('Upload a new Gallery theme').'</legend>'.
	'<p class="field"><label class=" classic required" title="'.__('Required field').'">'.__('Theme zip file:').' '.
	'<input type="file" name="pkg_file" /></label></p>'.
	'<p class="field"><label class="classic required" title="'.__('Required field').'">'.__('Your password:').' '.
	form::password(array('your_pwd'),20,255).'</label></p>'.
	'<input type="submit" name="upload_pkg" value="'.__('Upload theme').'" />'.
	form::hidden('p','gallery').
	form::hidden('m','maintenance').
	$core->formNonce().
	'</fieldset></form>';

echo '<form action="plugin.php" method="post" id="theme_downloader">'.
	'<fieldset><legend>'.__('Download a new Gallery theme').'</legend>'.
	'<p class="field"><label class=" classic required" title="'.__('Required field').'">'.__('Theme zip file URL:').' '.
	form::field(array('pkg_url'),40,255).'</label></p>'.
	'<p class="field"><label class="classic required" title="'.__('Required field').'">'.__('Your password:').' '.
	form::password(array('your_pwd'),20,255).'</label></p>'.
	'<input type="submit" name="fetch_pkg" value="'.__('Download theme').'" />'.
	form::hidden('p','gallery').
	form::hidden('m','maintenance').
	$core->formNonce().
	'</fieldset></form>';
}
?>

</div>
</body>
</html>