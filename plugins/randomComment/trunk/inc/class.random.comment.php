<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}

class randomComment
{
	protected $text;
	protected $date;
	protected $link;
	protected $title;
	protected $author;

	/**
	 * Constructs randomComment object
	 *
	 * @param	object	core
	 * @param	object	w
	 */
	public function __construct($core,$w)
	{
		$this->w = $w;
		$this->core = $core;
		$this->comments = $this->core->blog->getComments();
	}

	/**
	 * Gets a random comment in all comments
	 */
	public function getRandomComment()
	{
		$this->comments->index(rand(0,$this->comments->count()-1));
		
		$this->text = $this->comments->getContent();
		$this->date = $this->comments->getDate($this->core->blog->settings->system->date_format);
		$this->link = $this->comments->getPostURL();
		$this->title = $this->comments->post_title;
		$this->author = $this->comments->comment_author;
	}

	/**
	 * Returns the comment content
	 *
	 * @return	string
	 */
	public function getWidgetContent()
	{
		$str = preg_replace('#^<p>(.*)</p>$#','$1',$this->text);

		if ((integer) $this->w->text_size > 0) {
			$str = preg_replace('#</?[^>]+>#','',$str);
			$str = substr($str,0,(integer) $this->w->text_size).' ...';
		}

		return $str;
	}

	/**
	 * Returns the comment information
	 *
	 * @return	string
	 */
	public function getWidgetInfo()
	{
		$link = '<a href="'.$this->link.'">'.$this->title.'</a>';
		$str = str_replace(array('%author%','%entry%','%date%'),array('<span class="rd_author">%1$s</span>','<span class="rd_entry">%2$s</span>','%3$s'),$this->w->comment_info);

		return sprintf($str,$this->author,$link,$this->date);
	}
}