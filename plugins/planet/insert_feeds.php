<?php
# Check if metadata plugin exists
if (!$core->plugins->moduleExists('metadata')) {
	throw new Exception('Unable to find metadata plugin');
}

# Getting sources
$sources = explode("\n",trim($core->blog->settings->planet_sources));

if (count($sources) == 0) {
	exit(0);
}

dt::setTZ($core->blog->settings->blog_timezone);

$meta = new dcMeta($core);
$updates = false;

foreach ($sources as $source)
{
	$source = trim($source);
	
	if (substr($source,0,1) == '#') {
		continue;
	}
	
	$feed = feedReader::quickParse($source,null);
	$cur = $core->con->openCursor($core->prefix.'post');
	
	$core->con->begin();
	
	$strReq =
	'SELECT P.post_id '.
	'FROM dc_post P INNER JOIN dc_meta M ON P.post_id = M.post_id '.
	"WHERE blog_id='".$core->con->escape($core->blog->id)."' ".
	"AND meta_type = 'planet_url' ".
	"AND meta_id = '%s' ";
	
	foreach ($feed->items as $item)
	{
		# Check if entry exists
		$rs = $core->con->select(sprintf($strReq,$item->link));
		if (!$rs->isEmpty()) {
			continue;
		}
		
		# Insert entry
		$cur->clean();
		$cur->user_id = $core->auth->userID();
		$cur->post_title = $item->title ? $item->title : text::cutString(html::clean($cur->post_content),60);
		$cur->post_content = $item->content ? $item->content : $item->description;
		$cur->post_format = 'xhtml';
		$cur->post_dt = date('Y-m-d H:i:s',$item->TS);
		$cur->post_status = 1;
		
		$creator = $item->creator ? $item->creator : 'unknown creator';
		
		try
		{
			$post_id = $core->blog->addPost($cur);
			
			$meta->setPostMeta($post_id,'planet_url',$item->link);
			$meta->setPostMeta($post_id,'planet_author',$creator);
			$meta->setPostMeta($post_id,'planet_site',$feed->link);
			$meta->setPostMeta($post_id,'planet_sitename',$feed->title);
			
			foreach ($item->subject as $subject) {
				$meta->setPostMeta($post_id,'tag',dcMeta::sanitizeMetaID($subject));
			}
		}
		catch (Exception $e)
		{
			$core->con->rollback();
			throw $e;
		}
		
		$updates = true;
	}
	
	$core->con->commit();
	
	if ($updates) {
		$core->blog->triggerBlog();
	}
}
?>