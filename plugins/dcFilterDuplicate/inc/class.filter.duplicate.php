<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcFilterDuplicate, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

/**
 * @ingroup DC_PLUGIN_DCFILTERDUPLICATE
 * @brief Filter duplicate comments on multiblogs.
 * @since 2.6
 */
class dcFilterDuplicate extends dcSpamFilter
{
	public $name = 'Duplicate comment filter';
	public $has_gui = true;

	protected function setInfo()
	{
		$this->name = __('Duplicate comment filter');
		$this->description = __('Same comments on others blogs of a multiblog.');
	}
	
	public function isSpam($type, $author, $email, $site, $ip, $content, $post_id, &$status)
	{
		if ($type != 'comment') {

			return null;
		}

		$minlen = abs((integer) $this->core->blog->settings->dcFilterDuplicate->dcfilterduplicate_minlen);
		if (strlen($content) < $minlen) {

			return null;
		}

		try {
			if ($this->isDuplicate($content, $ip)) {
				$this->markDuplicate($content, $ip);
				$status = 'Duplicate on other blog';

				return true;
			}
			else {

				return null;
			}
		}
		catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function isDuplicate($content, $ip)
	{
		$rs = $this->core->con->select(
			'SELECT C.comment_id '.
			'FROM '.$this->core->prefix.'comment C '.
			'LEFT JOIN '.$this->core->prefix.'post P ON C.post_id=P.post_id '.
			"WHERE P.blog_id != '".$this->core->blog->id."' ".
			"AND C.comment_content='".$this->core->con->escape($content)."' ".
			"AND C.comment_ip='".$ip."' "
		);

		return !$rs->isEmpty();
	}
	
	public function markDuplicate($content, $ip)
	{
		$cur = $this->core->con->openCursor($this->core->prefix.'comment');
		$this->core->con->writeLock($this->core->prefix.'comment');

		$cur->comment_status = -2;
		$cur->comment_spam_status = 'Duplicate on other blog';
		$cur->comment_spam_filter = 'dcFilterDuplicate';

		$cur->update(
			"WHERE comment_content='".$this->core->con->escape($content)."' ".
			"AND comment_ip='".$ip."' "
		);
		$this->core->con->unlock();

		$this->triggerOtherBlogs($content, $ip);
	}

	public function gui($url)
	{
		$minlen = abs((integer) $this->core->blog->settings->dcFilterDuplicate->dcfilterduplicate_minlen);
		if (isset($_POST['dcfilterduplicate_minlen'])) {
			$minlen = abs((integer) $_POST['dcfilterduplicate_minlen']);
			$this->core->blog->settings->dcFilterDuplicate->put(
				'dcfilterduplicate_minlen',
				$minlen,
				'integer'
			);
		}

		$res =
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<p><label class="classic">'.__('Minimum content length before check for duplicate:').'<br />'.
		form::field(array('dcfilterduplicate_minlen'), 65, 255, $minlen).
		'</label></p>'.
		'<p><input type="submit" name="save" value="'.__('Save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';

		return $res;
	}

	public function triggerOtherBlogs($content, $ip)
	{
		$rs = $this->core->con->select(
			'SELECT P.blog_id '.
			'FROM '.$this->core->prefix.'comment C '.
			'LEFT JOIN '.$this->core->prefix.'post P ON C.post_id=P.post_id '.
			"WHERE C.comment_content='".$this->core->con->escape($content)."' ".
			"AND C.comment_ip='".$ip."' "
		);

		while ($rs->fetch()) {
			$b = new dcBlog($this, $rs->blog_id);
			$b->triggerBlog();
			unset($b);
		}
	}
}
