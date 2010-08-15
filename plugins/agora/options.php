<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Getting current parameters
$agora_flag		= (boolean)$core->blog->settings->agora->agora_flag;
$agora_announce	= $core->blog->settings->agora->agora_announce;
$nb_message_per_feed	= $core->blog->settings->agora->nb_message_per_feed;
$redir_url = $p_url.'&amp;act=options';

if (!empty($_POST['saveconfig']))
{
	try
	{
		$agora_flag = (empty($_POST['agora_flag']))?false:true;
		$agora_announce = $_POST['agora_announce'];
		$nb_message_per_feed = abs((integer) $_POST['nb_message_per_feed']);
		if ($nb_message_per_feed <= 1) { $nb_message_per_feed = 1; }

		if (empty($_POST['agora_announce'])) {
			throw new Exception(__('No agora announce.'));
		}

		$core->blog->settings->setNamespace('agora');
 		$core->blog->settings->put('agora_flag',$agora_flag,'boolean','Agora activation flag');
		$core->blog->settings->put('agora_announce',$agora_announce,'string','Agora announce');
		$core->blog->settings->put('nb_message_per_feed',$nb_message_per_feed,'integer','Number of messages on feeds');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&act=options&msg=save');

	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
if ($_REQUEST['msg'])
{
	$msg = __('Configuration successfully updated.');
}
?>
<html>
<head>
	<title><?php echo __('Agora'); ?></title>
  <?php echo
  dcPage::jsToolBar().
  dcPage::jsLoad('index.php?pf=agora/js/_options.js');
?>
</head>
<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Agora').'</h2>';

 if (!empty($msg)) echo '<p class="message">'.$msg.'</p>';

if (!empty($err)) echo '<p class="error">'.$err.'</p>'; 

echo '<div id="agora_options">'.
	'<form method="post" action="'.$redir_url.'">'.
		'<fieldset>'.
			'<legend>'.__('Plugin activation').'</legend>'.
				'<div class="two-cols">'.
				'<div class="col">'.
				'<p class="field">'.
					form::checkbox('agora_flag', 1, $agora_flag).
					'<label class=" classic" for="agora_flag">'.__('Enable Agora').'</label>'.
				'</p>'.
				'</div>'.

				'</div>'.
		'</fieldset>'.
		'<fieldset>'.
			'<legend>'.__('Presentation options').'</legend>'.
				'<div class="two-cols">'.
				'<p class="area"><label for="cat_desc" class="required" title="'.__('Required field').'">'.
					__('Agora announce:').'</label>'.
					form::textarea('agora_announce',50,8,html::escapeHTML($agora_announce),'').
				'</p>'.
				/*'<div class="col">'.
				'<p>'.
					'<label class="classic" for="agora_nb_msg_page">'.sprintf(__('Display %s messages per thread\'page'),
					form::field('agora_nb_msg_page',2,3,$agora_nb_msg_page)).
					'</label>'.
				'</p>'.
				'</div>'.*/
				'<p><label class="classic">'.sprintf(__('Display %s messages per feed'),
				form::field('nb_message_per_feed',2,3,$nb_message_per_feed)).
				'</label></p>'.
				'<div class="col">'.
				'</div>'.

				'</div>'.
		'</fieldset>'.
		
		'<p>'.form::hidden(array('p'),'agora').
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
	'</form>'.
'</div>';
?>
</body>
</html>
