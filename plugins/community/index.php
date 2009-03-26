<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Var initialisation
$p_url 		= 'plugin.php?p=community';
$default_tab 	= !empty($_GET['tab']) ? html::escapeHTML($_GET['tab']) : 'standby';
$default_tab 	= !empty($_GET['saveconfig']) ? 'settings' : $default_tab;
$page		= !empty($_GET['page']) ? (integer)$_GET['page'] : 1;
$nb_per_page 	= 10;
$q_standby	= !empty($_GET['q-standby']) ? trim(html::escapeHTML($_GET['q-standby'])) : '';
$q_user		= !empty($_GET['q-user']) ? trim(html::escapeHTML($_GET['q-user'])) : '';
$msg			= array();
$community	= new community($core);

$pages['user'] = $pages['standby'] = $pages['group'] = 1;
$pages[$default_tab] = $page;

# Standby users actions
if (isset($_POST['action']) && $_POST['type'] == 'standby') {
	$standby = $community->getStandbyUsers();
	# Enable standby users
	if ($_POST['action'] == 'enable') {
		foreach ($_POST['standby_id'] as $k => $v) {
			if (array_key_exists($v,$standby)) {
				$msg[] = sprintf(__('User: %s successfully enabled'),$v);
				$community->register($standby[$v]['key']);
			}
			else {
				$core->error->add(sprintf(__('Impossible to enable user: %s'),$v));
			}
		}
	}
	# Delete standby users
	elseif ($_POST['action'] == 'delete') {
		foreach ($_POST['standby_id'] as $k => $v) { //echo $k.' - '.$v; exit;
			if (array_key_exists($v,$standby)) {
				$msg[] = sprintf(__('User: %s successfully deleted'),$v);
				$community->delete($v);
			}
			else {
				$core->error->add(sprintf(__('Impossible to delete user: %s'),$v));
			}
		}
	}
}

# Saves setup
if (!empty($_POST['save']))
{
	$err = array();

	if (isset($_POST['community_enabled'])) {
		if (empty($_POST['community_admin_email'])) {
			$err[] = __('You have to put a email address');
		}
	}
	if (!empty($_POST['community_admin_email']) && !text::isEmail($_POST['community_admin_email'])) {
		$err[] = __('You have to put a valid email address');
	}

	if (count($err) == 0) {
		$core->blog->settings->setNamespace('community');
		$core->blog->settings->put('community_enabled',html::escapeHTML($_POST['community_enabled']),'string');
		$core->blog->settings->put('community_moderated',html::escapeHTML($_POST['community_moderated']),'string');
		$core->blog->settings->put('community_admin_email',html::escapeHTML($_POST['community_admin_email']),'string');
		$msg[] = __('Configuration successfully updated');
	}
	else {
		foreach ($err as $k => $v) {
			$core->error->add($v);
		}
	}
}

/**
 * This function returns the search form
 * 
 * @param	string	mode
 *
 * @return	string
 */
function searchForm($mode)
{
	global $core, $p_url, $q_plugins, $q_themes;

	$q = (empty($q_plugins)) ? $q_themes : $q_plugins;

	return
		'<fieldset><legend>'.__('Search options').
		'</legend>'.
		'<form method="get" action="'.$p_url.'?p=daInstaller">'.
		'<p><input type="hidden" name="p" value="daInstaller" />'.
		'<input type="hidden" name="tab" value="'.$mode.'" />'.
		'<label class="classic">'.__('Query:').' '.
		form::field('q-'.$mode,30,255,html::escapeHTML($q)).
		'</label> '.
		'<input type="submit" value="'.__('ok').'" /></p>'.
		'</form>'.
		'</fieldset>';
}

$s_u_rs = $community->getStandbyUsers();
$s_u_nb = count($s_u_rs); 
$s_u_rs = staticRecord::newFromArray($s_u_rs);
$s_u_list = new communityStandbyList($core,$s_u_rs,$s_u_nb);

?>
<html>
<head>
	<title><?php echo __('Community'); ?></title>
	<?php echo dcPage::jsModal(); ?>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=community/js/_community.js'); ?>
	<style type="text/css">@import '<?php echo DC_ADMIN_URL; ?>?pf=community/style.css';</style>
</head>
<body>
<h2><?php echo __('Community'); ?></h2>
<p><?php echo __('Create your blog community'); ?></p>

<?php
$res = '';
if (!empty($msg)) {
	foreach ($msg as $k => $v) {
		$res .= '<li>'.$v.'</li>';
	}
}
if (!empty($res)) {
	echo '<div class="message">'.__('Message(s)').'<ul>'.$res.'</ul></div>';
}
?>

<!-- Standby users -->
<div class="multi-part" id="standby" title="<?php echo __('Standby users'); ?>">
	<h3><?php echo $s_u_nb > 0 ? __('Users standby for registration') : __('No standby request'); ?></h3>
	<?php $s_u_list->display($page['standby'],$nb_per_page,'standby',$p_url,$q_standby); ?>
</div>

<!-- Configuration -->
<div class="multi-part" id="settings" title="<?php echo __('Settings'); ?>">
	<h3><?php echo __('Community settings'); ?></h3>
	<form method="post" action="<?php echo $p_url.'&amp;tab=settings'; ?>">
		<p class="field"><label class="classic" for="community_enabled">
		<?php echo __('Enable community for this blog:'); ?></label>
		<?php echo form::checkbox('community_enabled',1,$core->blog->settings->community_enabled); ?></p>
		<p class="field"><label class="classic" for="community_moderated">
		<?php echo __('Moderate new subscription of community for this blog:'); ?></label>
		<?php echo form::checkbox('community_moderated',1,$core->blog->settings->community_moderated); ?></p>
		<p class="field"><label class="classic" for="community_admin_email">
		<?php echo __('Enter email address of community admin (will receive notifications about community):'); ?></label>
		<?php echo form::field('community_admin_email',30,255,$core->blog->settings->community_admin_email); ?></p>
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="save" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>

</body>
</html>