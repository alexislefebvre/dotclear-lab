<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


require_once(dirname(__FILE__)."/inc/class.threadedcomments.commentlist.php");

$reply_code_template = '@<a href="#c%s">%s</a>&nbsp;: ';
$reply_code_match = '/<p>@<a href="#c%s"( rel="nofollow")?>/i';

# Get settings
$threading_active = $core->blog->settings->threading_active;
$threading_indent = $core->blog->settings->threading_indent;
$threading_max_levels = $core->blog->settings->threading_max_levels;
$threading_switch_text = $core->blog->settings->threading_switch_text;
$threading_by_default = $core->blog->settings->threading_by_default;

if ($threading_indent === null) {
	$threading_indent = 25;
}
if ($threading_max_levels === null) {
	$threading_max_levels = 5;
}
if ($threading_switch_text === null) {
	$threading_switch_text = __('Sort by thread');
}

if ($threading_by_default === null) {
	$threading_by_default = 0;
}

if (isset($_POST["saveconf"])) {
	# modifications
	try {
		$threading_active = !empty($_POST["threading_active"]);
		$threading_indent = intval(str_replace("px","",$_POST["threading_indent"]));
		$threading_max_levels = intval($_POST["threading_max_levels"]);
		$threading_switch_text = $_POST["threading_switch_text"];
		$threading_by_default = !empty($_POST["threading_by_default"]);

		if (empty($_POST['threading_indent'])) {
			throw new Exception(__('No indentation value.'));
		}
		if ($threading_indent == 0) {
			throw new Exception(__('Wrong indentation value.'));
		}
		if (empty($_POST['threading_max_levels'])) {
			throw new Exception(__('No maximum indentation level.'));
		}
		if ($threading_max_levels == 0) {
			throw new Exception(__('Wrong maximum indentation level value.'));
		}
		
		$core->blog->settings->setNameSpace('threadedComments');
		$core->blog->settings->put('threading_active',$threading_active,'boolean');
		$core->blog->settings->put('threading_indent',$threading_indent,'integer');
		$core->blog->settings->put('threading_max_levels',$threading_max_levels,'integer');
		$core->blog->settings->put('threading_switch_text',$threading_switch_text,'string');
		$core->blog->settings->put('threading_by_default',$threading_by_default,'boolean');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

if (isset($_POST["saveanswers"])) {
	# answer linking
	try {
		$comment_id = intval($_POST["comment"]);
		$answersto_id = intval($_POST["answersto"]);
		$comment = $core->blog->getComments(
			array("comment_id" => $comment_id));
		$answersto = $core->blog->getComments(
			array("comment_id" => $answersto_id));

		if ($comment->isEmpty()) {
			throw new Exception(__('Cannot find this comment.'));
		}
		if ($answersto->isEmpty()) {
			throw new Exception(__('Cannot find the answered comment.'));
		}

		print_r($_POST);
		echo $answersto->comment_content;
		echo $comment->comment_content;

		if (preg_match(sprintf($reply_code_match, $answersto_id),
		                       $comment->comment_content)) {
			http::redirect($p_url.'&already=1');
		}

		$reply_code = sprintf($reply_code_template, $answersto_id,
		                      $answersto->comment_author);
		if (strpos($comment->comment_content, '<p>') != 0) {
			$newcontent = "<p>" . $reply_code . "</p>" .
			              $comment->comment_content;
		} else {
			$newcontent = "<p>" . $reply_code .
			              substr($comment->comment_content, 3);
		}

		// Update comment
		$cur = $core->con->openCursor($core->prefix.'comment');
		$cur->comment_content = $newcontent;
		# --BEHAVIOR-- adminBeforeCommentUpdate
		$core->callBehavior('adminBeforeCommentUpdate',$cur,$comment_id);
		$core->blog->updComment($comment_id,$cur);
		# --BEHAVIOR-- adminAfterCommentUpdate
		$core->callBehavior('adminAfterCommentUpdate',$cur,$comment_id);
		
		http::redirect($p_url.'&linked=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

/* Get comments
-------------------------------------------------------- */
$comment_page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  20;
$params = array();
$params['limit'] = array((($comment_page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = false;
$params['comment_trackback'] = false;
$params['comment_status_not'] = -2;
try {
	$comments = $core->blog->getComments($params);
	$counter = $core->blog->getComments($params,true);
	$comment_list = new adminThreadedCommentList($core,$comments,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}


?>
<html>
<head>
	<title><?php echo(__('Threaded comments')); ?></title>
	<style type="text/css" media="screen">
		.center { text-align: center; }
	</style>
	<?php echo dcPage::jsLoad('js/_comments.js'); ?>
	<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('#form-link-comments tr.line').each(function() {
			dotclear.commentExpander(this);
		});
	});
	//]]>
	</script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Threaded comments'); ?></h2>
 
	<?php
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
	}
	if (!empty($_GET['already'])) {
		echo '<p class="error">'.__('This comment is already linked to the comment it replies to.').'</p>';
	}
	if (!empty($_GET['linked'])) {
		echo '<p class="message">'.__('This comment has been successfully linked to the comment it replies to.').'</p>';
	}
	?>

	<fieldset><legend><?php echo __('Configuration'); ?></legend>
	<form method="post" action="<?php echo($p_url); ?>">
		<p><?php echo $core->formNonce(); ?></p>

		<p><label class="classic"><?php 
			echo(form::checkbox('threading_active', 1,
			    (boolean) $threading_active).' '.
			    __('Allow threaded view')); ?></label></p>

		<p><label class="classic"><?php echo(__('Indentation width (in px):').
				" ".form::field('threading_indent',3,255,
				$threading_indent)); ?>px</p>

		<p><label class="classic"><?php echo(__('Maximum indentation level:').
				" ".form::field('threading_max_levels',3,255,
				$threading_max_levels)); ?></p>

		<p><label><?php echo(__('Switch text:').
				form::field('threading_switch_text',40,255,
				$threading_switch_text)); ?></p>

		<p><label class="classic"><?php 
			echo(form::checkbox('threading_by_default', 1,
			    (boolean) $threading_by_default).' '.
			    __('Default to threaded view')); ?></label></p>

		<p><input type="submit" name="saveconf"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>
	</fieldset>

	<fieldset><legend><?php echo __('Link comments'); ?></legend>

<? $comment_list->display($comment_page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-link-comments">'.
	
	'%s'.
	
	form::hidden(array('type'),$type).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('author'),preg_replace('/%/','%%',$author)).
	form::hidden(array('status'),$status).
	form::hidden(array('ip'),preg_replace('/%/','%%',$ip)).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	
	'<p>' . $core->formNonce() .
	'<input type="submit" name="saveanswers" value="'.__('Link comments').'" /></p>'.
	'</form>'
	);
?>
	</fieldset>

 
<?php dcPage::helpBlock('threadedComments');?>
</body>
</html>
