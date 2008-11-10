<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_REQUEST['edit']) && !empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/edit.php';
	return;
}

if (!empty($_REQUEST['add'])) {
	include dirname(__FILE__).'/add.php';
	return;
}

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->carnaval_active)) {
	try {
		$core->blog->settings->setNameSpace('carnaval');

		// Carnaval is not active by default
		$core->blog->settings->put('carnaval_active',false,'boolean');
		$core->blog->triggerBlog();
		http::redirect(http::getSelfURI());
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$active = (boolean)$core->blog->settings->carnaval_active;

# Delete CSS Class
if (!empty($_POST['removeaction']) && !empty($_POST['remove'])) {
	foreach ($_POST['remove'] as $k => $v)
	{
		try {
			dcCarnaval::delClass($v);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&removed=1');
	}
}

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->setNameSpace('carnaval');

		$active = (empty($_POST['active']))?false:true;
		$core->blog->settings->put('carnaval_active',$active,'boolean');

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Get CSS Classes
try {
	$rs = dcCarnaval::getClasses();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
  <title>Carnaval</title>
</head>

<body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=carnaval/icon_32.png) no-repeat;">
<?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; Carnaval - 
<a class="button" href="<?php echo $p_url.'&amp;add=1'; ?>"><?php echo html::escapeJS(
__('New CSS Class')); ?></a>
</h2>

<?php

if (!empty($_GET['removed'])) {
	echo '<p class="message">'.__('Classes have been successfully removed.').'</p>';
}

if (!empty($_GET['addclass'])) {
	echo '<p class="message">'.__('Class has been successfully created.').'</p>';
}

if (!empty($msg)) {
	echo '<p class="message">'.$msg.'</p>';
}

?>
<?php
	echo '<form action="'.$p_url.'" method="post" id="config-form">';
	echo '<fieldset><legend>'.__('Plugin activation').'</legend>';
	echo 
	'<p class="field">'.
			form::checkbox('active', 1, $active).
			'<label class=" classic" for="active">'.__('Enable Carnaval').'</label></p></fieldset>';
	echo 
	'<p>'.form::hidden(array('p'),'carnaval').
		$core->formNonce().
		'<input type="submit" name="saveconfig" accesskey="s" value="'.__('Save configuration').' (s)"/>';
	echo '</p></form>';
?>
<fieldset class="two-cols"><legend><?php echo __('My CSS Classes'); ?></legend>
<form action="plugin.php" method="post" id="classes-form">
<table class="maximal">
<thead>
<tr>
  <th colspan="2"><?php echo __('Name'); ?></th>
  <th><strong><?php echo __('CSS Class'); ?></strong></th>
  <th><?php echo __('Mail'); ?></th>
  <th><?php echo __('URL'); ?></th>
  <th><?php if ($core->blog->settings->theme == 'default') {echo __('Text color');} ?></th>
  <th><?php if ($core->blog->settings->theme == 'default') { echo __('Background color');} ?></th>
</tr>
</thead>
<tbody id="classes-list">
<?php
while ($rs->fetch())
{
	echo
	'<tr class="line" id="l_'.$rs->class_id.'">'.
	'<td class="minimal">'.form::checkbox(array('remove[]'),$rs->class_id).'</td>'.
	'<td>'.html::escapeHTML($rs->comment_author).'</a></td>'.
	'<td><a href="'.$p_url.'&amp;edit=1&amp;id='.$rs->class_id.'">'.
		html::escapeHTML($rs->comment_class).'</a></td>'.
	'<td>'.html::escapeHTML($rs->comment_author_mail).'</td>'.
	'<td>'.html::escapeHTML($rs->comment_author_site).'</td>';
	if ($core->blog->settings->theme == 'default') {
		echo '<td>'.html::escapeHTML($rs->comment_text_color).'</td>'.
		'<td>'.html::escapeHTML($rs->comment_background_color).'</td>';
	}
	echo '</tr>';
}
?>
</tbody>
</table>

<div class="two-cols">
<p class="col">
<?php echo form::hidden(array('p'),'carnaval');
echo $core->formNonce(); ?>
<input type="submit" name="removeaction" accesskey="d"
value="<?php echo __('Delete selected CSS Classes'); ?>"
onclick="return window.confirm('<?php echo html::escapeJS(
__('Are you sure you you want to delete selected CSS Classes ?')); ?>');" /></p>
</div>

</fieldset>

<?php dcPage::helpBlock('carnaval');?>
</body></html>
