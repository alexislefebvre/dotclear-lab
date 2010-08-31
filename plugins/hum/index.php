<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('amdin');

$core->blog->settings->addNamespace('hum');

$active = (boolean) $core->blog->settings->hum->active;
$comment_selected = (boolean) $core->blog->settings->hum->comment_selected;
$jquery_hide = (boolean) $core->blog->settings->hum->jquery_hide;
$css_extra = (string) $core->blog->settings->hum->css_extra;
$title_tag = (string) $core->blog->settings->hum->title_tag;
$content_tag = (string) $core->blog->settings->hum->content_tag;

if (isset($_POST['save']))
{
	try
	{
		$active = !empty($_POST['active']);
		$comment_selected = !empty($_POST['comment_selected']);
		$jquery_hide = !empty($_POST['jquery_hide']);
		$css_extra = $_POST['css_extra'];
		$title_tag = preg_match('#^[a-zA-Z0-9]{2,}$"',$_POST['title_tag']) ? $_POST['title_tag'] : 'dt';
		$content_tag = preg_match('#^[a-zA-Z0-9]{2,}$"',$_POST['content_tag']) ? $_POST['content_tag'] : 'dd';
		
		$core->blog->settings->hum->put('active',$active,'boolean');
		$core->blog->settings->hum->put('comment_selected',$comment_selected,'boolean');
		$core->blog->settings->hum->put('jquery_hide',$jquery_hide,'boolean');
		$core->blog->settings->hum->put('css_extra',$css_extra,'string');
		$core->blog->settings->hum->put('title_tag',$title_tag,'string');
		$core->blog->settings->hum->put('content_tag',$content_tag,'string');
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=hum&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Hide useless messages').'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Hide useless messages').'</h2>'.
(!empty($_REQUEST['done']) ? '<p class="message">'.__('Configuration successfully updated').'</p>' : '').'
<div class="two-cols">
<div class="col">
<fieldset><legend>'.__('Settings').'</legend>
<form method="post" action="plugin.php">

<h4>admin</h4>

<p><label class="classic">'.
form::checkbox('active','1',$active).__('Enable extension').'</label></p>

<p><label class="classic">'.
form::checkbox('comment_selected','1',$active).__('By default, mark new comments as selected').'</label></p>

<h4>public</h4>

<p><label class="classic">'.
form::checkbox('jquery_hide','1',$jquery_hide).__('Use jQuery to hide unselected comments').'</label></p>

<p><label class="classic">'.__('HTML tag of comment title block:').'<br />'.
form::field('title_tag',7,64,$title_tag).'</label></p>

<p><label class="classic">'.__('HTML tag of comment content block:').'<br />'.
form::field('content_tag',7,64,$content_tag).'</label></p>

<p><label class="classic">'.__('Additionnal style sheet:').' '.
form::textarea(array('css_extra'),164,10,$css_extra,'maximal').'</label></p>

<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().form::hidden(array('p'),'hum').'</p>
</form>
</fieldset>
</div><div class="col">
<fieldset><legend>'.__('About').'</legend>
<h4>'.__('How to').'</h4>
<p>'.__('In comments list and posts list of your blog you can now choose to un/select comments.').'<br />
'.__('Then in public side you can add text or CSS or whatever you want on un/selected comments.').'</p>
<h4>'.__('Note it').'</h4>
<ul>
<li>'.__('To use jQuery feature and extra CSS, your theme must have behavior publicHeadContent.').'</li>
<li>'.__('To use jQuery feature, your theme must have jQuery loaded.').'</li>
<li>'.__('With jQuery, you must place at least {{tpl:CommentsIfNotSelected}} in "class" attribute of the title comment HTML block.').'</li>
<li>'.__('With jQuery, links to hide/show comment content have class "read-it".').'</li>
<li>'.__('You can use <tpl:CommentSelectedIf is_selected="1"></tpl:CommentSelectedIf> or {{tpl:CommentsIfSelected return="selected"}} or {{tpl:CommentsIfNotSelected return="unselected"}} .').'</li>
</ul>
<h4>'.__('Exemple for jQuery with post.html of default theme').'</h4>
<ul>
<li>'.__('Default theme loads jQuery').'</li>
<li>'.__('Default theme has behavior publicHeadContent').'</li>
<li>'.__('Title tag:').' dt</li>
<li>'.__('Content tag:').' dd</li>
<li>'.__('Class of unselected comments:').' unselected ('.__('default').')</li>
<li>'.__('Only thing to do is to place {{tpl:CommentIfNotSelected}}').'</li>
</ul>
<pre>'.html::escapeHTML('
  <!-- # Comments -->
  <tpl:EntryIf show_comments="1">
    <tpl:Comments>
    <tpl:CommentsHeader>
      <div id="comments">
        <h3>{{tpl:lang Comments}}</h3>
      <dl>
    </tpl:CommentsHeader>
      <dt id="c{{tpl:CommentID}}" class="{{tpl:CommentIfMe}} {{tpl:CommentIfOdd}} {{tpl:CommentIfFirst}} {{tpl:CommentIfSelected}} {{tpl:CommentIfNotSelected}}"><a
      href="#c{{tpl:CommentID}}" class="comment-number">{{tpl:CommentOrderNumber}}.</a>
      {{tpl:lang On}} {{tpl:CommentDate}}, {{tpl:CommentTime}}
      {{tpl:lang by}} {{tpl:CommentAuthorLink}}</dt>
      
      <dd class="{{tpl:CommentIfMe}} {{tpl:CommentIfOdd}} {{tpl:CommentIfFirst}}">
      <!-- # --BEHAVIOR-- publicCommentBeforeContent -->
      {{tpl:SysBehavior behavior="publicCommentBeforeContent"}}
      
      {{tpl:CommentContent}}
      
      <!-- # --BEHAVIOR-- publicCommentAfterContent -->
      {{tpl:SysBehavior behavior="publicCommentAfterContent"}}
      </dd>
    <tpl:CommentsFooter>
      </dl>
      </div>
    </tpl:CommentsFooter>
    </tpl:Comments>
  </tpl:EntryIf>
').'</pre>
</fieldset>
</div></div>
<br class="clear"/>
<p class="right">
hum - '.$core->plugins->moduleInfo('hum','version').'&nbsp;
<img alt="'.__('Hide useless messages').'" src="index.php?pf=hum/icon.png" />
</p>';
dcPage::helpBlock('hum');
echo '</body></html>';
?>