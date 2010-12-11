<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of footnotesToolbar, a plugin for Dotclear.
#
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
#
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

$footnotes_supported_modes = array("none", "float", "under");

# Get settings
$footnotes_mode = $core->blog->settings->footnotesToolbar->footnotes_mode;

if (isset($_POST["save"])) {
	# modifications
	try {
		$footnotes_mode = $_POST["footnotes_mode"];

		if (empty($_POST['footnotes_mode'])) {
			throw new Exception(__('No footnotes mode.'));
		}

		$core->blog->settings->setNameSpace('footnotesToolbar');
		$core->blog->settings->put('footnotes_mode',$footnotes_mode,'string');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo(__('Footnotes toolbar')); ?></title>
	<style type="text/css">
		ul.nobullet { list-style-type: none; }
		ul.nobullet li { margin: 1em 0 2em 0;}
		ul.nobullet li input { vertical-align: middle; }
		img.preview {
			border: 1px solid black;
			margin-left: 2em;
			display: block;
		}
	</style>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Footnotes toolbar'); ?></h2>

	<?php
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
	}
	?>

	<p style="float:right;margin-right:3%">
          <a href="http://flattr.com/thing/48219/Dotclear-Footnotes-toolbar-plugin" target="_blank"
          style="border:none"><img src="http://api.flattr.com/button/button-static-50x60.png"
          alt="Flattr this" title="Flattr this" border="0" /></a>
     </p>

	<form method="post" action="<?php echo($p_url); ?>">
		<p><?php echo $core->formNonce(); ?></p>

		<p><?php echo(__('Footnotes display mode:')); ?></p>
		<ul class="nobullet">
			<?php
			foreach ($footnotes_supported_modes as $mode) {
				$is_default = $mode == $footnotes_mode ? true : false;
				echo (
					'<li><label class="classic">'.
					form::radio(array('footnotes_mode'),$mode,$is_default).
					html::escapeHTML(__('Footnotes_mode_'.$mode)).
					'. '.__('Example:').
					'<img src="index.php?pf=footnotesToolbar/footnotes_mode_'.$mode.'.png" class="preview" />'.
					'</label>'.
					'</li>'
					);
			}
			?>
		</ul>

		<p><input type="submit" name="save"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>

<?php //dcPage::helpBlock('footnotesToolbar');?>
</body>
</html>
