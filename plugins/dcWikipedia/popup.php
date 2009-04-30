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
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=dcWikipedia/js/popup.min.js'); ?>
	<style type="text/css">@import '<?php echo DC_ADMIN_URL; ?>?pf=dcWikipedia/style.min.css';</style>
</head>

<body>
<h2><?php echo __('Add a Wikipedia link'); ?></h2>

<?php

$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
	if (isset($all_langs[$rs->post_lang])) {
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	} else {
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($rs);

echo 
'<form id="dcwikipedia-lang-form" action="'.DC_ADMIN_URL.'plugin.php" method="get">'.
form::hidden('p','dcWikipedia').
form::hidden('popup','1').
form::hidden('value',$value).
'<p><label for="lang">'.__('Lang:').''.
form::combo('lang',$lang_combo,$lang).
'</label></p>'.
$core->formNonce().
'</form>';

if (count($parser->getItems()) == 0) {
	echo
	'<p>'.sprintf(
	__('No suggestion found for : %s in %s'),
	'<strong><q>'.$value.'</q></strong>',
	'<em>'.$all_langs[$lang].'</em>'
	).'</p>';
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
	'<input type="hidden" name="dcwikipedia_lang" value="'.$lang.'" />'.
	'</form>'.
	'<p><a class="button" href="#" id="dcwikipedia-insert-cancel">'.__('cancel').'</a> - '.
	'<strong><a class="button" href="#" id="dcwikipedia-insert-ok">'.__('insert').'</a></strong></p>'."\n";
}

?>

</body>
</html>