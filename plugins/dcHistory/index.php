<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

//$history = new dcHistory($core);

$params = array();
if (!empty($_REQUEST['post'])) {
	$params['post_id'] = $_REQUEST['post'];
	$post_id = $_REQUEST['post'];
}

if (!empty($_REQUEST['diff'])) {
	$params['revision_id'] = $_REQUEST['diff'];
}

$restore = !empty($_REQUEST['restore']) ? $_REQUEST['restore'] : null;

$params['order'] = 'revision_dt desc';

if ($restore == 'lastDiff')
{
	try {
		$core->history->patchWithLastDiff($post_id);
		http::redirect($p_url.'&restoreLastdiff');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

try {
	$rs = $core->history->getAllRevisions($params);
	$count = $core->history->getAllRevisions($params,true);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
  <title><?php echo __('History'); ?></title>
</head>

<body>
<h2>
<?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo __('History'); ?> &rsaquo; <?php echo $count->f(0); ?>
</h2>

<?php
if (!empty($_REQUEST['diff']) && !empty($_REQUEST['post']))
{
	if ($rs->isEmpty()) {
		echo '<p class="error">'.__('No diff available with these conditions').'</p>';
	}
	else
	{
		$diff_c = explode("\n",$rs->revision_content_diff);
		$diff_e = explode("\n",$rs->revision_excerpt_diff);
		
		echo '<h3>'.__('post').':&nbsp;'.$rs->post_title.'</h3>'.
			'<p>'.__('Revision:&nbsp;').$rs->revision_id.'</p>';
		
		if ($rs->revision_excerpt_diff != '') {
		echo '<p class="area" id="diff-excerpt-area"><label for="revision_excerpt_diff">'.__('Excerpt diff:').'</label> '.
		form::textarea('diff_excerpt',50,count($diff_e),html::escapeHTML($rs->revision_excerpt_diff),'',2,true).
		'</p>';
		}
		
		if ($rs->revision_content_diff != '' ) {
			echo '<p class="area" id="diff-content-area"><label for="revision_content_diff">'.__('Content diff:').'</label> '.
			form::textarea('diff_content',50,count($diff_c),html::escapeHTML($rs->revision_content_diff),'',3,true).
			'</p>';
		}
		
		echo '<h3>'.__('Restore last revision from this post').'</h3>'.
			'<form action="plugin.php" method="post">'.
			'<p><input type="submit" value="'.__('Restore previous version of this post').'" /> '.
			$core->formNonce().
			form::hidden(array('p'),'dcHistory').'</p>'.
			form::hidden(array('restore'),'lastDiff').
			form::hidden(array('post'),$rs->post_id).
			'</form>';
	}

}

else {
	echo 
	'<div id="post_revisions">

			<table class="maximal">
				<thead>
					<tr>
						<th>'.__('Title').'</th>
						<th>'.__('Revision').'</th>
						<th>'.__('Author').'</th>
						<th>'.__('Date').'</th>
					</tr>
				</thead>
				<tbody id="revisions-list">';

	while($rs->fetch())
	{
		echo
			'<tr class="line">'.
			'<td class="maximal"><a href="post.php?id='.$rs->post_id.'" title="'.__('Go back to post').'&nbsp;'.$rs->post_id.'">'.html::escapeHTML($rs->post_title).'</a></td>'.

			'<td><a href="plugin.php?p=dcHistory&post='.$rs->post_id.'&diff='.$rs->revision_id.'" title="'.__('Go back to post').'&nbsp;'.$rs->post_id.'">'.html::escapeHTML($rs->revision_id).'</td>'.
			'<td>'.html::escapeHTML($rs->user_id).'</td>'.
			'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rs->revision_dt).'</td>'.
			'</tr>';

	}
	echo '</tbody></table></div>';
}
?>

			
		

</body></html>