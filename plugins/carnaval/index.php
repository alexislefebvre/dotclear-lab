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

$comment_author = $comment_author_mail = $comment_author_site = $comment_class =
$default_tab = '';

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

# Add CSS Class
if (!empty($_POST['add_class']))
{
	$comment_author = $_POST['comment_author'];
	$comment_author_mail = $_POST['comment_author_mail'];
	$comment_author_site = $_POST['comment_author_site'];
	$comment_class = $_POST['comment_class'];
	
	try {
		dcCarnaval::addClass($comment_author,$comment_author_mail,$comment_author_site,$comment_class);
		http::redirect($p_url.'&addclass=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$default_tab = 'add-class';
	}
}

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
  <?php echo dcPage::jsToolMan(); ?>
  <?php echo dcPage::jsConfirmClose('classes-form','add-class-form'); ?>
  <?php echo dcPage::jsPageTabs($default_tab); ?>
</head>

<body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=carnaval/icon_32.png) no-repeat;">
<?php echo html::escapeHTML($core->blog->name); ?> &gt; Carnaval</h2>

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

<div class="multi-part" title="<?php echo __('Carnaval'); ?>">
<?php
	echo '<form method="post" action="plugin.php">';
	echo '<fieldset><legend>'.__('Plugin activation').'</legend>';
	echo 
	'<p class="field">'.
			form::checkbox('active', 1, $active).
			'<label class=" classic" for="active">'.__('Enable Carnaval').'</label></p></fieldset>';
	echo 
	'<p><input type="hidden" name="p" value="carnaval" />'.
		$core->formNonce().
		'<input type="submit" name="saveconfig" accesskey="s" value="'.__('Save configuration').' (s)"/>';
	echo '</p></form>';
?>
<fieldset class="two-cols"><legend><?php echo __('My CSS Classes'); ?></legend>
<form action="plugin.php" method="post" id="classes-form">
<table class="maximal dragable">
<thead>
<tr>
  <th colspan="2"><?php echo __('Name'); ?></th>
  <th><strong><?php echo __('CSS Class'); ?></strong></th>
  <th><?php echo __('Mail'); ?></th>
  <th><?php echo __('URL'); ?></th>
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
	'<td>'.html::escapeHTML($rs->comment_author_site).'</td>'.
	'</tr>';
}
?>
</tbody>
</table>

<div class="two-cols">
<p class="col"><input type="submit" name="removeaction" accesskey="d"
value="<?php echo __('Delete selected CSS Classes'); ?>"
onclick="return window.confirm('<?php echo html::escapeJS(
__('Are you sure you you want to delete selected CSS Classes ?')); ?>');" /></p>
</div>
</fieldset>
</div>

<?php
require dirname(__FILE__).'/forms.php';
echo '<div class="multi-part" id="add-class" title="'.__('Add a CSS Class').'">
	<form action="plugin.php" method="post">
	<fieldset class="two-cols"><legend>'.__('Add a new CSS Class').'</legend>
	'.$forms['form_fields'].'
	<p>'.form::hidden(array('p'),'carnaval').$core->formNonce().
	'<input type="submit" name="add_class" accesskey="a" value="'.__('Add').' (a)" tabindex="6" /></p>
	</fieldset>
	</form>
	</div>';
?>
<?php dcPage::helpBlock('carnaval');?>
</body></html>
