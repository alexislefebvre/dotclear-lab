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
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; Carnaval</h2>

<?php

if (!empty($_GET['removed'])) {
		echo '<p class="message">'.__('Classes have been successfully removed.').'</p>';
}

if (!empty($_GET['addclass'])) {
		echo '<p class="message">'.__('Class has been successfully created.').'</p>';
}

?>

<div class="multi-part" title="<?php echo __('Carnaval'); ?>">
<?php
	echo 
	'<h3>'.__('About').'</h3>'.
	'<p>'.__('You can personnalize the appearance of your comments and trackbacks in public part of your blog.').'</p>'.
	'<p>'.__('You have only to link a e-mail or a web site adress with a CSS class. Don\'t forget to add rules in CSS stylesheet of your theme.').'</p>'.
	'<p>'.__('The field \'Name\' is not used by this plugin.').'</p>';
?>
<form action="plugin.php" method="post" id="classes-form">
<table class="maximal dragable">
<thead>
<tr>
  <th colspan="2"><?php echo __('Name'); ?></th>
  <th><?php echo __('Mail'); ?></th>
  <th><?php echo __('URL'); ?></th>
  <th><?php echo __('CSS Class'); ?></th>
</tr>
</thead>
<tbody id="classes-list">
<?php
while ($rs->fetch())
{
	echo
	'<tr class="line" id="l_'.$rs->class_id.'">'.
	'<td class="minimal">'.form::checkbox(array('remove[]'),$rs->class_id).'</td>'.
	'<td><a href="'.$p_url.'&amp;edit=1&amp;id='.$rs->class_id.'">'.
		html::escapeHTML($rs->comment_author).'</a></td>'.
	'<td>'.html::escapeHTML($rs->comment_author_mail).'</td>'.
	'<td>'.html::escapeHTML($rs->comment_author_site).'</td>'.
	'<td>'.html::escapeHTML($rs->comment_class).'</td>'.
	'</tr>';
}
?>
</tbody>
</table>

<div class="two-cols">
<p class="col"><input type="submit" name="removeaction"
value="<?php echo __('Delete selected CSS Classes'); ?>"
onclick="return window.confirm('<?php echo html::escapeJS(
__('Are you sure you you want to delete selected CSS Classes ?')); ?>');" /></p>
</div>
</div>

<?php
require dirname(__FILE__).'/forms.php';
echo '<div class="multi-part" id="add-class" title="'.__('Add a CSS Class').'">
	<form action="plugin.php" method="post">
	<fieldset class="two-cols"><legend>'.__('Add a new CSS Class').'</legend>
	'.$forms['form_fields'].'
	<p>'.form::hidden(array('p'),'carnaval').$core->formNonce().
	'<input type="submit" name="add_class" value="'.__('save').'" tabindex="6" /></p>
	</fieldset>
	</form>
	</div>';
?>
</body></html>
