<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors.
# All rights reserved.
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
# This file is part of dcFlickr, which inserts Flickr photos in a blog note
# Charles Delorme http://www.suricat.net/
# 

# Ajout d'une entree dans le menu de gauche de l'interface d'administration
# http://www.nikrou.net/post/2007/10/27/Creation-dun-plugin-dotclear-2-etape-2
$_menu['Plugins']->addItem(__('dcFlickr'),'plugin.php?p=dcFlickr','index.php?pf=dcFlickr/icon.png',
		preg_match('/plugin.php\?p=dcFlickr/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminPostHeaders',array('dcFlickrBehaviors','postHeaders'));
$core->addBehavior('adminRelatedHeaders',array('dcFlickrBehaviors','postHeaders'));

class dcFlickrBehaviors
{
	public static function postHeaders()
	{
		return
		'<script type="text/javascript" src="index.php?pf=dcFlickr/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.dcflickr.title','dcFlickr').
		"\n//]]>\n".
		"</script>\n";
	}
}
?>