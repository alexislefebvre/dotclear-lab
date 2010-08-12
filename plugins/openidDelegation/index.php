<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of openidDelegation, a plugin for Dotclear.
#
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
#
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


# Load providers
$providers = array();
require dirname(__FILE__).'/inc/lib.openidDelegation.providers.php';
$providers["custom"] = array("name" => __('Custom'),
                             "header" => '');

# Get settings
$openid_active = $core->blog->settings->openid_active;
$openid_provider = $core->blog->settings->openid_provider;
$openid_username = $core->blog->settings->openid_username;
$openid_header = $core->blog->settings->openid_header;


if (isset($_POST["openid_provider"])) {
	# modifications
	try {
		$openid_active = !empty($_POST["openid_active"]);
		$openid_provider = $_POST["openid_provider"];
		$openid_username = $_POST["openid_username"];
		$openid_header = $_POST["openid_header"];

		if (empty($_POST['openid_provider'])) {
			throw new Exception(__('No provider.'));
		}

		if ($openid_provider != "custom") {
			if (empty($openid_username)) {
				throw new Exception(__('No username.'));
			}
			$openid_header = sprintf($providers[$openid_provider]["header"],$openid_username);
		}

		$core->blog->settings->setNameSpace('openidDelegation');
		$core->blog->settings->put('openid_active',$openid_active,'boolean');
		$core->blog->settings->put('openid_provider',$openid_provider,'string');
		$core->blog->settings->put('openid_username',$openid_username,'string');
		$core->blog->settings->put('openid_header',$openid_header,'string');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Fill the combo box
$provider_names = array();
foreach ($providers as $pname => $pvalues) {
	$provider_names[$pvalues["name"]] = $pname;
}

?>
<html>
<head>
	<title><?php echo(__('OpenID Delegation')); ?></title>
	<style type="text/css">
	<?php
		if ($openid_provider == "custom") {
			echo "#openid-provider { display: none; }\n";
			echo "#openid-custom { display: block; }\n";
		} else {
			echo "#openid-provider { display: block; }\n";
			echo "#openid-custom { display: none; }\n";
		}
	?>
	</style>
	<script type="text/javascript">
		function show_field(provider) {
			if (provider == "custom") {
				$("div#openid-provider").hide();
				$("div#openid-custom").show();
			} else {
				$("div#openid-custom").hide();
				$("div#openid-provider").show();
			}
		}
		$(document).ready(function(){
			//var provider = $("#openid_provider").val();
			//show_field(provider);
			$("#openid_provider").change(function(event){
				show_field($(this).val());
			});
		});
	</script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('OpenID Delegation'); ?></h2>

	<p style="float:right;margin-right:3%;"><a href="http://flattr.com/thing/48108/Dotclear-OpenID-delegation-plugin" target="_blank" style="border:none">
	<img src="http://api.flattr.com/button/button-static-50x60.png" alt="Flattr this" title="Flattr this" border="0" /></a></p>

	<form method="post" id="openid" action="<?php echo($p_url); ?>">
		<p><?php echo $core->formNonce(); ?></p>

		<p><label class="classic"><?php 
			echo(form::checkbox('openid_active', 1,
			    (boolean) $openid_active).' '.
			    __('Use OpenID delegation')); ?></label></p>

		<p><label><?php echo(__('Provider:').
				form::combo('openid_provider',$provider_names,
				html::escapeHTML($openid_provider))); ?>
		</label></p>

		<div id="openid-provider">
			<p><label><?php echo(__('Username:').
					form::field('openid_username',30,255,
					$openid_username)); ?></p>
		</div>

		<div id="openid-custom">
			<p class="area"><label><?php echo(__('OpenID header:').
					form::textarea('openid_header',40,20,
					html::escapeHTML($openid_header))); ?></label></p>
		</div>

		<p><input type="submit" name="save"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>

<?php dcPage::helpBlock('openidDelegation');?>
</body>
</html>
