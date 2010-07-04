<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of construction, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->blog->settings->addNamespace('construction');
$s =& $core->blog->settings->construction;

$flag = $s->construction_flag;
$allowed_ip = array();
$myip = http::realIP();

if (!empty($_POST['saveconfig']))
{
	try
	{
		$flag = (empty($_POST['construction_flag']))?false:true;

		$s->put('construction_flag',$flag,'boolean','Construction blog flag');
		$all_ip = explode("\n",$_POST['construction_allowed_ip']);
		foreach ($all_ip as $ip) {
			$allowed_ip[] = trim($ip);
		}
		$s->put('construction_allowed_ip',serialize($allowed_ip),'string','Construction blog allowed ip');
		$s->put('construction_title',$_POST['construction_title'],'string','Construction blog title');		
		$s->put('construction_message',$_POST['construction_message'],'string','Construction blog message');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&saved=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$nb_rows = count(unserialize($s->construction_allowed_ip));
if ($nb_rows < 2) {
	$nb_rows = 2;
} elseif ($nb_rows > 10) {
	$nb_rows = 10;
}
?>
<html>
<head>
	<title><?php echo __('Construction'); ?></title>
	<?php echo  dcPage::jsToolBar().
	dcPage::jsLoad('index.php?pf=construction/js/config.js'); ?>
</head>
<body>
<?php 
if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; 
if (!empty($_GET['saved'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('construction').'</h2>';
	
echo '<div id="construction_options">
<form method="post" action="'.$p_url.'">
<fieldset>
<legend>'.__('Configuration').'</legend>
<p class="field">'.
form::checkbox('construction_flag', 1, $s->construction_flag).
'<label class="classic" for="construction_flag">'.__('Plugin activation').'</label>
</p>
<p><label for"construction_allowed_ip">'.__('Allowed IP:').'&nbsp;</label>'.
form::textarea('construction_allowed_ip',20,$nb_rows,html::escapeHTML(implode("\n",unserialize($s->construction_allowed_ip)))).
'</p>
<p class="form-note">'.sprintf(__('Your IP is <strong>%s</strong> - the allowed IP can view the blog normally.'),$myip).'</p>
</fieldset>
<fieldset>
<legend>'.__('Presentation').'</legend>
<p class="area"><label for="construction_title">'.__('Title:').'</label>'.
form::field('construction_title',20,255,html::escapeHTML($s->construction_title),'maximal').
'</p>
<p class="area"><label for="construction_message">'.__('Informations:').'</label>'.
form::textarea('construction_message',40,10,html::escapeHTML($s->construction_message)).
'</p>
</fieldset>
<p>'.form::hidden(array('p'),'construction').
$core->formNonce().
'<input type="submit" name="saveconfig" value="'.__('save').'" />
</p>
</form>
</div>';
?>
</body>
</html>