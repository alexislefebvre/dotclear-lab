<?php
class publicSouvenir
{
	public static function show(&$w)
	{
		global $core, $_ctx;

		if ($core->url->type != 'post') {
			return;
		}

		$query = 'SELECT `post_title`, `post_url`, `post_dt`,
			ABS(TIMESTAMPDIFF(SECOND,\''.$_ctx->posts->post_dt.'\', SUBDATE(\''.$_ctx->posts->post_dt.'\', INTERVAL '.$w->interval.' MONTH))) AS DIFF 
			FROM '.$core->prefix.'post
			WHERE (
				(`post_dt` >= SUBDATE(\''.$_ctx->posts->post_dt.'\', INTERVAL '.ceil($w->interval*30.4+$w->range).' DAY)) 
				AND (`post_dt` <= SUBDATE(\''.$_ctx->posts->post_dt.'\', INTERVAL '.ceil($w->interval*30.4-$w->range).' DAY))
				AND (`post_status` = \'1\')
				AND (`blog_id` = \''.$core->con->escape($core->blog->id).'\')
			)
			ORDER BY DIFF ASC LIMIT 1;';

		$rs = $core->con->select($query);

		$post_title = (strlen($w->truncate) > 0) ? text::cutString($rs->f('post_title'),$w->truncate) : $rs->f('post_title');

		$header = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		$date = (strlen($w->date) > 0) ? ' '.dt::dt2str($w->date,$rs->f('post_dt')) : null;

		if ($rs->count() > 0)
		{
			return '<div id="postBefore">'.$header.'<p class="text"><a href="'.$core->blog->url.$core->url->getBase('post').'/'.html::sanitizeURL($rs->f('post_url')).'" title="'.$rs->f('post_title').'">'.$post_title.$date.'</a></p></div>';
		}
	}
}
?>