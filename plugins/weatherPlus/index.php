<?php 
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Weather+ for Dotclear.
# Copyright (c) 2008 Gonzague Reydet. All rights
# reserved.
#
# Weather for Dotclear is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Weather for Dotclear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$default_tab = 'search-tab';
 
if (isset($_REQUEST['tab'])) {
	$default_tab = $_REQUEST['tab'];
}

$wp_id	= $core->blog->settings->weatherplus->wp_id;
$wp_key	= $core->blog->settings->weatherplus->wp_key;

if (isset($_POST['wp_id']))
{
	try
	{
		$wp_id = $_POST['wp_id'];
		$wp_key = $_POST['wp_key'];
		
		# Everything's fine, save options
		$core->blog->settings->addNamespace('weatherplus');
		$core->blog->settings->weatherplus->put('wp_id',$wp_id,'string','Partner ID');
		$core->blog->settings->weatherplus->put('wp_key',$wp_key,'string','License Key');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&upd=1&tab=config-tab');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>

<html>
<head>
  <title><?php echo __('Weather'); ?></title>
  <?php echo dcPage::jsPageTabs($default_tab); ?>
</head>
<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Weather').'</h2>';

if ($default_tab == 'config-tab') { /* l'onglet */
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Setting have been successfully updated.').'</p>';
	}
?>
	<a href="<?php echo($p_url.'&amp;tab=search-tab'); ?>" class="multi-part">
	<?php echo(__('Search a city')); ?></a>
	<div class="multi-part" id="config-tab" title="<?php echo __('Configuration'); ?>">
<?php		
echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('Weather.com XML Data Feed').'</legend>'.
'<p><label>'.__('Partner ID:').' '.
form::field('wp_id',30,512,html::escapeHTML($wp_id),'').'</label></p>'.
'<p><label>'.__('License Key:').' '.
form::field('wp_key',30,512,html::escapeHTML($wp_key),'').'</label></p>'.
'</fieldset>'.
'<p>'.$core->formNonce().'<input type="submit" value="'.__('Save').'" /></p>'.
'</form>';
?>
	</div>
<?php } else { /* le lien */ ?>
	<div class="multi-part" id="search-tab" title="<?php echo __('Search a city'); ?>">
	<form action="plugin.php" method="post">
	<p><label class="classic">
		<?php echo __('City').' : '.form::field('city',20,255,'','',2); ?>
	</label>
	<input type="submit" value="<?php echo __('Search'); ?>" />
	<input type="hidden" name="p" value="weatherPlus"/>
	<?php echo $core->formNonce(); ?></p>
	</form>
<?php 
	if (!empty($_POST['city'])) {
		$cities = dcWeather::searchCity($_POST['city']);
	
		if (!empty($cities->loc)) {
			echo __('Results found for').' '.$_POST['city'].' :
			<table>
			<thead>
			<tr>
	  			<th>'.__('City').'</th>
	  			<th>'.__('Id').'</th>
			</tr>
			</thead>
			<tbody>';
		
			foreach($cities->loc as $loc)
				echo '<tr><td>'.$loc.'</td><td>'.$loc['id'].'</td></tr>';
		
			echo '</tbody></table>';
		}
		else echo __('No result found for').' '.$_POST['city'];
	}
?>		
	</div>
	<a href="<?php echo($p_url.'&amp;tab=config-tab'); ?>" class="multi-part">
	<?php echo(__('Configuration')); ?></a>
<?php
}
dcPage::helpBlock('weatherPlus');
?>

</body>
</html>
