<?php

if ($fatal_error)
	return;

$nea_forms = array();

$nea_forms['list_actions'] = array(
	__('Delete')=>'delete',
	__('Make not evil')=>'mknotevil',
	__('Make evil')=>'mkevil',
	__('Enable')=>'enable',
	__('Disable')=>'disable');


/* FORMULAIRE - Configuration
--------------------------------------------------- */

$nea_forms['config'] = '
<form action="'.$p_url.'" method="post">
<fieldset class="two-cols"><legend>'.__('General configuration').'</legend>
<p class="col"><label class="required" title="'.__('Required field').'">'.__('Not evil ads identifiers:').' '.
	form::field(array('nea_identifiers'),40,false,html::escapeHTML($nea_settings['identifiers']),'','',true).'</label></p>
<div class="col">
	<p><label class="classic">'.form::checkbox(array('nea_default'),1,$nea_settings['default']).
		' '.__('Show ads by default').'</label></p>
	<p><label class="classic">'.form::checkbox(array('nea_nothome'),1,$nea_settings['nothome']).
		__('Do not show ads on home page by default').'</label></p>
	<p><label class="classic">'.form::checkbox(array('nea_notajax'),1,$nea_settings['notajax']).
		' '.__('Disable Ajax').'</label></p>
</div>
</fieldset>

<fieldset><legend>'.__('Cookie configuration').'</legend>
<p><label class="required" title="'.__('Required field').'">'.__('Cookie name').' '.
	form::field(array('nea_cookiename'),20,128,html::escapeHTML($nea_settings['cookiename'])).'</label></p>
<p><label class="classic">'.sprintf(__('Keep cookie %s days'),
	form::field(array('nea_cookiedays'),3,4,html::escapeHTML($nea_settings['cookiedays']))).'</label></p>
<p><label class="classic">'.form::checkbox(array('nea_easycookie'),1,$nea_settings['easycookie']).' '.
	__('Do not use cookie advanced configuration (cookie domain and path parameters)').'</label></p>
<div class="lockable two-cols">
	<p class="col"><label>'.__('Cookie path').' '.
		form::field(array('nea_cookiepath'),20,false,html::escapeHTML($nea_settings['cookiepath'])).'</label></p>
	<p class="col"><label>'.__('Cookie domain').' '.
		form::field(array('nea_cookiedome'),20,256,html::escapeHTML($nea_settings['cookiedome'])).'</label></p>
	<br class="clear" />
	<p class="form-note warn">'.__('Do not use path and domain parameters unless you know what you are doing').'</p>
</div>

