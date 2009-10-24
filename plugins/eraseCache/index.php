<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 gerits aurelien for clashdesign All rights
# reserved.
#
# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$m_version = $core->plugins->moduleInfo('eraseCache','version');
#tableau des erreurs
$errors = array();
$error_cache =  '<p class="error">Votre dossier "cache" n\'est pas accessible</p>';
if (isset($_POST['delcache']))
{ 
	#Si aucune selection
	if (count($_POST['delete']) == 0)
	{
		#message au cas ou aucun dossier n'est supprimé
		$msg = __('No dirs deleted.');
	}
	else
	{
		foreach ($_POST['delete'] as $dir)
		{
			#boucle les dossiers et execute la fonction de suppression
			files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.$dir);
		}
		#Message de réussite et listes les dossiers supprimée
		$msg = __('Deleted dirs :').'<ul><li>'.
		implode('</li><li>',$_POST['delete']).'</li></ul>';
	}
}
if (isset($_POST['delcbtpl']))
{ 
	#Si aucune selection
	if (count($_POST['deletecbtpl']) == 0)
	{
		#message au cas ou aucun dossier n'est supprimé
		$msg = __('No dirs deleted.');
	}
	else
	{
		foreach ($_POST['deletecbtpl'] as $dir)
		{
			#boucle les dossiers et execute la fonction de suppression
			files::deltree(DC_TPL_CACHE.'/cbtpl/'.DIRECTORY_SEPARATOR.$dir);
		}
		#Message de réussite et listes les dossiers supprimée
		$msg = __('Deleted dirs :').'<ul><li>'.
		implode('</li><li>',$_POST['deletecbtpl']).'</li></ul>';
	}
}
if (isset($_POST['delcbfeed']))
{ 
	#Si aucune selection
	if (count($_POST['deletecbfeed']) == 0)
	{
		#message au cas ou aucun dossier n'est supprimé
		$msg = __('No dirs deleted.');
	}
	else
	{
		foreach ($_POST['deletecbtpl'] as $dir)
		{
			#boucle les dossiers et execute la fonction de suppression
			files::deltree(DC_TPL_CACHE.'/cbfeed/'.DIRECTORY_SEPARATOR.$dir);
		}
		#Message de réussite et listes les dossiers supprimée
		$msg = __('Deleted dirs :').'<ul><li>'.
		implode('</li><li>',$_POST['deletecbfeed']).'</li></ul>';
	}
}
#Chmod le dossier en 777 (experimental)
/*if (isset($_POST['perms'])){
	if(function_exists('chmod') && is_writable) {
		if(@chmod(DC_TPL_CACHE, 0755)!== false){
		$msg = __('Permissions on your file are now type 755');
		}else{
	throw new Exception(
		$error = __('Unable to change the type'));
		}
	}
}*/
#tableau des dossiers à supprimer
	if (is_writable && is_dir(DC_TPL_CACHE)) {
		if(@opendir(DC_TPL_CACHE) == true) {
			if($handle  = @opendir(DC_TPL_CACHE)){
				while (($filename = readdir($handle))!== false) {
				    $array[] =$filename;
				}closedir($handle);
			}
		$checkb = '<p class="select">'.__('To select the files with removed :').'</p>'.
	   '<form id="delcache" action="'.$p_url.'" method="post">';
		#boucle les dossiers, case a cocher
		foreach ($array as $value)
		{
			if (substr($value,0,1) != '.')
				{
					$checkb .= ('<p><label for="'.$value.'" class="classic">'.$value.': </label>'.form::checkbox(array('delete[]',$value),$value).'</p>');
				}
		}
		$checkb .= '<p>'.__('Click on the button to erase the temporary files').'</p>'
	.'<p><input type="submit" name="delcache" value="'.__('Remove the temporary files').'" />'.$core->formNonce().'</p>'.
	'</form>';
	}else {
		$error_cache;
	}
}else {
		$error_cache;
	}
	if (is_writable && is_dir(DC_TPL_CACHE.'/cbtpl/')) {
		if(@opendir(DC_TPL_CACHE.'/cbtpl/') == true) {
			if($cbtpl  = @opendir(DC_TPL_CACHE.'/cbtpl/')){
				while (($cbtplfilename = readdir($cbtpl))!== false) {
				    $arraycbtpl[] = $cbtplfilename;
				}closedir($cbtpl);
			}
		$checkcbtpl = '<p class="select">'.__('To select the files with removed :').'</p>'.
	   '<form id="delcache" action="'.$p_url.'" method="post">'.
	   '<ul class="ercbtpl">';
		#boucle les dossiers, case a cocher
		foreach ($arraycbtpl as $valuecbtpl)
		{
			if (substr($valuecbtpl,0,1) != '.')
				{
					$checkcbtpl .= ('<li><label for="'.$valuecbtpl.'" class="classic">'.$valuecbtpl.': </label>'.form::checkbox(array('deletecbtpl[]',$valuecbtpl),$valuecbtpl).'</li>');
				}
		}
		$checkcbtpl .= '</ul><div style="clear:left;"></div>';
		$checkcbtpl .= '<p>'.__('Click on the button to erase the temporary files').'</p>'
	.'<p><input type="submit" name="delcbtpl" value="'.__('Remove the temporary files').'" />'.$core->formNonce().'</p>'.
	'</form>';
	}else {
		$error_cache;
	}
}
if (is_writable && is_dir(DC_TPL_CACHE.'/cbfeed/')) {
		if(@opendir(DC_TPL_CACHE.'/cbfeed/') == true) {
			if($cbfeed  = @opendir(DC_TPL_CACHE.'/cbfeed/')){
				while (($cbfeedfilename = readdir($cbfeed))!== false) {
				    $arraycbfeed[] = $cbfeedfilename;
				}closedir($cbfeed);
			}
		$checkcbfeed = '<p class="select">'.__('To select the files with removed :').'</p>'.
	   '<form id="delcache" action="'.$p_url.'" method="post">'.
	   '<ul class="ercbfeed">';
		#boucle les dossiers, case a cocher
		foreach ($arraycbfeed as $valuecbfeed)
		{
			if (substr($valuecbfeed,0,1) != '.')
				{
					$checkcbfeed .= ('<li><label for="'.$valuecbfeed.'" class="classic">'.$valuecbfeed.': </label>'.form::checkbox(array('deletecbfeed[]',$valuecbfeed),$valuecbfeed).'</li>');
				}
		}
		$checkcbfeed .= '</ul><div style="clear:left;"></div>';
		$checkcbfeed .= '<p>'.__('Click on the button to erase the temporary files').'</p>'
	.'<p><input type="submit" name="delcbfeed" value="'.__('Remove the temporary files').'" />'.$core->formNonce().'</p>'.
	'</form>';
	}else {
		$error_cache;
	}
}
?>
<html>
<head>
<title><?php echo __('eraseCache')?></title>;
<?php
echo '<link rel="stylesheet" type="text/css" href="index.php?pf=eraseCache/eraseCache.css" />';
if (!empty($_GET['part'])) {
	$part = $_GET['part'] == 'about' ? 'about' :
				'form';
} else {
	$part = 'form';
}
echo dcPage::jsPageTabs($part);
?>
<script type="text/javascript">
  //<![CDATA[
  	<?php 
  	echo dcPage::jsVar('dotclear.msg.confirm_cleanconfig_delete',
  	__('Are you sure you want to remove the temporary files?')); 
  	echo dcPage::jsVar('dotclear.msg.confirm_chmod',
  	__('Are you sure you want to change permissions ?')); 
  	?>
  	$(function() {
		$('input[@name="delcache"]').click(function() {
			return window.confirm(dotclear.msg.confirm_cleanconfig_delete);
		});
		$('input[@name="delcbtpl"]').click(function() {
			return window.confirm(dotclear.msg.confirm_cleanconfig_delete);
		});
		$('input[@name="delcbfeed"]').click(function() {
			return window.confirm(dotclear.msg.confirm_cleanconfig_delete);
		});
		$('input[@name="perms"]').click(function() {
			return window.confirm(dotclear.msg.confirm_chmod);
		});
	});
  //]]>
