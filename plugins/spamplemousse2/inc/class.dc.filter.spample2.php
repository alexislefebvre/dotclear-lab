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
	public $has_gui = false;
	
	// Set here the localized description of the filter
	protected function setInfo()
	{
		$this->description = __('A bayesian filter');
	}
	
	
	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
	}

	
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

	public function trainFilter($status,$filter,$type,
		$author,$email,$site,$ip,$content,$rs)
	{
		
		# We handle only manual classification from the user
		if ($filter != 'manual')
		{ 

			return;
		}

		$spamFilter = new bayesian($this->core);

		if ($status == 'spam') { # the current action marks the comment as spam
			$spamFilter->retrain($author,$email,$site,$ip,$content, 1);
		} else {
			$spamFilter->retrain($author,$email,$site,$ip,$content, 0);
		}	
	}
}
?>
