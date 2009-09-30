<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$combo_refresh = array(
	'15s'=>10000,'30s'=>30000,'45s'=>45000,'60s'=>60000,'120s'=>120000
);

$tribune_flag = (boolean)$core->blog->settings->tribune_flag;
$tribune_syntax_wiki = (boolean)$core->blog->settings->tribune_syntax_wiki;
$tribune_display_order = (boolean)$core->blog->settings->tribune_display_order;
$tribune_refresh_time = abs((integer) $core->blog->settings->tribune_refresh_time);
$tribune_message_length = abs((integer) $core->blog->settings->tribune_message_length);
$tribune_limit = abs((integer) $core->blog->settings->tribune_limit);

if (!empty($_POST['saveconfig']))
{
	try
	{
		$core->blog->settings->setNameSpace('tribune');

		$active = (empty($_POST['tribune_flag']))?false:true;
		$wiki = (empty($_POST['tribune_syntax_wiki']))?false:true;
		$order = (empty($_POST['tribune_display_order']))?false:true;
		$timeout = (!empty($_POST['tribune_refresh_time']))?
			$_POST['tribune_refresh_time']:$tribune_refresh_time;
		$length = (!empty($_POST['tribune_message_length']))?
			$_POST['tribune_message_length']:$tribune_message_length;
		$limit = (!empty($_POST['tribune_limit'])&&is_numeric($_POST['tribune_limit']))?
			$_POST['tribune_limit']:$tribune_limit;
		$core->blog->settings->put('tribune_flag',$active,'boolean');
		$core->blog->settings->put('tribune_syntax_wiki',$wiki,'boolean');
		$core->blog->settings->put('tribune_display_order',$order,'boolean');
		$core->blog->settings->put('tribune_refresh_time',$timeout,'integer');
		$core->blog->settings->put('tribune_message_length',$length,'integer');
		$core->blog->settings->put('tribune_limit',$limit,'integer');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1&upd=1');

	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo __('Free chatbox'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <a class="button" href="<?php echo $p_url;?>"><?php echo __('Free chatbox'); ?></a> &rsaquo; <?php echo __('Configuration'); ?> </h2>
<?php 
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
	}

echo 
	'<div id="tribune_options">'.
	'<form action="plugin.php" method="post" id="form_tribune_options">'.
		'<fieldset>'.
			'<legend>'.__('Plugin activation').'</legend>'.
				'<div class="two-cols">'.
				'<div class="col">'.
				'<p class="field">'.
					form::checkbox('tribune_flag', 1, $tribune_flag).
					'<label class=" classic" for="tribune_flag">'.__('Enable chatbox').'</label>'.
				'</p>'.
				'<p class="form-note">'.
					sprintf(__('Don\'t forget to add a <a href="%s">widget</a> to display the chatbox on your blog.'),'plugin.php?p=widgets').
				'</p>'.
				'</div>'.
				'</div>'.
			'</fieldset>'.
			'<fieldset >'.
			'<legend>'.__('Miscellaneous options').'</legend>'.
				'<p class="field">'.
					'<label class=" classic" for="tribune_limit">'.__('Messages to show :').
						form::field('tribune_limit', 5,10, $tribune_limit).
					'</label>'.
				'</p>'.
				'<div class="two-cols">'.
				'<div class="col">'.
				'<p class="aera">'.
					form::checkbox('tribune_syntax_wiki', 1, $tribune_syntax_wiki).
					'<label class=" classic" for="tribune_syntax_wiki">'.__('Enable Wiki syntax in chatbox').'</label>'.
				'</p>'.
				'<p class="aera">'.
					form::checkbox('tribune_display_order', 1, $tribune_display_order).
					'<label class=" classic" for="tribune_display_order">'.__('Inverse chatbox display').'</label>'.
				'</p>'.
				'</div>'.
				'<div class="col">'.
				'<p class="field">'.
					'<label for="tribune_message_length">'.__('Length of messages :').
					form::field('tribune_message_length', 5,10, $tribune_message_length).
					'</label>'.
				'</p>'.
				'<p class="field">'.
					form::combo('tribune_refresh_time', $combo_refresh, $tribune_refresh_time).
					'<label class=" classic" for="tribune_refresh_time">'.__('Refresh rate of chatbox :').'</label>'.
				'</p>'.
				'</div>'.
				'</div>'.
		'</fieldset>'.


		'<p>'.
		form::hidden(array('p'),'dctribune').
		form::hidden('config',1).
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
		'</fieldset>'.
		'</form>'
		;
		?>

	</form>
</div>

</body>
</html>