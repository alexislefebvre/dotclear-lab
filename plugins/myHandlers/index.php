<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My Handlers', a plugin for Dotclear 2             *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Handlers' (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

try
{
	$handlers = myHandlers::getDefaults();
	
	if (($settings = @unserialize($core->blog->settings->my_handlers))
	&& is_array($settings)) {
		foreach ($settings as $name=>$url)
		{
			if (isset($handlers[$name])) {
				$handlers[$name] = $url;
			}
		}
	}
	
	
	if (!empty($_POST['handlers']) && is_array($_POST['handlers'])) {
		foreach ($_POST['handlers'] as $name=>$url)
		{
			$url = strtolower(text::str2url($url,false));
			
			if (empty($handlers[$name])) {
				throw new Exception(sprintf(
				__('"%s" handler does not exist.'),html::escapeHTML($name)));
			}
			
			if (empty($url)) {
				throw new Exception(sprintf(
				__('Invalid URL for handler "%s".'),html::escapeHTML($name)));
			}
			
			$handlers[$name] = $url;
		}
		
		if ($keys = array_keys(array_diff_key($handlers,array_unique($handlers)))) {
			throw new Exception(sprintf(count($keys) > 1
				? __('Duplicate URL in handlers "%s".')
				: __('Duplicate URL in handler "%s".'),implode('", "',$keys)));
		}
	}
	
	
	if (isset($_POST['act_save'])) {
		$core->blog->settings->setNamespace('myhandlers');
		$core->blog->settings->put('my_handlers',serialize($handlers));
		$core->blog->triggerBlog();
		$msg = __('URL handlers have been succefully updated.');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

echo '
<html><head>
<title>'.__('URL handlers').'</title>'.
dcPage::jsToolMan().'
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &gt; '.
	__('Personalize default URL handlers').'</h2>';

if (!empty($msg)) {
	echo '<p class="message">'.html::escapeHTML($msg).'</p>';
}

?>

<?php if (empty($handlers)): ?>
<p class="message"><?php echo __('No URL handlers to define.'); ?></p>
<?php else: ?>
<p><?php echo __('You can write your own URL for each handler in this list.'); ?></p>
<form action="<?php echo $p_url; ?>" method="post">
<table>
  <thead>
    <tr><th>Type</th><th>URL</th></tr>
  </thead>
  <tbody>
<?php
foreach ($handlers as $name=>$url)
{
	echo '    <tr><td>'.$name.'</td><td>'.
		form::field(array('handlers['.$name.']'),20,255,$url).'</td></tr>';
}
?>
  </tbody>
</table>
<p><input type="submit" name="act_save" value="<?php echo __('save'); ?>" />
  <?php echo $core->formNonce(); ?></p>
</form>
<?php endif; ?>
</body></html>