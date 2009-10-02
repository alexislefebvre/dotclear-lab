<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Popularity Contest.
# Copyright 2007,2009 Moe (http://gniark.net/)
#
# Popularity Contest is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Popularity Contest is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

	# default settings
	if (!(is_int($core->blog->settings->popularityContest_time_interval)))
	{
		$core->blog->settings->setNameSpace('popularitycontest');
		# Time interval in seconds between sends to Popularity Contest : 3 days
		$core->blog->settings->put('popularityContest_time_interval',
			(3*24*3600),'integer','Time interval in seconds between sends to Popularity Contest',true,true);
		# Popularity Contest last report : 30 days before -> will submit
		$core->blog->settings->put('popularityContest_last_report',
			(time()-(30*24*3600)),'integer','Popularity Contest last report (Unix timestamp)',true,true);
		# Hide plugins
		//$popularityContest_hidden_plugins = (!empty($_POST['hidden_plugins']))?base64_encode(serialize($_POST['hidden_plugins'])):array();
		$core->blog->settings->put('popularityContest_hidden_plugins',
			base64_encode(serialize(array(''))),'text','Hidden plugins',true,true);

		$popularityContest_sent = true;
	}

	# if the last report is "old"
	if ((time() - $core->blog->settings->popularityContest_last_report) > $core->blog->settings->popularityContest_time_interval)
	{
		$null = null;
		popularityContest::send($null,$null);
	}

?>