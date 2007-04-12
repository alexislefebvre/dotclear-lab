<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse2, a plugin for DotClear. 
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

/// @defgroup SPAMPLE2 Spamplemousse2, a bayesian spam filter

/**
@ingroup SPAMPLE2
@brief Spamplemousse2 filter adapter class

This class implements all the methods needed for this plugin
to run as a spam filter.
*/
class dcFilterSpample2 extends dcSpamFilter
{
	public $name = 'Spamplemousse2';
	public $has_gui = true;

	/**
	Set here the localized description of the filter.
	
	@return			<b>string</b>
	*/
	protected function setInfo()
	{
		$this->description = __('A bayesian filter');
	}
	
	/**
	Returns a status message for a given comment which relates to the filtering process.
	
	@param	status			<b>integer</b>		Status of the comment
	@param	comment_id		<b>integer</b>		Id of the comment
	@return					<b>string</b>
	*/	
	public function getStatusMessage($status,$comment_id)
	{
		$p = 0;
		$con = $this->core->con;
		$spamFilter = new bayesian($this->core);
		$rs = $con->select('SELECT comment_author, comment_email, comment_site, comment_ip, comment_content FROM '.$this->core->blog->prefix.'comment WHERE comment_id = '.$comment_id);
		$rs->fetch();
		$p = $spamFilter->getMsgProba($rs->comment_author, $rs->comment_email, $rs->comment_site, $rs->comment_ip, $rs->comment_content);
		$p = round($p*100);
		return sprintf(__('Filtered by %s, actual spamminess: %s %%'),$this->guiLink(), $p);
	}

	/**
	This method should return if a comment is a spam or not.
	
	Your filter should also fill $status variable with its own information if
	comment is a spam.
	
	@param		type	<b>string</b>		Comment type (comment or trackback)
	@param		author	<b>string</b>		Comment author
	@param		email	<b>string</b>		Comment author email
	@param		site	<b>string</b>		Comment author website
	@param		ip		<b>string</b>		Comment author IP address
	@param		content	<b>string</b>		Comment content
	@param		post_id	<b>integer</b>		Comment post_id
	@param[out]	status	<b>integer</b>		Comment status
	@return				<b>boolean</b>
	*/
	public function isSpam($type,$author,$email,
		$site,$ip,$content,$post_id,&$status)
	{
		$spamFilter = new bayesian($this->core);

		$spam = $spamFilter->handle_new_message($author,$email,$site,$ip,$content);

		if ($spam == true) {
			$status = '';
		}
		
		return $spam;
	}

	/**
	This method is called when a non-spam (ham) comment becomes spam or when a
	spam becomes a ham. It trains the filter with this new user decision.
	
	@param[out]	status	<b>integer</b>		Comment status
	@param	filter		<b>string</b>		Filter name
	@param	type		<b>string</b>		Comment type (comment or trackback)
	@param	author		<b>string</b>		Comment author
	@param	email		<b>string</b>		Comment author email
	@param	site		<b>string</b>		Comment author website
	@param	ip			<b>string</b>		Comment author IP address
	@param	content		<b>string</b>		Comment content
	@param	rs			<b>record</b>		Comment record
	*/
	public function trainFilter($status,$filter,$type,
		$author,$email,$site,$ip,$content,$rs)
	{ 
		$spamFilter = new bayesian($this->core);

		$rs2 = $this->core->con->select('SELECT comment_bayes FROM '.$this->core->blog->prefix.'comment WHERE comment_id = '.$rs->comment_id);
		$rs2->fetch();
		
		$spam = 0;		
		if ($status == 'spam') { # the current action marks the comment as spam
			$spam = 1;
		}
		
		if ($rs2->comment_bayes == 0) {
			$spamFilter->train($author,$email,$site,$ip,$content,$spam);
			$req = 'UPDATE '.$this->core->blog->prefix.'comment SET comment_bayes = 1 WHERE comment_id = '.$rs->comment_id;
			$this->core->con->execute($req);
		} else {
			$spamFilter->retrain($author,$email,$site,$ip,$content,$spam);
		}
	}

	/**
	This method handles the main gui used to configure this plugin.
		
	@param	url			<b>string</b>		url of the plugin
	@return				<b>string</b>		html content
	*/	
	public function gui($url) {
		$content = '';
		
		if ($_GET['cleanup'] == 1) {
			
		} else if ($_GET['oldmsg'] == 1) {
			$spamFilter = new bayesian($this->core);
			$spamFilter->oldMsgs();		
		}
		
		# affichage 
		$content .= '<p><a href="'.$url.'&cleanup=1">'.__('Cleanup').'</a></p>';
		$content .= '<p><a href="'.$url.'&oldmsg=1">'.__('Learn from old messages').'</a></p>';
		
		return $content;
	}

	/**
	This method is a hack to toggle the "learned" flag on a given comment.
	When a comment passes through the isSpam method of this filter for the first
	time, it is possible that the filter learns from this message, but in isSpam
	we are too early in the filtering process to be able to toggle the flag. So
	we set the global $GLOBALS['sp2_learned'] to 1, and when the process comes to 
	its end, this method is triggered (by the events publicAfterCommentCreate and
	publicAfterTrackbackCreate), and we update the flag in the database.
		
	@param	cur			<b>cursor</b>		cursor on the comment
	@param	id			<b>integer</b>		id of the comment
	*/
	public static function toggleLearnedFlag($cur, $id)
	{
		$core = $GLOBALS['core'];
		if (isset($GLOBALS['sp2_learned']) && $GLOBALS['sp2_learned'] == 1) {
			$req = 'UPDATE '.$core->blog->prefix.'comment SET comment_bayes = 1 WHERE comment_id = '.$id;
			$core->con->execute($req);			
		}
	}
}
?>
