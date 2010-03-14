<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear Galaxy plugin.
#
# Dotclear Galaxy plugin is free software: you can redistribute it
# and/or modify  it under the terms of the GNU General Public License
# version 2 of the License as published by the Free Software Foundation.
#
# Dotclear Galaxy plugin is distributed in the hope that it will be
# useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Dotclear Galaxy plugin.
# If not, see <http://www.gnu.org/licenses/>.
#
# Copyright (c) 2010 Mounir Lamouri.
# Based on the Dotclear metadata plugin by Olivier Meunier.
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

?>
<html>
<head>
  <title>Planets</title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=galaxy/style.css" />
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo;
<?php echo __('Planets'); ?></h2>

<?php
if (!empty($_GET['del'])) {
	echo '<p class="message">'.__('Planet has been successfully removed').'</p>';
}

$galaxy = new dcGalaxy($core);

$planets = $galaxy->getPlanets();
$planets->sort('planet_id_lower','asc');

$empty = true;
while ($planets->fetch())
{
	// TODO: this is not clean but ... working ;)
	$feed = $core->blog->url.'feed/planet/'.$planets->planet_id.'/';

	$cols .=
	'<tr class="line">'.
		'<td class="maximal"><a href="'.$p_url.
		'&amp;m=planet_posts&amp;planet='.rawurlencode($planets->planet_id).'">Planet '.$planets->planet_id.'</a></td>'.
		'<td class="nowrap"><strong>'.$planets->count.'</strong> '.__('entries').
		' <a href="'.$feed.'atom">atom</a>'.
		' <a href="'.$feed.'rss2">rss2</a>'.
		'</td>'.
	'</tr>';
	$empty = false;
}

$table = '<div class="col"><table class="planets">%s</table></div>';

if (!$empty)
{
	echo '<div class="two-cols">';
	printf($table, $cols);
	echo '</div>';
}
else
{
	echo '<p>'.__('No planets confgured on this blog.').'</p>';
}

?>

</body>
</html>