</script>
<script type="text/javascript">
$(document).ready(function(){
		
	$("#viewcbtpl a").addClass("hideul");
	$("#viewcbtpl a").click(function(){
 	 var answer = $('#allCbtpl');
        if (answer.is(":visible")) {
            answer.slideUp("fast");
            $(this).removeClass("showul");
			$(this).addClass("hideul");
        } else {
            answer.slideDown("slow");
            $(this).removeClass("hideul");
			$(this).addClass("showul");
        }
 	});
	})
</script>
</head>
<body>
<?php
#onglet contenant le formulaire de suppression des dossiers du cache
echo '<h2>'.html::escapeHTML($core->blog->name).'&gt;'.__('eraseCache').'</h2>'.
'<div id="form" title="'.__('Form').'" class="multi-part">';
#les messages envoyé par le plugin
if (!empty($msg)) {echo '<div class="message">'.$msg.'</div>';}
if (!empty($error)) {echo '<div class="error"><strong>'.__('Error:').'</strong> '.$error.'</div>';}
	echo '<h3 class="stitle">'.__('Plugin eraseCache').'</h3>'.
	'<p class="version">'.__('version').'<strong> '.$m_version.'</strong>'.'</p>'.
	'<p>'.__('Allows to erase the temporary files of your blog').'</p>';
	
	echo '<fieldset>'.
		'<legend>'.__('Restriction').'</legend>'.
		'<p>'.__('Change the permissions in the menu "users"').'</p>'.
	'</fieldset>'.
	'<fieldset>'.
		'<legend class="important">'.__('Important !').'</legend>'.
		'<p>'.__('Beware of files you delete, choose not support any of the system files.').'</p>'.
		'<strong>Informations</strong>'.
		'<p>'.__('The default file cache are:').'</p>'.
		'<ul class="dirs"><li>cbtpl</li><li>cbfeed</li></ul>'.
	'</fieldset>'.
	'<fieldset>'.
		'<legend>'.__('Form').'</legend>';
		!file_exists(DC_TPL_CACHE) ? print $error_cache : true;
		!@opendir(DC_TPL_CACHE) ? print $error_cache: true;
		//!file_exists(DC_TPL_CACHE.'/cbtpl/') ? print $error_cache : true;
		//!@opendir(DC_TPL_CACHE.'/cbtpl/') ? print $error_cache: true;
		if(count($array) <= 2){
			echo '<p><img src="index.php?pf=eraseCache/img/message-warn.png" alt="file empty" width="16px" height="16px" />'.__('The dirs is empty').'</p>';
		}
		if(count($array) >= 3) {
			echo $checkb;
		}
		
	echo '</fieldset>';
