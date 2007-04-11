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

class dcFilterSpample2 extends dcSpamFilter
{
	public $name = 'Spamplemousse2';
	public $has_gui = true;
	
	// Set here the localized description of the filter
	protected function setInfo()
	{
		$this->description = __('A bayesian filter');
	}
	
	
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

	
	public function isSpam($type,$author,$email,
		$site,$ip,$content,$post_id,&$status)
	{
		$spamFilter = new bayesian($this->core);

		$spam = $spamFilter->handle_new_message($author,$email,$site,$ip,$content);
		// FIXME : passer comment_bayes à 1
		if ($spam == true) {
			$status = '';
		}
		
		return $spam;
	}

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
}
?>
