<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
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

//require dirname(__FILE__).'/../inc/admin/prepend.php';

dcPage::check('usage,contentadmin');
?>
<html><head>  <title><?php echo __('commentControl'); ?></title>
</head><body>
<?php
	echo "<h2>".__("Machines that have multiple author names in comments")."</h2>\n";
	$rs=comCtrl::getIPWhithMultipleAuthors(1);
	if (count($rs)){
		// there are some authors that publish with several names

	echo ("<table>\n");
	echo ("<tr><th>".__("Host")."</th><th>".__("nIDs")."</th><th>".__("Author names writing from this IP")."</th></tr>\n");
		foreach($rs as $ip_idcnt){
			$ip_url='plugin.php?p=comCtrl&amp;ip='.$ip_idcnt['comment_ip'] .'';

			echo "<tr class=\"line\"><td class=\"nowrap\"><a href=\"$ip_url\">".$ip_idcnt['comment_ip']."</a></td>";
			echo "<td class=\"nowrap\">".$ip_idcnt['count'] ."</td>";
			echo "<td class=\"maximal\">".comCtrl::getAKAList($ip_idcnt['comment_ip'])."</td></tr>\n";
		}
		echo "</table>\n";
	}
	else{
		echo "<p>".__('None to report...')."</p>\n";	
	}	
	
	
	echo "<h2>".__("Top Posters by host")."</h2>\n";
	$rs=comCtrl::getIPCommentCount();
	if (count($rs)){
		// there are some IPs that have published several comments

	echo ("<table>\n");
	echo ("<tr><th>".__("Host")."</th><th>".__("nComs")."</th><th>".__("Author names writing from this IP")."</th></tr>\n");
		foreach($rs as $ip_idcnt){
			$ip_url='plugin.php?p=comCtrl&amp;ip='.$ip_idcnt['comment_ip'] .'';

			echo "<tr class=\"line\"><td class=\"nowrap\"><a href=\"$ip_url\">".$ip_idcnt['comment_ip']."</a></td>";
			echo "<td class=\"nowrap\">".$ip_idcnt['comment_count'] ."</td>";
			echo "<td class=\"maximal\">".comCtrl::getAKAList($ip_idcnt['comment_ip'])."</td></tr>\n";
		}
		echo "</table>\n";
	}
	else{
		echo "<p>".__('None to report...')."</p>\n";	
	}	
?>

</body></html>
