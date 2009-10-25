<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

//Javatar image size
$javatar_size_combo = array(
	__('Small') => '32',
	__('Medium') => '64',
	__('Large') => '80',
	__('Extra Large') => '96',
);

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->javatar_active)) {
	try {
		$core->blog->settings->setNameSpace('javatar');

		// Javatars are not active by default
		$core->blog->settings->put('javatar_active',false,'boolean');
		$core->blog->settings->put('gravatar_default',false,'boolean');
		$core->blog->settings->put('javatar_custom_css','','string');
		$core->blog->settings->put('javatar_default_img','','string');
		$core->blog->triggerBlog();
		http::redirect(http::getSelfURI());
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$active = (boolean)$core->blog->settings->javatar_active;
$gravatar = (boolean)$core->blog->settings->gravatar_default;
$custom_css = (string)$core->blog->settings->javatar_custom_css;
$default_img = (string)$core->blog->settings->javatar_default_img;

if (!in_array($core->blog->settings->javatar_img_size,$javatar_size_combo)) {
	$javatar_size_combo[html::escapeHTML($core->blog->settings->javatar_img_size)] = html::escapeHTML($core->blog->settings->javatar_img_size);
}

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->setNameSpace('javatar');

		$active = (empty($_POST['active']))?false:true;
		$gravatar = (empty($_POST['gravatar']))?false:true;
		$custom_css = (empty($_POST['custom_css']))?'':html::sanitizeURL($_POST['custom_css']);
		$default_img = (empty($_POST['default_img']))?'':html::sanitizeURL($_POST['default_img']);
		$core->blog->settings->put('javatar_active',$active,'boolean');
		$core->blog->settings->put('gravatar_default',$gravatar,'boolean');
		$core->blog->settings->put('javatar_custom_css',$custom_css,'string');
		$core->blog->settings->put('javatar_default_img',$default_img,'string');
		$core->blog->settings->put('javatar_img_size',$_POST['javatar_img_size']);

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Javatars'); ?></title>
</head>

<body>
<?php
echo
'<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=javatar/icon_32.png) no-repeat;">'.
html::escapeHTML($core->blog->name).
'  &rsaquo; '.__('Javatars').'</h2>';
if (!empty($msg)) {
	echo '<p class="message">'.$msg.'</p>';
}

echo '<div id="sitemaps_options">';
echo '<form method="post" action="plugin.php?p=javatar">';
echo '<fieldset><legend>'.__('Plugin activation').'</legend>';
echo 
	'<p class="field">'.
		form::checkbox('active', 1, $active).
		'<label class=" classic" for="active">'.__('Enable Javatars').'</label></p>';
echo 
	'<p class="field">'.
		form::checkbox('gravatar', 1, $gravatar).
		'<label class=" classic" for="gravatar">'.__('Enable Gravatars compatibility').'</label></p>'.
		'<p class="form-note">'.__('If no Javatar, we try to show Gravatar.').'</p></fieldset>';

echo '<fieldset><legend>'.__('Options').'</legend>';
echo 
	'<label for="javatar_img_size" class="required" title="'.__('Required field').'">'.__('Javatar image size').'</label>'.
		'<p>'.form::combo('javatar_img_size',$javatar_size_combo,html::escapeHTML($core->blog->settings->javatar_img_size)).'</p>'.
		'<p class="form-note">'.__('This defines image size for Javatars.').'</p>';
		
echo '<h3>'.__('Custom parameters').'</h3>';
echo '<p>'.__('You can use a custom CSS by providing its location.').'<br /></p>';
echo 
	'<p><label>'.
		__('Custom CSS:').'</label>'.
			form::field('custom_css',40,128,$custom_css).
		'</p>';
echo '<p class="form-note">'.
		__('A location beginning with a / is treated as absolute, else it is treated as relative to the blog\'s current theme URL').
		'</p>';
echo '<p>'.__('You can use a custom default Javatar image by providing its location.').'<br /></p>';
echo 
	'<p><label>'.
		__('Custom default image:').'</label>'.
			form::field('default_img',40,128,$default_img).
		'</p>';
echo '<p class="form-note">'.
		__('The API of Presence Jabber works only with full path for the default picture.').
		'</p>';

echo '</fieldset>';

echo 
	'<p><input type="hidden" name="p" value="javatar" />'.
		$core->formNonce().
		'<input type="submit" name="saveconfig" accesskey="s" value="'.__('Save configuration').'"/>';
echo '</p></form></div>';

dcPage::helpBlock('javatar');
?>

</body>
</html>