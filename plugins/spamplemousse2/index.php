<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse, a plugin for DotClear. 
# Copyright (c) 2005 Benoit CLERC, Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require dirname(__FILE__).'/class.dc.spampl.php';
$spamFilter = new dcSpamFilter($core);

$default_tab = 'infos';
if (!empty($_REQUEST['spamwords'])) {
	$default_tab = 'spamwords';
}

# Create list
if (!empty($_POST['createlist']))
{
	try {
		$spamFilter->defaultWordsList();
		http::redirect($p_url.'&list=1&spamwords=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Adding a spamword
if (!empty($_POST['swa']))
{
	$globalsw = !empty($_POST['globalsw']) && $core->auth->isSuperAdmin();
	
	try {
		$spamFilter->addRule('word',$_POST['swa'],$globalsw);
		http::redirect($p_url.'&added=1&spamwords=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Removing spamwords
if (!empty($_POST['swd']) && is_array($_POST['swd']))
{
	try {
		$spamFilter->removeRule($_POST['swd']);
		http::redirect($p_url.'&removed=1&spamwords=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
  <title><?php echo __('Spam filter'); ?></title>
  <?php echo dcPage::jsPageTabs($default_tab); ?>
  <link rel="stylesheet" type="text/css" href="index.php?pf=spamplemousse/style.css" />
</head>

<body>
<?php
if (!empty($_GET['list'])) {
	echo '<p class="message">'.__('Words have been successfully added').'</p>';
}
if (!empty($_GET['added'])) {
	echo '<p class="message">'.__('Word has been successfully added').'</p>';
}
if (!empty($_GET['removed'])) {
	echo '<p class="message">'.__('Words have been successfully removed').'</p>';
}
?>
<h2><?php echo __('Spam filter'); ?></h2>
<div class="multi-part" id="infos" title="Informations">
<h3><?php echo __('Information') ?></h3>
<ul>
	<li><?php echo __('Number of junk comments:').' <a href="comments.php?status=-2">'.
	$spamFilter->countJunkComments().'</a>'; ?></li>
	<li><?php echo __('Number of published comments:').' '.$spamFilter->countPublishedComments(); ?></li>
</ul>
<?php
$feed = $core->blog->url.$core->url->getBase('spamfeed').'/'.$code = $spamFilter->getUserCode();
if (DC_ADMIN_URL) {
	echo '<p><a class="feed" href="'.$feed.'">'.__('Junk comments RSS feed').'</a></p>';
}
?>
</div>

<div class="multi-part" id="spamwords" title="Spamwords">
<h3><?php echo __('Manage spamwords list') ?></h3>

<?php
echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('Add a word').'</legend>'.
'<p>'.form::field('swa',20,128).' '.
'<input type="submit" value="'.__('Add').'"/> ';

if ($core->auth->isSuperAdmin()) {
	echo '<label class="classic">'.form::checkbox('globalsw',1).' '.
	__('Global spamword').'</label>';
}

echo
form::hidden(array('spamwords'),1).'</p>'.
'</fieldset>'.
'</form>';

$rs = $spamFilter->getRules('word');
if (!$rs->isEmpty())
{
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<fieldset><legend>' . __('List') . '</legend>'.
	'<div class="wordslist">';
	
	while ($rs->fetch())
	{
		$disabled_word = false;
		$p_class = '';
		if (!$rs->blog_id) {
			$disabled_word = !$core->auth->isSuperAdmin();
			$p_class = 'class="globalword"';
		}
		
		echo
		'<p '.$p_class.'><label class="classic">'.
		form::checkbox(array('swd[]'),$rs->rule_id,false,'','',$disabled_word).' '.
		html::escapeHTML($rs->rule_content).
		'</label></p>';
	}
	
	echo
	'</div>'.
	'<p>'.form::hidden(array('spamwords'),1).
	'<input class="submit" type="submit" value="' . __('Delete selected words') . '"/></p>'.
	'</fieldset></form>';
}

if ($core->auth->isSuperAdmin())
{
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<p><input type="submit" value="'.__('Create default wordlist').'" />'.
	form::hidden(array('spamwords'),1).
	form::hidden(array('createlist'),1).'</p>'.
	'</form>';
}
?>
</div>

</body>
</html>