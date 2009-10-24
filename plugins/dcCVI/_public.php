<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Xavier Plantefeve and contributors. All rights
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

$core->addBehavior('publicHeadContent',array('dcCviPublic','publicHeadContent'));

class dcCviPublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->cvi_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		$effect_parameters = $core->blog->settings->cvi_effect_parameters;
		$effect_parameters = @unserialize($effect_parameters);
		if (!is_array($effect_parameters)) {
			return;
		}
		echo
		'<script type="text/javascript">'."\n".
		'//<![CDATA['."\n".
			' $(document).ready(function(){'."\n".
			'$("div.post img").addClass("'.
			$core->blog->settings->cvi_effect.
			$effect_parameters[$core->blog->settings->cvi_effect].
			'");'."\n".
			'});'."\n".
		'//]]>'."\n".
		'</script>'."\n".
		'<script type="text/javascript" src="'.$url.'/js/'.$core->blog->settings->cvi_effect.'.js"></script>'."\n";
	}
}
?>