<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_MODULE')) {

	return null;
}

$redir = empty($_REQUEST['redir']) ? 
	$list->getURL().'#plugins' : $_REQUEST['redir'];

# -- Get settings --
$core->blog->settings->addNamespace('hum');
$s = $core->blog->settings->hum;

$active			= (boolean) $s->active;
$comment_selected	= (boolean) $s->comment_selected;
$jquery_hide		= (boolean) $s->jquery_hide;
$css_extra		= (string) $s->css_extra;
$title_tag		= (string) $s->title_tag;
$content_tag		= (string) $s->content_tag;

# -- Set settings --
if (!empty($_POST['save'])) {

	try {
		$active			= !empty($_POST['active']);
		$comment_selected	= !empty($_POST['comment_selected']);
		$jquery_hide		= !empty($_POST['jquery_hide']);
		$css_extra		= $_POST['css_extra'];
		$title_tag		= preg_match('#^[a-zA-Z0-9]{2,}$"', $_POST['title_tag']) ?
			$_POST['title_tag'] : 'dt';
		$content_tag		= preg_match('#^[a-zA-Z0-9]{2,}$"', $_POST['content_tag']) ?
			$_POST['content_tag'] : 'dd';

		$s->put('active',			$active,			'boolean');
		$s->put('comment_selected',	$comment_selected,	'boolean');
		$s->put('jquery_hide',		$jquery_hide,		'boolean');
		$s->put('css_extra',		$css_extra,		'string');
		$s->put('title_tag',		$title_tag,		'string');
		$s->put('content_tag',		$content_tag,		'string');

		$core->blog->triggerBlog();

		dcPage::addSuccessNotice(
			__('Configuration has been successfully updated.')
		);
		http::redirect(
			$list->getURL('module=hum&conf=1&redir='.
			$list->getRedir())
		);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# -- Display form --
echo '
<div class="fieldset">
<h4>'.__('Activation').'</h4>

<p><label for="active">'.
form::checkbox(
	'active',
	1,
	$active
).
__('Enable plugin').
'</label></p>

<p><label for="comment_selected">'.
form::checkbox(
	'comment_selected',
	1,
	$active
).
__('By default, mark new comments as selected').
'</label></p>

</div>

<div class="fieldset">
<h4>'.__('Public').'</h4>

<p><label for="jquery_hide">'.
form::checkbox(
	'jquery_hide',
	1,
	$jquery_hide
).
__('Use jQuery to hide unselected comments').
'</label></p>

<p><label for="title_tag">'.
__('HTML tag of comment title block:').'</label>'.
form::field(
	'title_tag',
	7,
	64,
	$title_tag,
	'maximal'
).
'</p>

<p><label for="content_tag">'.
__('HTML tag of comment content block:').'</label>'.
form::field(
	'content_tag',
	7,
	64,
	$content_tag,
	'maximal'
).
'</p>

<p><label for="css_extra">'.
__('Additionnal style sheet:').'</label>'.
form::textarea(
	'css_extra',
	164,
	10,
	$css_extra,
	'maximal'
).
'</p>

</div>';
