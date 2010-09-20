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

?>

<html>
<head>
  <title><?php echo __('Weather'); ?></title>
</head>
<body>
<h2><?php echo __('Weather'); ?></h2>
<form action="plugin.php" method="post">
	<p><label class="classic">
		<?php echo __('City').' : '.form::field('city',20,255,'','',2); ?>
	</label>
	<input type="submit" value="<?php echo __('Search city'); ?>" />
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
</body>
</html>
