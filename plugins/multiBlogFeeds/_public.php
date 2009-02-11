<?php
if (!defined('DC_RC_PATH')) { return; }

header('Content-Type: application/rss+xml');

$core->url->register('multiBlogFeedPosts','multiBlogFeedPosts','^multiBlogFeedPosts$',array('RssPostURL','multiBlogFeedPosts'));
$core->url->register('multiBlogFeedComments','multiBlogFeedComments','^multiBlogFeedComments$',array('RssCommentURL','multiBlogFeedComments'));


class RssPostURL extends dcUrlHandlers
{
        public static function multiBlogFeedPosts($args)
        {
                AfficheEntete("Nouveaux billets");
                ListNewPosts($core);
                AfficheBas();
        }
}
class RssCommentURL extends dcUrlHandlers
{
        public static function multiBlogFeedComments($args)
        {
                AfficheEntete("Nouveaux commentaires");
                ListNewComments($core);
                AfficheBas();
        }
}


function ListNewPosts($core)
{
      $query = "SELECT post_url, post_title, post_content, post_dt , blog_url FROM ".DC_DBPREFIX."post as post,".DC_DBPREFIX."blog as blog  WHERE post.blog_id=blog.blog_id AND post_status = 1 AND post_type = 'post' ORDER BY post_dt DESC LIMIT 50";
      //echo $query;
      global $core;
      $res_post = $core->con->select($query);
      while ($res_post->fetch())
      {
      	$blog_url = $res_post->f('blog_url');
      	$post_url = $blog_url.'post/'.$res_post->f('post_url');
        echo '<item>';
        echo '<title>'.$res_post->f('post_title').'</title>';
        echo '<link>'.$post_url.'</link>';
        echo '<pubDate>'.$res_post->f('post_dt').'</pubDate>';
        echo '<description>'.html::absoluteURLs(html::escapeHTML($res_post->f('post_content')),$core->blog->url).'</description>';
        echo '</item>';
      }
}

function ListNewComments($core)
{
      $query = "SELECT post_url, post_title, comment_content, comment_dt , blog_url FROM ".DC_DBPREFIX."comment as comment,".DC_DBPREFIX."post as post,".DC_DBPREFIX."blog as blog  WHERE post.post_id=comment.post_id AND post.blog_id=blog.blog_id AND comment_status = 1 ORDER BY comment_dt DESC LIMIT 50";
      global $core;
      $res_post = $core->con->select($query);
      while ($res_post->fetch())
      {
      	$blog_url = $res_post->f('blog_url');
      	$post_url = $blog_url.'post/'.$res_post->f('post_url');
        echo '<item>';
        echo '<title>'.$res_post->f('post_title').'</title>';
        echo '<link>'.$post_url.'</link>';
        echo '<pubDate>'.$res_post->f('comment_dt').'</pubDate>';
        echo '<description>'.html::absoluteURLs(html::escapeHTML($res_post->f('comment_content')),$core->blog->url).'</description>';
        echo '</item>';
      }
}

function AfficheEntete($type)
{
	global $core;
  echo '<?xml version="1.0" encoding="utf-8"?><?xml-stylesheet title="XSL formatting" type="text/xsl" href="'.$core->blog->url.'feed/rss2/xslt" ?><rss version="2.0"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel>
  <title>Feed - '.$type.' - '.$core->blog->name.'</title>
  <link>'.$core->blog->url.'</link>
  <description>'.html::escapeHTML($core->blog->desc).'</description>
  <language>fr</language>
  <pubDate>'.date('D, j F Y H:i:s').'</pubDate>
  <generator>Dotclear</generator>';
 }

function AfficheBas()
{
echo '</channel>
</rss>';
}
?>