?>
<div id="viewcbtpl">
			<a id="linksCbtpl" href="#cbtpl">Voir tous les dossiers</a>
		</div>
		<div id="allCbtpl">
		<?php
		echo '<fieldset>'.
		'<legend>'.__('Form').' cbtpl</legend>';
		if(file_exists(DC_TPL_CACHE.'/cbtpl/')){
			if (count($arraycbtpl) >=1) {
				echo $checkcbtpl;
			}
		}else{
			echo '<p class="notgenere">le dossier cbtpl n\'a pas encore été regénéré</p>';
		}
		echo '</fieldset>';
		echo '<fieldset>'.
		'<legend>'.__('Form').' cbfeed</legend>';
		if(file_exists(DC_TPL_CACHE.'/cbfeed/')){
			if (count($arraycbfeed) >=1) {
				echo $checkcbfeed;
			}
		}else{
			echo '<p class="notgenere">le dossier cbfeed n\'a pas encore été regénéré</p>';
		}
		echo '</fieldset></div></div>';
#Deuxième onglet pour afficher quelques informations
echo '<div id="about" title="'.__('About').'" class="multi-part">'.
'<h3>'.__('Made by:').'</h3>'.
      '<ol>'.
      	'<li><a href="http://www.clashdesign.net">Gtraxx</a></li>'
     .'</ol>'.
     '<h3>'.__('Thanks').'</h3>'.
     '<ol>'.
      	'<li><a href="http://gniark.net/">Moe</a></li>'.
      	'<li><a href="http://web.saymonz.net/">Simon</a></li>'
     .'</ol>'.
     '<h3>'.__('More information on the plugin:').'</h3>';