<p><input type="submit" name="nea_action_config" value="'.__('Update configuration').'" />'.
(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';

/* FORMULAIRE - Liste des publicités
--------------------------------------------------- */

$nea_forms['list'] = '
<form action="'.$p_url.'" id="nea-form-list" method="post">
<table class="maximal">
<thead><tr>
<th colspan="2">'.__('Identifier').'</th>
<th>'.__('Title').'</th>
<th colspam="2">'.__('Status').'</th>
</tr></thead>
<tbody>
';

foreach ($nea_ads as $ad)
{
	$nea_forms['list'] .=
		'<tr class="line'.($ad['disable'] ? ' offline' : '').'" id="a'.$ad['identifier'].'">'.
		'<td class="nowrap">'.
			form::checkbox(array('nea_selected[]'),$ad['identifier'],
				(in_array($ad['identifier'],$nea_selected) ? true : false),
				'','',false,'title="'.__('Select this ad').'"').'</td>'.

		'<td class="nowrap">'.html::escapeHTML($ad['identifier']).'</td>'.
		
		'<td class="maximal">'.html::escapeHTML($ad['title']).'</td>'.
		
		'<td class="nowrap status">'.($ad['disable']
			? '<img src="images/check-off.png" alt="'.__('disabled').'" />'
			: ($ad['notevil']
				? '<img src="images/check-on.png" alt="'.__('not evil').'" />'
				: '<img src="images/check-wrn.png" alt="'.__('evil').'" />')).
		'</td>'.
		'<td class="nowrap status">'.
			'<a href="'.$p_url.'&amp;edit='.$ad['identifier'].'" title="'.__('Edit this ad').'">'.
			'<img src="images/edit-mini.png" alt="'.__('edit').'" /></a></td>'.
		"</tr>\n";
}

$nea_forms['list'] .= '</tbody>
</table>

<div class="two-cols">
<p class="col checkboxes-helpers">&nbsp;</p>
<p class="col right">'.__('Action for selected ads:').' '.
	form::combo(array('nea_action'),$nea_forms['list_actions']).
	(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').
	' <input type="submit" name="nea_action_fromlist" value="'.__('ok').'" /></p>
</div>
<br class="clear" />
</form>
<p class="status"><strong>'.__('Legend:').'</strong><br/>
<img src="images/plus.png" alt="'.__('preview').'" /> - '.__('Preview').'<br/>
<img src="images/edit-mini.png" alt="'.__('edit').'" /> - '.__('Edit').'<br/>
<img src="images/check-on.png" alt="'.__('not evil').'" /> - '.__('This ad is not evil').'<br/>
<img src="images/check-wrn.png" alt="'.__('evil').'" /> - '.__('This ad is evil').'<br/>
<img src="images/check-off.png" alt="'.__('disabled').'" /> - '.__('This ad is disabled').'</p>';


/* FORMULAIRE - Ajout / modification d'une publicité
--------------------------------------------------- */

$nea_forms['edit'] = '
<form action="'.$p_url.'" method="post">
<fieldset class="two-cols"><legend>'.
(empty($_REQUEST['edit']) ? __('Add a new ad')
	: sprintf(__('Edit ad \'%s\''),$_REQUEST['edit'])).'</legend>
<p class="col"><label class="required" title="'.__('Required field').'">'.__('HTML code:').'<br/>'.
	form::textArea(array('nea_htmlcode'),35,7,html::escapeHTML($nea_newad['htmlcode'])).'</label></p>
<div class="col">
	<p><label class="required" title="'.__('Required field').'">'.__('Identifier:').
		form::field(array('nea_identifier'),20,256,html::escapeHTML($nea_newad['identifier'])).'</label></p>
	<p><label>'.__('Title:').
		form::field(array('nea_title'),20,256,html::escapeHTML($nea_newad['title'])).'</label></p>
	<p><label>'.__('Extra DIV attributes:').
		form::field(array('nea_attr'),20,256,html::escapeHTML($nea_newad['attr'])).'</label></p>
</div>
<br class="clear" />

<p><label class="classic"> '.
	form::checkbox(array('nea_notevil'),1,$nea_newad['notevil']).
	__('This ad is not evil').'</label></p>
<p><label class="classic">'.
	form::checkbox(array('nea_nothome'),1,$nea_newad['nothome']).
	__('Do not show on home page').'</label></p>
<p><label class="classic">'.
	form::checkbox(array('nea_notajax'),1,$nea_newad['notajax']).
	__('Disable Ajax for this ad').'</label></p>
</fieldset>

<p><input type="submit" name="nea_action_'.(empty($_REQUEST['edit']) ? 'add' : 'edit').
	'" value="'.(empty($_REQUEST['edit']) ? __('Add') : __('Edit')).'" />'.
	(empty($_REQUEST['edit']) ? ''
		: form::hidden(array('edit'),html::escapeHTML($_REQUEST['edit']))).
		(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';

/* FORMULAIRE - Aide
--------------------------------------------------- */

$nea_forms['help'] = '
<p>'.sprintf(__('Unfortunately, help is not avaible in English. '.
'To read the up-to-date help in French, please go to the <a href="%s" title="Not Evil Ads online help">support webpage</a>.'),
"http://sacha.xn--phnix-csa.net/post/2007/08/15/%5BDotclear%5D-Plugin-Not-Evil-Ads-un-nouveau-systeme-de-publicite-pour-votre-blog").
'</p>';
?>
