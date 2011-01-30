<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of optionsForComment, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

$core->blog->settings->addNamespace('optionsForComment');
$active = (boolean) $core->blog->settings->optionsForComment->active;
$css_extra = $core->blog->settings->optionsForComment->css_extra;
if ($css_extra === null) {
	$css_extra = 
	"#comment-form p.anonymous { margin: 0; }\n".
	"#comment-form input#c_anonymous { width: auto; border: 0; margin: 0 5px 0 30%; }\n".
	"#comment-form img.ofc-twitterimage { width:32px; height:32px; }\n".
	"#comment-form p.ofc-twitterlogin { margin: 0 5px 0 30%; }";
}
$mode = (string) $core->blog->settings->optionsForComment->mode;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

try {
	$core->callBehavior('optionsForCommentAdminPrepend',$core,$action);

	if ($action == 'savesettings') {
		$active = isset($_POST['active']);
		$css_extra = $_POST['css_extra'];
		$mode = $_POST['mode'];
		
		$core->blog->settings->optionsForComment->put('active',isset($_POST['active']),'boolean');
		$core->blog->settings->optionsForComment->put('css_extra',$css_extra,'string');
		$core->blog->settings->optionsForComment->put('mode',$mode,'string');
	}
	
	if ($action) {
		$core->blog->triggerBlog();
		http::redirect($p_url.'&msg='.$action);
	}
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>'.__('Options for comment').'</title>';

$core->callBehavior('optionsForCommentAdminHeader',$core);

echo '
</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Options for comment').'</h2>
';
if (!empty($_REQUEST['msg'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

echo '
<form method="post" action="'.$p_url.'">

<fieldset><legend>'.__('Extension').'</legend>
<p><label class="classic">'.
form::checkbox(array('active'),'1',$active).
__('Enable extension on this blog').'</label></p>
</fieldset>

<fieldset><legend>'.__('Fields').'</legend>
<p><label class="classic">'.
form::radio(array('mode'),'',empty($mode)).
__('Normal usage').'</label></p>';

$core->callBehavior('optionsForCommentAdminFormMode',$core);

echo '
</fieldset>';

$core->callBehavior('optionsForCommentAdminForm',$core);

echo '
<fieldset><legend>'.__('Theme').'</legend>
<p><label class="classic">'.__('Additionnal style sheet:').' '.
form::textarea(array('css_extra'),164,10,$css_extra,'maximal').'</label></p>
</fieldset>
<p><input type="submit" name="save" value="'.__('send').'" />'.
$core->formNonce().
form::hidden(array('p'),'optionsForComment').
form::hidden(array('action'),'savesettings').'
</p>

</form>
<br class="clear"/>
<p class="right">
optionsForComment - '.$core->plugins->moduleInfo('optionsForComment','version').'&nbsp;
<img alt="optionsForComment" src="index.php?pf=optionsForComment/icon.png" />
</p>';
dcPage::helpBlock('optionsForComment');
echo '</body></html>';
?>