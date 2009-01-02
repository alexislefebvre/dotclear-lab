<?php
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('OpenID','OpenID','^OpenID$',array('OpenidFormSend','OpenidProcess'));
 
class OpenidFormSend extends dcUrlHandlers
{
        public static function OpenidProcess($args)
        {
			if(!empty($_POST['openid_identifier']) && !empty($_POST['current_url'])){
				$session_id = session_id();
				if (empty($session_id)) { session_start(); }
				$scheme = 'http';
				if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') { $scheme .= 's'; }
				$scheme .= '://';
				global $core;
				$blog_url = $core->blog->url;
				$blog_url = str_replace($scheme.$_SERVER['SERVER_NAME'], '', $blog_url);
				$blog_url = str_replace('/index.php?', '', $blog_url);
				$_SESSION['blog_dir'] = $blog_url;
				$_SESSION['current_page'] = htmlspecialchars($_POST['current_url']);
				$_GET['openid_identifier'] = $_POST['openid_identifier'];
				require(dirname(__FILE__).'/library/examples/consumer/try_auth.php');
			}
			elseif(isset($_POST['openid_logout'])){
				$session_id = session_id();
				if (empty($session_id)) { session_start(); }
				session_destroy();
				header('Location: '.$_SERVER['HTTP_REFERER']);
			}
			elseif(empty($_GET)){
				self::p404();
			}
			else{
				require(dirname(__FILE__).'/library/examples/consumer/finish_auth.php');
			}
        }
}

class OpenidWidget
{
	public static function OpenidConnect(&$w)
	{
		global $core;
		$blog_url = $core->blog->url;
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') { $scheme .= 's'; }
		$scheme .= '://';
		$blog_url = str_replace($scheme.$_SERVER['SERVER_NAME'], '', $blog_url);
		define('dir', $blog_url.'OpenID');
		$session_id = session_id();
		if (empty($session_id)) { session_start(); }
		
		if(isset($_SESSION['openid_client'])) {
			return '<div id="openid">
			<h2>'.$w->title.'</h2>
			<form action="'.dir.'" method="post">
			<p>'.$core->formNonce().'<input type="hidden" name="openid_logout" value="1" /><input type="submit" value="'.__('Log out').'" class="deconnexion" /></p>
			</form></div>';
		}
		else{
			$scheme = 'http';
			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
				$scheme .= 's';
			}
			$scheme .= '://';
			$current_url = $scheme.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
    
			$openid_error = '';
			if(isset($_SESSION['openid_error'])) { 
				$openid_error = '<div class="error"><strong>'.__('Errors').' :</strong><br />'.$_SESSION['openid_error'].'</div>';			
			}

			return '
			<div id="openid">
			<h2>'.$w->title.'</h2>'.$openid_error.'
			<form action="'.dir.'" method="post">
			<p>
			'.$core->formNonce().'
			<input name="current_url" type="hidden" value="'.$current_url.'" />
			<input name="openid_identifier" id="openid_identifier" type="text" /><input value="Ok" type="submit" />
			</p>
			</form> 
			</div>';
		}
		
	}
	
	public static function OpenidSessionIf($attr, $content)
	{
		return '<?php
		$session_id = session_id();
        $error_mail = __("");
		if (empty($session_id)) { session_start(); }
		if(isset($_SESSION["openid_client"])) {
            if(isset($_SESSION["openid_client"]) AND isset($_SESSION["openid_mail"])){
			echo \'<div style="display: none;">\';
            }
            else{
                echo \'<div>\';
            }
            echo \'
        <p class="field"><input name="c_name" id="c_name" type="hidden" size="30" maxlength="255" value="\'.$_SESSION[\'openid_client\'].\'" />
        </p>\';
        	if(isset($_SESSION["openid_mail"])) {
				echo \'<p class="field">
        <input name="c_mail" id="c_mail" type="hidden" size="30" maxlength="255" value="\'.utf8_encode(trim($_SESSION[\'openid_mail\'])).\'" />
        </p>\';
			}
			else{
                echo \'<p>\'.$error_mail.\'</p>\';
				echo \'</p><p class="field">
        <input name="c_mail" id="c_mail" type="text" size="30" maxlength="255" />
        </p>\';
			}
			
			if(isset($_SESSION["openid_url"])) {
				echo \'<p class="field">
        <input name="c_site" id="c_site" type="hidden" size="30" maxlength="255" value="\'.$_SESSION[\'openid_url\'].\'" />
        </p></div>\'; 
			}
			else {
				echo \'<p class="field">
        <input name="c_site" id="c_site" type="text" size="30" maxlength="255" />
        </p></div>\'; 
			}
		}
		else{
		?>
		'.$content.'
		<?php
		}
		?>';
	}
}

$core->tpl->addBlock('OpenidSessionIf',
    array('OpenidWidget','OpenidSessionIf'));
?>