?>
   <div id="link_site">
	   <a href="http://www.clashdesign.net">
	      <img src="index.php?pf=eraseCache/img/clashdesign.png" alt="clashdesign" />
	   </a>&nbsp;
       <a href="http://www.dotclear.net">
       	<img src="index.php?pf=eraseCache/img/plugin_dotclear.png" alt="plugin dotclear" />
      </a>
    </div><br />
<?php
echo '<h3>'.__('Support and Update').'</h3><p>'.
      __('Please go to:').'<a href="http://www.clashdesign.net">http://www.clashdesign.net</a></p>'.
       '<p><a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.0/be/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-nd/2.0/be/88x31.png" /></a>
      <br /><span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type">
      EraseCache </span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.clashdesign.net" property="cc:attributionName" rel="cc:attributionURL">
      Clashdesign</a> est mis &#224; disposition selon les termes de la <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.0/be/">
      licence Creative Commons Paternit&#233;-Pas d\'Utilisation Commerciale-Pas de Modification 2.0 Belgique</a>.<br />Bas&#233;(e) sur une oeuvre &#224;</p>
      <p>Les autorisations au-del&#224; du champ de cette licence peuvent &#234;tre obtenues &#224; <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.clashdesign.net" rel="cc:morePermissions">
      http://www.clashdesign.net</a></p>.'.
'</div>';
#troisième onglet pour afficher la FAQ
echo '<div id="faq" title="'.__('FAQ').'" class="multi-part">'.
'<h2>'.__('F.A.Q').'</h2>'.
'<h4 class="titlefaq">'.__('I receive an error message when I am in the administration').'</h4>'.
		'<ul class="olfaqerror"><li>Warning: scandir(../dotclear2/tmp) [function.scandir]: failed to open dir: No such file or directory in</li>'
		.'<li>Warning: scandir() [function.scandir]: (errno 2): No such file or directory in votreracine/plugins/eraseCache/index.php on line 100</li>'
		.'<li>Warning: Invalid argument supplied for foreach() in votreracine/plugins/eraseCache/index.php on line 102</li>'
.'</ul>'.
'<h3 class="solution">'.__('Solutions').'</h3>'.
'<ul class="olfaq">'.
'<li>'.__('The permissions of writing are not correct (700 minimum)').'</li>'.
'</ul>'.
'<h4 class="titlefaq">'.__('I did not file cbtpl and cbfeed in the list').'</h4>'.
'<h3 class="solution">'.__('Solutions').'</h3>'.
'<ul class="olfaq"><li>'.__('check your file "config.php"').'</li>'.
'<li>'.__('The cache is empty').'</li>'.
'</ul>'.
'<p><a href="http://www.clashdesign.net/blog/index.php/post/2007/08/02/Plugin-eraseCache"><img src="index.php?pf=eraseCache/img/bug.png" alt="bug" width="16px" height="16px" />Signaler un bug</a></p>'.
'</div>';
?>


</body>
</html>