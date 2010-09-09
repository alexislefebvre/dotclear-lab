<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

?>
<html>
<head>
  <title><?php echo __('Grumph configuration'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=grumph/js/jquery.ajaxmanager.js').
			dcPage::jsLoad('index.php?pf=grumph/js/jquery.progressbar.min.js').
			dcPage::jsLoad('index.php?pf=grumph/js/refresh.js');
	echo 
	'<script type="text/javascript">'."\n".
	"//<![CDATA[\n".
		"dotclear.refresh_limit = ".$core->blog->settings->grumph->grumph_ajax_max_simul_updates."; \n".
		"dotclear.get_ids_limit = ".$core->blog->settings->grumph->grumph_ajax_max_simul_queries."; \n".
		"dotclear.msg.error_retrieve_post = '".html::escapeJS(__('Error while retrieving posts'))."';\n".
		"dotclear.msg.error_refresh_post = '".html::escapeJS(__('Error while refreshing posts'))."';\n".
	"\n//]]>\n".
	"</script>\n";	
  ?>
</head>
<body>

<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Grumph configuration').'</h2>';

$scan_method = array(__('Build resources for posts never scanned before') => 'new_only',__('Build resources for all posts with no exception') => 'all');

echo '<form action="#" method="post" id="scan-form" onsubmit="return false;">'.
	'<fieldset><legend>'.__('Build posts resources').'</legend>'.
	'<p><label class="classic">'.__('Select scan method :')."&nbsp;".
	form::combo('scan_method',$scan_method,'').'</label></p> '.

	'<input type="button" class="proceed" value="'.__('proceed').'" />'.
	'</fieldset></form>';
	
echo '<fieldset id="results" style="display:none;"><legend>'.__('Building posts resources...').'</legend>'.
	'<p> '.__('Number of posts found').' : <span id="result-nb">&nbsp;</span></p>'.
	'<p id="progress">'.__('Progress').'&nbsp : <span class="progressBar" id="pb"></span></p>'.
	'<form action="#" id="abort-form" onsubmit="return false;"><p><input type="button" id="abort" value="'.__('Abort processing').'"/></p></form>'.
	'<p id="error" class="error" style="display: none;"></p>'.
	'<p><a class="back" href="index.php">'.__('back').'</a></p>'.
	'</fieldset>';
?>

</body>
</html>
