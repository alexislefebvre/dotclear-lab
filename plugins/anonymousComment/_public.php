<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of anonymousComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('publicHeadContent',
	array('anonymousCommentBehaviors','publicHeadContent'));
$core->addBehavior('publicCommentFormBeforeContent',
	array('anonymousCommentBehaviors','publicCommentFormBeforeContent'));

class anonymousCommentBehaviors
{
	public static function publicHeadContent(&$core,$_ctx)
	{
		if (!$core->blog->settings->anonymous_active) { return; }
		// print the headers
		echo "\n<!-- Anonymous comments -->\n";
		echo (
			'<script type="text/javascript">'.
			'//<![CDATA['."\n".
			'var anonymous_name = "'.
			html::escapeHTML($core->blog->settings->anonymous_name).
			'";'."\n".
			'var anonymous_mail = "'.
			html::escapeHTML($core->blog->settings->anonymous_email).
			'";'."\n".
			'//]]>'.
			'</script>'.
			'<script type="text/javascript" src="'.$core->blog->getQmarkURL().
			'pf=anonymousComment/anonymousComment.js'.'"></script>'."\n"
			);
	}

	public static function publicCommentFormBeforeContent(&$core,$_ctx)
	{
		if (!$core->blog->settings->anonymous_active) { return; }
		echo ('<p class="field"><label for="c_anonymous">'.
			 __("Anonymous comment:")."</label>\n".
		      '<input name="c_anonymous" id="c_anonymous" type="checkbox" />'.
			 "</p>\n");
	}
}

?>