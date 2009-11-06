<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogFeeds, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Johan Pustoch and contributors
# johan.pustoch@crdp.ac-versailles.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

header('Content-Type: application/rss+xml, charset=utf-8');

$core->url->register('multiBlogFeedPosts','multiBlogFeedPosts','^multiBlogFeedPosts$',array('RssPostURL','multiBlogFeedPosts'));
$core->url->register('multiBlogFeedComments','multiBlogFeedComments','^multiBlogFeedComments$',array('RssCommentURL','multiBlogFeedComments'));

class RssPostURL extends dcUrlHandlers
{
        public static function multiBlogFeedPosts($args)
        {
                GetRSSHeader("Nouveaux billets","Posts");
                GetPostsItems($core);
                GetRSSFooter();
        }
}

class RssCommentURL extends dcUrlHandlers
{
        public static function multiBlogFeedComments($args)
        {
                GetRSSHeader("Nouveaux commentaires","Comments");
                GetCommentsItems($core);
                GetRSSFooter();
        }
}

function GetPostsItems($core)
{
	$query = "SELECT post_url, post_title, post_password,post_words ,post_excerpt,post_excerpt_xhtml,post_content,post_content_xhtml, post_dt,post_tz , post_format , blog_url FROM ".DC_DBPREFIX."post as post,".DC_DBPREFIX."blog as blog  WHERE post.blog_id=blog.blog_id AND post_status = 1 AND post_type = 'post' ORDER BY post_dt DESC LIMIT 50";
	global $core;
	$res_post = $core->con->select($query);
	while ($res_post->fetch())
	{
		$blog_url = $res_post->f('blog_url');
		$post_url = $blog_url.'post/'.$res_post->f('post_url');
		echo '<item>';
		echo '<title>'.$res_post->f('post_title').'</title>';
		echo '<guid isPermaLink="true">'.$post_url.'</guid>';
		echo '<link>'.$post_url.'</link>';
		echo '<pubDate>'.dt::rfc822(strtotime($res_post->f('post_dt')),$res_post->f('post_tz')).'</pubDate>';
		if ( $res_post->f('post_format')=='wiki' )
		{
			if ($res_post->f('post_password')!="")
			echo '<description>Billet en accès privé</description>';
			else echo '<description>'.html::absoluteURLs(html::escapeHTML( $core->wikiTransform( $res_post->f('post_content') ) ) , $core->blog->url ).'</description>';
		}
		else
		{
			if ($res_post->f('post_password')!="")
			echo '<description>Billet en accès privé</description>';
			else echo '<description>'.html::absoluteURLs(html::escapeHTML( $res_post->f('post_content_xhtml')), $core->blog->url ).'</description>';
		}
		echo '</item>';
	}
}

function GetCommentsItems($core)
{
	$query = "SELECT post_url, post_title,post_password, comment_content, comment_id, comment_dt ,comment_tz, blog_url FROM ".DC_DBPREFIX."comment as comment,".DC_DBPREFIX."post as post,".DC_DBPREFIX."blog as blog  WHERE post.post_id=comment.post_id AND post.blog_id=blog.blog_id AND comment_status = 1 ORDER BY comment_dt DESC LIMIT 50";
	global $core;
	$res_post = $core->con->select($query);
	while ($res_post->fetch())
	{
		$blog_url = $res_post->f('blog_url');
		$post_url = $blog_url.'post/'.$res_post->f('post_url');
		$comment_id = $res_post->f('comment_id');
		echo '<item>';
		echo '<title>'.$res_post->f('post_title').'</title>';
		echo '<guid isPermaLink="true">'.$post_url.'#c'.$comment_id.'</guid>';
		echo '<link>'.$post_url.'#c'.$comment_id.'</link>';
		echo '<pubDate>'.dt::rfc822(strtotime($res_post->f('comment_dt')),$res_post->f('comment_tz')).'</pubDate>';
		if ( $res_post->f('post_format')=='wiki' )
		{
			if ($res_post->f('post_password')!="")
			echo '<description>Billet en accès privé</description>';
			else echo '<description>'.html::absoluteURLs(html::escapeHTML( $core->wikiTransform( $res_post->f('comment_content') ) ) , $core->blog->url ).'</description>';
		}
		else
		{
			if ($res_post->f('post_password')!="")
			echo '<description>Billet en accès privé</description>';
			else echo '<description>'.html::absoluteURLs(html::escapeHTML( $res_post->f('comment_content')), $core->blog->url ).'</description>';
		}
		echo '</item>';
	}
}

function GetRSSHeader( $texte , $type)
{
	global $core;
	echo '<?xml version="1.0" encoding="utf-8"?><?xml-stylesheet title="XSL formatting" type="text/xsl" href="'.$core->blog->url.'feed/rss2/xslt" ?><rss version="2.0"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:atom="http://www.w3.org/2005/Atom" >
  <channel>
  <title>Feed - '.$texte.' - '.$core->blog->name.'</title>
  <link>'.$core->blog->url.'</link>
  <description>'.html::escapeHTML($core->blog->desc).'</description>
  <language>fr</language>
  <pubDate>'.date(DateTime::RFC822).'</pubDate>
  <generator>Dotclear - MultiBlogFeeds</generator>
  <atom:link href="'.$core->blog->url.'multiBlogFeed'.$type.'" rel="self" type="application/rss+xml" />';
}

function GetRSSFooter()
{
	echo '</channel>
	</rss>';
}
?>