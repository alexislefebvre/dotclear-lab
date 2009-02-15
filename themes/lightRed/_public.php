<?php
$core->tpl->addValue('MyEntriesCount',array('myTpl','MyEntriesCount'));
$core->tpl->addValue('MyCommentsCount',array('myTpl','MyCommentsCount'));

class myTpl
{
	/*
	Cette fonction affiche le nombre de billets
	*/
	public static function MyEntriesCount($attr)
	{
		$tbl_billets = $GLOBALS['core']->blog->prefix."post";
        $billets = $GLOBALS['core']->con->select("
            SELECT count(post_id) as somme
            FROM ".$tbl_billets." billets
            WHERE post_status=1 AND
                  blog_id='asiam'")->field("somme");
	    return '<?php echo '.$billets.'; ?>';
	}


     /*
	Cette fonction affiche le nombre de commentaires
	*/
	public static function MyCommentsCount($attr)
	{
	    global $core;
        $tbl_billets = $GLOBALS['core']->blog->prefix."post";
		$tbl_comments = $core->blog->prefix."comment";
        $comments = $core->con->select("
            SELECT count(comment_id) as somme
            FROM ".$tbl_billets." billets, ".$tbl_comments." comments
            WHERE comments.post_id = billets.post_id AND
                  billets.post_status=1 AND
                  comment_status=1 AND
                  comment_trackback=0 AND
                  blog_id='asiam'")->field("somme");

	    return '<?php echo '.$comments.'; ?>';
	}
}

?>
