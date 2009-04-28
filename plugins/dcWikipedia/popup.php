<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcWikipedia, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$value = isset($_GET['value']) ? rawurldecode($_GET['value']) : '';
$lang = isset($_GET['lang']) ? rawurldecode($_GET['lang']) : '';

$parser = dcWikipediaReader::quickParse('http://'.$lang.'.wikipedia.org/w/api.php?action=opensearch&format=xml&search='.rawurlencode($value),DC_TPL_CACHE);

?>

<html>
<head>
	<title><?php echo __('dcWikipedia'); ?></title>
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=dcWikipedia/js/popup.js'); ?>
	<style type="text/css">@import '<?php echo DC_ADMIN_URL; ?>?pf=dcWikipedia/style.css';</style>
</head>

<body>
<h2><?php echo __('Add a Wikipedia link'); ?></h2>

<?php

if (count($parser->getItems()) == 0) {
	echo '<p>'.sprintf(__('No suggestion found for : %s'),'<q>'.$value.'</q>').'</p>';
}
else {
	echo '<form id="dcwikipedia-insert-form" action="#" method="get">';
	foreach($parser->getItems() as $v => $k) {
		echo 
		'<div class="dcwikipedia_item">'.
		'<p class="wikipedia_value">'.
		form::radio(array('dcwikipedia_uri'),$k['uri']).
		$k['value'].'&nbsp;<a href="'.$k['uri'].
		'" class="wikipedia_uri">('.__('Read more...').')</a></p>'.
		'<p class="wikipedia_desc">'.$k['desc'].'</p>'.
		'</div>'."\n";
	}
	echo
	'<input type="hidden" name="dcwikipedia_value" value="'.$value.'" />'.
	'</form>'.
	'<p><a class="button" href="#" id="dcwikipedia-insert-cancel">'.__('cancel').'</a> - '.
	'<strong><a class="button" href="#" id="dcwikipedia-insert-ok">'.__('insert').'</a></strong></p>'."\n";
}

?>

</body>
</html>