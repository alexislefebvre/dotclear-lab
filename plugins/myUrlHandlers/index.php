<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of My URL handlers, a plugin for Dotclear.
# 
# Copyright (c) 2007-2008 Oleksandr Syenchuk
# <sacha@xn--phnix-csa.net>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	# Read default handlers
	$handlers = myUrlHandlers::getDefaults();
	
	# Overwrite with user settings
	$settings = @unserialize($core->blog->settings->url_handlers);
	if (is_array($settings)) {
		foreach ($settings as $name=>$url)
		{
			if (isset($handlers[$name])) {
				$handlers[$name] = $url;
			}
		}
	}
	unset($settings);
	
	if (!empty($_POST['handlers']) && is_array($_POST['handlers']))
	{
		foreach ($_POST['handlers'] as $name=>$url)
		{
			$url = text::tidyURL($url);
			
			if (empty($handlers[$name])) {
				throw new Exception(sprintf(
				__('Handler "%s" doesn\'t exist.'),html::escapeHTML($name)));
			}
			
			if (empty($url)) {
				throw new Exception(sprintf(
				__('Invalid URL for handler "%s".'),html::escapeHTML($name)));
			}
			
			$handlers[$name] = $url;
		}
		
		# Get duplicates
		$w = array_unique(array_diff_key($handlers,array_unique($handlers)));
		array_walk($w,create_function('&$v,$k,$h','$v = array_keys($h,$v);'),$handlers);
		$w = call_user_func_array('array_merge',$w);
		
		if (!empty($w)) {
			throw new Exception(sprintf(
				__('Duplicate URL in handlers "%s".'),implode('", "',$w)));
		}
	}
	
	
	if (isset($_POST['act_save']))
	{
		$core->blog->settings->setNamespace('myurlhandlers');
		$core->blog->settings->put('url_handlers',serialize($handlers));
		$core->blog->triggerBlog();
		$msg = __('URL handlers have been succefully updated.');
	}
	elseif (isset($_POST['act_restore']))
	{
		$core->blog->settings->setNamespace('myurlhandlers');
		$core->blog->settings->put('url_handlers',serialize(array()));
		$core->blog->triggerBlog();
		$handlers = myUrlHandlers::getDefaults();
		$msg = __('URL handlers have been succefully restored.');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

?>
<html><head>
<title><?php echo __('URL handlers'); ?></title>
</head><body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=myUrlHandlers/icon-b.png) no-repeat;">
  <?php echo html::escapeHTML($core->blog->name).' › '.__('URL handlers'); ?></h2>

<?php
if (!empty($msg)) {
	echo '<p class="message">'.html::escapeHTML($msg).'</p>';
}
?>

<?php if (empty($handlers)): ?>
<p class="message"><?php echo __('No URL handler to configure.'); ?></p>
<?php else: ?>
<p><?php echo __('You can write your own URL for each handler of this list.'); ?></p>
<form action="<?php echo $p_url; ?>" method="post">
<table>
  <thead>
    <tr><th>Type</th><th>URL</th></tr>
  </thead>
  <tbody>
<?php
foreach ($handlers as $name=>$url)
{
	echo
	'<tr><td>'.html::escapeHTML($name).'</td><td>'.
	form::field(array('handlers['.$name.']'),20,255,html::escapeHTML($url)).
	'</td></tr>'."\n";
}
?>
  </tbody>
</table>
<p><input type="submit" name="act_save" value="<?php echo __('save'); ?>" />
  <input type="submit" name="act_restore" value="<?php echo __('reset'); ?>" />
  <?php echo $core->formNonce(); ?></p>
</form>
<?php endif; ?>
</body></html>