<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of WFWComment, a plugin for DotClear2.
#
# Copyright (c) 2006-2009 Pep and contributors.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcWFWComment
{
	public $core;

	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
	}

	public function receive($post_id)
	{
		header('Content-Type: text/plain; charset=UTF-8');

		$rawdata = file_get_contents("php://input");
		if (empty($rawdata)) {
			http::head(400,'Bad request');
			echo	"Missing data";
			return;
		}

		$post_id = (integer) $post_id;
		$is_tb_like = true;
		$err = false;
		$msg = '';

		// Looking for <autor> or <dc:author>.
		// If found, incoming data wouln't be treated as a trackback
		if (preg_match('!<author[^>]*>(.*)</author[^>]*>!i', $rawdata, $match)) {
			$mail = $match[1];
		}

		if (preg_match('!<dc:creator[^>]*>(.*)</dc:creator[^>]*>!i', $rawdata, $match)) {
			if (preg_match('!^(.*)\((.*)\)!i', $match[1], $names)) {
				$name = $names[2];
				$mail = $names[1];
			} else {
				$name = $match[1];
			}
			$is_tb_like = false;
		}

		// Got <link> ?
		if (preg_match('!<link[^>]*>(.*)</link[^>]*>!i', $rawdata, $match)) {
			$link = $match[1];
		}

		// Got <title> ?
		if (preg_match('!<title[^>]*>(.*)</title[^>]*>!i', $rawdata, $match)) {
			$title = $match[1];
		}

		// Got <source> ?
		if (preg_match('!<source[^>]*>(.*)</source[^>]*>!i', $rawdata, $match)) {
			$blog_name = $match[1];
		}

		// Got <description> ?
		if (preg_match('!<description[^>]*>(.*)</description[^>]*>!ims', $rawdata, $match)) {
			if (preg_match('/^<!\[CDATA\[(.*)\]\]>/ims', $match[1], $cdata)) {
				$content = $cdata[1];
			} else {
				$content = $match[1];
			}
		}

		$name = !empty($name) ? $name : '';
		$mail = !empty($mail) ? $mail : '';
		$title = !empty($title) ? $title : '';
		$content = !empty($content) ? $content : '';
		$link = !empty($link) ? $link : '';
		$blog_name = !empty($blog_name) ? $blog_name : '';

		$charset = '';
		$comment = '';

		if ($this->core->blog === null) {
			$err = true;
			$msg = 'No blog.';
		}
		elseif ($is_tb_like) {
			if ($link == '') {
				$err = true;
				$msg = 'URL parameter is requiered.';
			}
			elseif ($blog_name == '') {
				$err = true;
				$msg = 'Blog name is requiered.';
			}
		}
		else {
			if (empty($name)) {
				$err = true;
				$msg = 'You must provide a name.';
			}
			elseif (!text::isEmail($mail)) {
				$err = true;
				$msg = 'You must provide a valid email adress.';
			}
		}

		if (!$err) {
			$post = $this->core->blog->getPosts(array('post_id'=>$post_id));

			if ($post->isEmpty()) {
				$err = true;
				$msg = 'No such post.';
			}
			elseif ($is_tb_like && !$post->trackbacksActive()) {
				$err = true;
				$msg = 'Trackbacks are not allowed for this post or weblog.';
			}
			elseif (!$post->commentsActive()) {
				$err = true;
				$msg = 'Comments are not allowed for this post or weblog.';
			}
		}

		if (!$err)
		{
			$charset = $this->getCharsetFromRequest();

			if (!$charset) {
				$charset = mb_detect_encoding($title.' '.$content.' '.$blog_name.' '.$name,
				'UTF-8,ISO-8859-1,ISO-8859-2,ISO-8859-3,'.
				'ISO-8859-4,ISO-8859-5,ISO-8859-6,ISO-8859-7,ISO-8859-8,'.
				'ISO-8859-9,ISO-8859-10,ISO-8859-13,ISO-8859-14,ISO-8859-15');
			}


			$link = trim(html::clean($link));
			$mail = trim(html::clean($mail));

			$cur = $this->core->con->openCursor($this->core->prefix.'comment');

			if ($is_tb_like) {
				if (strtolower($charset) != 'utf-8') {
					$title = iconv($charset,'UTF-8',$title);
					$content = iconv($charset,'UTF-8',$content);
					$blog_name = iconv($charset,'UTF-8',$blog_name);
				}

				$title = trim(html::clean($title));
				$title = html::decodeEntities($title);
				$title = html::escapeHTML($title);
				$title = text::cutString($title,60);

				$content = trim(html::clean($content));
				$content = html::decodeEntities($content);
				$content = preg_replace('/\s+/ms',' ',$content);
				$content = text::cutString($content,252);
				$content = html::escapeHTML($content).'...';

				$blog_name = trim(html::clean($blog_name));
				$blog_name = html::decodeEntities($blog_name);
				$blog_name = html::escapeHTML($blog_name);
				$blog_name = text::cutString($blog_name,60);

				if (!$blog_name) {
					$blog_name = 'Anonymous blog';
				}

				$comment =
				"<!-- TB -->\n".
				'<p><strong>'.($title ? $title : $blog_name)."</strong></p>\n".
				'<p>'.$content.'</p>';

				$cur->comment_author = $blog_name;
				$cur->comment_site = $link;
				$cur->comment_content = $comment;
				$cur->post_id = $post_id;
				$cur->comment_trackback = 1;
				$cur->comment_status = $this->core->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();

				$before_behavior = 'publicBeforeTrackbackCreate';
				$after_behavior  = 'publicAfterTrackbackCreate';
			}
			else {
				if (strtolower($charset) != 'utf-8') {
					$name = iconv($charset,'UTF-8',$name);
					$content = iconv($charset,'UTF-8',$content);
				}

				$name = trim(html::clean($name));
				$name = html::decodeEntities($name);
				$name = html::escapeHTML($name);
				$name = text::cutString($name,60);

				$content = trim(html::clean($content));
				$content = html::decodeEntities($content);
				$content = preg_replace('/\s+/ms',' ',$content);
				$content = html::escapeHTML($content);

				$cur->comment_author = $name;
				$cur->comment_site = $link;
				$cur->comment_email = $mail;
				$cur->comment_content = $content;
				$cur->post_id = $post_id;
				$cur->comment_status = $this->core->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();

				$before_behavior = 'publicBeforeCommentCreate';
				$after_behavior  = 'publicAfterCommentCreate';
			}

			try
			{
				# --BEHAVIOR-- publicBeforeCommentCreate / publicBeforeTrackbackCreate
				$this->core->callBehavior($before_behavior,$cur);

				$comment_id = $this->core->blog->addComment($cur);

				# --BEHAVIOR-- publicAfterCommentCreate / publicAfterTrackbackCreate
				$this->core->callBehavior($after_behavior,$cur,$comment_id);
			}
			catch (Exception $e) {
				$err = true;
				$msg = 'Something went wrong : '.$e->getMessage();
			}
		}

		if ($err) {
			http::head(412,'Precondition failed');
			if ($msg) {
				echo $msg;
			}
		}
	}

	private function getCharsetFromRequest()
	{
		if (isset($_SERVER['CONTENT_TYPE']))
		{
			if (preg_match('|charset=([a-zA-Z0-9-]+)|',$_SERVER['CONTENT_TYPE'],$m)) {
				return $m[1];
			}
		}

		return null;
	}
}
?>