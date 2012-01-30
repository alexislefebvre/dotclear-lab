<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Contact, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$core->url->register('contact','contact','^contact$',array('tplContact','load'));
$core->url->register('contact_to','contact','^contact/(.+)$',array('tplContact','load'));

class tplContact extends dcUrlHandlers
{
	public static function contactWidget($w) {
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		} else {
			$title = $w->title ? html::escapeHTML($w->title) : __('Contact');
			$subtitle = $w->subtitle ? html::escapeHTML($w->subtitle) : __('Contact me!');
			$link = $core->blog->url.$core->url->getBase("contact");
			
			
			if ($w->usesubtitle && $w->usesubtitle == true) {
				$title = str_replace('%I', '<img src="'.$core->blog->url.'?pf=contact/icon.png" alt="" style="margin: 0 3px; vertical-align: middle;" />', $title);
				$subtitle = str_replace('%I', '</a><img src="'.$core->blog->url.'?pf=contact/icon.png" alt="" style="margin: 0 3px; vertical-align: middle;" /><a href="'.$link.'">', $subtitle);
				
				return
				'<div id="contact">'.
				'<h2>'.$title.'</h2>'.
				'<ul><li><a href="'.$link.'">'.$subtitle.'</a></li></ul>'.
				'</div>';
			} else {
				$title = str_replace('%I', '</a> &nbsp;<img src="'.$core->blog->url.'?pf=contact/icon.png" alt="" width="16" height="16" style="margin: 0 3px; vertical-align: middle;" />&nbsp; <a href="'.$link.'">', $title);
				return
				'<div id="contact">'.
				'<h2><a href="'.$link.'">'.$title.'</a></h2>'.
				'</div>';
			}
		}
	}
	
	public static function load($args) {
		global $core;
		
		if ($core->blog->id != 'default' && !file_exists(dirname(__FILE__).'/contact.'.$core->blog->id.'.html')) {
			copy(dirname(__FILE__).'/contact.default.html', dirname(__FILE__).'/contact.'.$core->blog->id.'.html');
		}
		
		$GLOBALS['contact'] = array();
		
		$GLOBALS['contact']['recipients'] = unserialize($core->blog->settings->contact_recipients);
		$GLOBALS['contact']['formconfig'] = unserialize(str_replace('\\n', "\n", str_replace('\\r', "\r", $core->blog->settings->contact_formconfig)));
		
		if (empty($GLOBALS['contact']['formconfig']) || empty($GLOBALS['contact']['recipients'])) {
			$GLOBALS['contact']['errors'][] = __('The contact page is not set up yet. Please wait the webmaster do it.');
		} else {
			$GLOBALS['contact']['formvalues'] = array();
			
			// Set the recipient if given in the url
			if (!empty($args)) {
				foreach ($GLOBALS['contact']['recipients'] as $k => $v) {
					if ($args == $v['name']) {
						$GLOBALS['contact']['formvalues']['contact_recipient'] = $k;
						break;
					}
				}
			}
			if (!isset($GLOBALS['contact']['formvalues']['contact_recipient'])) {
				$GLOBALS['contact']['formvalues']['contact_recipient'] = '';
			}
			
			// Load default values from cookie
			if (!empty($_COOKIE['comment_info'])) {
				$c_cookie = unserialize($_COOKIE['comment_info']);
				$GLOBALS['contact']['formvalues']['contact_name'] = !empty($c_cookie['name']) ? $c_cookie['name'] : '';
				$GLOBALS['contact']['formvalues']['contact_email'] = !empty($c_cookie['mail']) ? $c_cookie['mail'] : '';
				unset($c_cookie);
			} else {
				$GLOBALS['contact']['formvalues']['contact_name'] = '';
				$GLOBALS['contact']['formvalues']['contact_email'] = '';
			}
			$GLOBALS['contact']['formvalues']['contact_subject'] = '';
			$GLOBALS['contact']['formvalues']['contact_body'] = '';
			
			$GLOBALS['contact']['formvalues']['contact_additionnal'] = array();
			if (!empty($GLOBALS['contact']['formconfig']['additionnal'])) {
				foreach ($GLOBALS['contact']['formconfig']['additionnal'] as $k => $v) {
					$GLOBALS['contact']['formvalues']['contact_additionnal'][$k] = $v['ini'];
				}
			}
			
			if (isset($_POST['contact']['preview']) || isset($_POST['contact']['send'])) {
				$GLOBALS['contact']['formvalues']['contact_recipient'] =
					$GLOBALS['contact']['formvalues']['contact_recipient'] !== '' ? $GLOBALS['contact']['formvalues']['contact_recipient'] :
					(!empty($_POST['contact']['recipient']) ? $_POST['contact']['recipient'] : 0);
				$GLOBALS['contact']['formvalues']['contact_name'] = !empty($_POST['contact']['name']) ? $_POST['contact']['name'] : '';
				$GLOBALS['contact']['formvalues']['contact_email'] = !empty($_POST['contact']['email']) ? $_POST['contact']['email'] : '';
				$GLOBALS['contact']['formvalues']['contact_subject'] = !empty($_POST['contact']['subject']) ? $_POST['contact']['subject'] : '';
				$GLOBALS['contact']['formvalues']['contact_body'] = !empty($_POST['contact']['body']) ? $_POST['contact']['body'] : '';
				if (!empty($GLOBALS['contact']['formconfig']['additionnal'])) {
					foreach ($GLOBALS['contact']['formconfig']['additionnal'] as $k => $v) {
						$GLOBALS['contact']['formvalues']['contact_additionnal'][$k] = $v['type'] == 'text' || $v['type'] == 'select' ? (isset($_POST['contact']['additionnal'][$k]) ? $_POST['contact']['additionnal'][$k] : $v['ini']) : ($v['type'] == 'checkbox' ? isset($_POST['contact']['additionnal'][$k]) : '');
					}
				}
				
				if (isset($_POST['contact']['preview'])) {
					$temp = '';
					if (!empty($GLOBALS['contact']['formconfig']['additionnal'])) {
						foreach ($GLOBALS['contact']['formconfig']['additionnal'] as $k => $v) {
							$temp .= '| '.$v['caption'].'   ';
							$temp .= $v['type'] == 'text' ? $GLOBALS['contact']['formvalues']['contact_additionnal'][$k] : ($v['type'] == 'select' ? $v['options'][$GLOBALS['contact']['formvalues']['contact_additionnal'][$k]] : ($v['type'] == 'checkbox' ? ($GLOBALS['contact']['formvalues']['contact_additionnal'][$k] ? __('Yes') : __('No')) : ''));
							$temp .= "\n";
						}
					}
					
					$GLOBALS['contact']['preview'] =
						'<strong>From:</strong> '.(empty($_POST['contact']['email']) ? html::escapeHTML($_POST['contact']['name']) : (html::escapeHTML($_POST['contact']['name']).' &lt;'.html::escapeHTML($_POST['contact']['email']).'&gt;')).'<br />'.
						'<strong>To:</strong> '.html::escapeHTML($GLOBALS['contact']['recipients'][$_POST['contact']['recipient']]['name']).'<br />'.
						'<strong>Subject:</strong> '.html::escapeHTML(!empty($_POST['contact']['subject']) ? $_POST['contact']['subject'].' (Contact '.$core->blog->name.')' : 'Contact '.$core->blog->name).'<br />'.
						'<br />'.
						(!empty($temp) ? nl2br(html::escapeHTML($temp)).'<br />' : '').
						nl2br(html::escapeHTML($_POST['contact']['body']));
					
				} elseif (isset($_POST['contact']['send'])) {
					$GLOBALS['contact']['errors'] = array();
					
					if ( (empty($_POST['contact']['name']) && $GLOBALS['contact']['formconfig']['name_required'])
						|| (empty($_POST['contact']['email']) && $GLOBALS['contact']['formconfig']['email_required'])
						|| (empty($_POST['contact']['subject']) && $GLOBALS['contact']['formconfig']['subject_required'])
						|| (empty($_POST['contact']['body']) && $GLOBALS['contact']['formconfig']['body_required']) ) {
						$GLOBALS['contact']['errors'][] = __('Some needed fields are empty. Refresh the page by pressing the F5 key on your keyboard.');
					} elseif ($GLOBALS['contact']['formconfig']['antispam_enabled'] && strtoupper($_POST['contact']['antispam']) != file_get_contents(dirname(__FILE__).'/antispam.key.txt')) {
						$GLOBALS['contact']['errors'][] = __('The anti-spam code is incorrect. Refresh the page by pressing the F5 key on your keyboard.');
					} elseif (!empty($_POST['contact']['email']) && !ereg('([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*@([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*\.([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*', $_POST['contact']['email'])) {
						$GLOBALS['contact']['errors'][] = __('The given email is invalid.');
					} else {
						// Make a new anti-spam code
						self::ContactFormAntispamImg(array(), array());
						
						require dirname(__FILE__).'/class.mime_mail.php';
						
						$_POST['contact']['recipient'] = isset($_POST['contact']['recipient']) ? $_POST['contact']['recipient'] : key($GLOBALS['contact']['recipients']);
						
						$GLOBALS['contact']['email'] = new mime_mail();
						$GLOBALS['contact']['email']->mimemail = $GLOBALS['contact']['formconfig']['mimemail'];
						$GLOBALS['contact']['email']->addRecipient($GLOBALS['contact']['recipients'][$_POST['contact']['recipient']]['email'], $GLOBALS['contact']['recipients'][$_POST['contact']['recipient']]['name']);
						$GLOBALS['contact']['email']->sender_name = (!empty($_POST['contact']['name']) ? $_POST['contact']['name'] : __('Visitor'));
						$GLOBALS['contact']['email']->sender_email = (!empty($_POST['contact']['email']) ? $_POST['contact']['email'] : '');
						$GLOBALS['contact']['email']->subject = (!empty($_POST['contact']['subject']) ? $_POST['contact']['subject'].' (Contact '.$core->blog->name.')' : 'Contact '.$core->blog->name);
						$GLOBALS['contact']['email']->body = (!empty($_POST['contact']['name']) ? $_POST['contact']['body'] : '');
						
						$temp = '';
						if (!empty($GLOBALS['contact']['formconfig']['additionnal'])) {
							foreach ($GLOBALS['contact']['formconfig']['additionnal'] as $k => $v) {
								$temp .= '| '.$v['caption'].'   ';
								$temp .= $v['type'] == 'text' ? $GLOBALS['contact']['formvalues']['contact_additionnal'][$k] : ($v['type'] == 'select' ? $v['options'][$GLOBALS['contact']['formvalues']['contact_additionnal'][$k]] : ($v['type'] == 'checkbox' ? ($GLOBALS['contact']['formvalues']['contact_additionnal'][$k] ? __('Yes') : __('No')) : ''));
								$temp .= "\n";
							}
						}
						if (!empty($temp)) {
							$GLOBALS['contact']['email']->body = $temp."\n".$GLOBALS['contact']['email']->body;
						}
						
						if (!empty($_FILES['contact_file']['name'])
							&& ($_FILES['contact_file']['size'] <= 0
							|| !$GLOBALS['contact']['email']->attach(file_get_contents($_FILES['contact_file']['tmp_name']), $_FILES['contact_file']['name']))) {
								$GLOBALS['contact']['errors'][] = __('Error with the attached file.');
						} else {
							if ($GLOBALS['contact']['email']->send()) {
								$GLOBALS['contact']['emailsent'] = true;
							} else {
								$GLOBALS['contact']['errors'][] = __('The email couldn\'t be sent.');
							}
						}
						unset($GLOBALS['contact']['email']);
					}
				}
			}
		}
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/');
		
		// Non cachable page
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		self::serveDocument('contact.'.$core->blog->id.'.html','text/html',false);
	}
	
	public static function Contact($attr,$content) {
		return $content;
	}
	
	public static function ContactPageUrl($attr,$content) {		
		return
		'<?php echo $core->blog->url.$core->url->getBase("contact"); ?>';
	}
	
	public static function ContactPageTitle($attr,$content) {		
		return
		'<?php echo !empty($GLOBALS[\'contact\'][\'formconfig\']) ? str_replace(\'%N\', $core->blog->name, html::escapeJS($GLOBALS[\'contact\'][\'formconfig\'][\'pagetitle\'])) : \'\'; ?>';
	}
	
	public static function ContactH2Text($attr,$content) {		
		return
		'<?php echo !empty($GLOBALS[\'contact\'][\'formconfig\']) ? $GLOBALS[\'contact\'][\'formconfig\'][\'h2text\'] : \'\'; ?>';
	}

	public static function ContactInfoText($attr,$content) {		
		return
		'<?php echo !empty($GLOBALS[\'contact\'][\'formconfig\']) && !empty($GLOBALS[\'contact\'][\'recipients\']) ? isset($GLOBALS[\'contact\'][\'emailsent\']) ? $GLOBALS[\'contact\'][\'formconfig\'][\'emailsenttext\'] : $GLOBALS[\'contact\'][\'formconfig\'][\'infotext\'] : \'\'; ?>';
	}

	public static function ContactErrorBlock($attr,$content) {		
		return
		'<?php if (!empty($GLOBALS[\'contact\'][\'errors\'])) { ?>'.$content.'<?php } ?>';
	}

	public static function ContactErrorText($attr,$content) {		
		return
		'<?php echo \'<strong>'.html::escapeHTML(__('Error')).'</strong><br />\'.implode(\'<br />\', $GLOBALS[\'contact\'][\'errors\']); ?>';
	}

	public static function ContactPreviewBlock($attr,$content) {		
		return
		'<?php if (!empty($GLOBALS[\'contact\'][\'preview\'])) { ?>'.$content.'<?php } ?>';
	}

	public static function ContactPreviewText($attr,$content) {		
		return
		'<?php echo $GLOBALS[\'contact\'][\'preview\']; ?>';
	}

	public static function ContactForm($attr,$content) {		
		return
		'<?php if (!empty($GLOBALS[\'contact\'][\'recipients\']) && !isset($GLOBALS[\'contact\'][\'emailsent\'])) { ?>'.
		$content.'<?php } ?>';
	}

	public static function ContactFormInput($attr,$content) {
		switch ($attr['type']) {
			case 'recipient': return '<?php if (!empty($GLOBALS[\'contact\'][\'recipients\']) && count($GLOBALS[\'contact\'][\'recipients\']) >= 2) { ?>'.$content.'<?php } ?>'; break;
			/*case 'recipient':
				return
				'<?php if () {'.
				'<?php } elseif (!empty($GLOBALS[\'contact\'][\'recipients\']) && count($GLOBALS[\'contact\'][\'recipients\']) >= 2) { ?>'.
				$content.
				'<?php } ?>';
				break;*/
			case 'name': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'name_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'email': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'email_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'subject': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'subject_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'file': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'file_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'antispam': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'antispam_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'preview': return '<?php if ($GLOBALS[\'contact\'][\'formconfig\'][\'preview_enabled\']) { ?>'.$content.'<?php } ?>'; break;
			case 'additionnal': return '<?php foreach ($GLOBALS[\'contact\'][\'formconfig\'][\'additionnal\'] as $k => $v) { ?>'.$content.'<?php } ?>'; break;
			default: return $content;
		}
	}
	
	public static function ContactFormInputValue($attr,$content) {
		switch ($attr['type']) {
			case 'recipient_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'recipient_caption\']; ?>'; break;
			case 'recipient_v': return '<?php foreach ($GLOBALS[\'contact\'][\'recipients\'] as $k => $v) { echo \'<option value="\'.$k.\'"\'.($k == $GLOBALS[\'contact\'][\'formvalues\'][\'contact_recipient\'] ? \' selected="selected"\' : \'\').\'>\'.html::escapeHTML($v[\'name\']).\'</option>\';} ?>'; break;
			case 'name_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'name_caption\']; ?>'; break;
			case 'name_v': return '<?php echo html::escapeHTML($GLOBALS[\'contact\'][\'formvalues\'][\'contact_name\']); ?>'; break;
			case 'email_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'email_caption\']; ?>'; break;
			case 'email_v': return '<?php echo html::escapeHTML($GLOBALS[\'contact\'][\'formvalues\'][\'contact_email\']); ?>'; break;
			case 'subject_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'subject_caption\']; ?>'; break;
			case 'subject_v': return '<?php echo html::escapeHTML($GLOBALS[\'contact\'][\'formvalues\'][\'contact_subject\']); ?>'; break;
			case 'body_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'body_caption\']; ?>'; break;
			case 'body_v': return '<?php echo html::escapeHTML($GLOBALS[\'contact\'][\'formvalues\'][\'contact_body\']); ?>'; break;
			case 'file_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'file_caption\']; ?>'; break;
			case 'antispam_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'antispam_caption\']; ?>'; break;
			case 'antispam_v': return '<?php echo tplContact::ContactFormAntispamImg(\'\',\'\'); ?>'; break;
			case 'preview_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'preview_caption\']; ?>'; break;
			case 'send_c': return '<?php echo $GLOBALS[\'contact\'][\'formconfig\'][\'send_caption\']; ?>'; break;
			
			case 'additionnal_k': return '<?php echo $k; ?>'; break;
			case 'additionnal_c': return '<?php echo $v[\'caption\']; ?>'; break;
			case 'additionnal_v':
			return '<?php if ($v[\'type\'] == \'text\') {
					echo \'<input type="text" id="Contact_additionnal\'.$k.\'" name="contact[additionnal][\'.$k.\']" value="\'.html::escapeHTML($GLOBALS[\'contact\'][\'formvalues\'][\'contact_additionnal\'][$k]).\'" />\';
				} elseif ($v[\'type\'] == \'checkbox\') {
					echo \'<input type="checkbox" id="Contact_additionnal\'.$k.\'" name="contact[additionnal][\'.$k.\']"\'.($GLOBALS[\'contact\'][\'formvalues\'][\'contact_additionnal\'][$k] ? \' checked="checked"\' : \'\').\' style="width: auto; height: auto;" />\';
				} elseif ($v[\'type\'] == \'select\') {
					echo \'<select id="Contact_additionnal\'.$k.\'" name="contact[additionnal][\'.$k.\']">\'; foreach ($v[\'options\'] as $k2 => $v2) { echo \'<option value="\'.$k2.\'"\'.($k2 == $GLOBALS[\'contact\'][\'formvalues\'][\'contact_additionnal\'][$k] ? \' selected="selected"\' : \'\').\'>\'.html::escapeHTML($v2).\'</option>\'; } echo \'</select>\';
				} ?>';
			break;
			default: return $content;
		}
	}
	
	public static function ContactFormAntispamImg($attr,$content) {
		if (!function_exists('imagecreatetruecolor')) {
			return false;
		}
		
		$chars = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
		$key = '';
		for ($i = 0; $i < 6; $i++) {
			$key .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		
		file_put_contents(dirname(__FILE__).'/antispam.key.txt', $key);
		
		$img = imagecreatetruecolor(100, 25);
		$backgroundcolor = imagecolorallocate($img, 240, 240, 255);
		imagefilledrectangle($img, 0, 0, 100, 25, $backgroundcolor);
		
		$fontcolors = array(imagecolorallocate($img, 102, 102, 153), imagecolorallocate($img, 153, 153, 255), imagecolorallocate($img, 102, 102, 204), imagecolorallocate($img, 102, 102, 153), imagecolorallocate($img, 51, 51, 153), imagecolorallocate($img, 153, 153, 255));
		if (function_exists('imagettftext')) {
			for ($i = 0; $i < 6; $i++) {
			    imagettftext($img, mt_rand(13, 20), mt_rand(-20, 20), $i*15 + 5, 20, $fontcolors[$i], dirname(__FILE__).'/arial_lite.ttf', $key[$i]);
			}
		} else {
			for ($i = 0; $i < 6; $i++) {
			    imagestring($img, 5, $i*15 + 8, mt_rand(2, 9), $key[$i], $fontcolors[$i]);
			}
		}
		
		$bordercolor = imagecolorallocate($img, 200, 200, 255);
		imageline($img, 0, 0, 100, 0, $bordercolor);
		imageline($img, 0, 0, 0, 25, $bordercolor);
		imageline($img, 99, 0, 99, 25, $bordercolor);
		imageline($img, 0, 24, 100, 24, $bordercolor);
		
		imagepng($img, dirname(__FILE__).'/antispam.img.png');
		#$return = 'data:image/png;base64,'.base64_encode(file_get_contents(dirname(__FILE__).'/antispam.img.png'));
		#unlink(dirname(__FILE__).'/antispam.img.png');
		$return = '?pf=contact/antispam.img.png';
		
		return $return;
	}
	
	public static function clearCache() {
		global $core;
		
		@unlink(DC_TPL_CACHE.'/dctpl_'.md5($core->blog->themes_path.'/'.$core->blog->settings->theme.'/../../plugins/contact/contact.'.$core->blog->id.'.html').'.php');
		@unlink(DC_TPL_CACHE.'/dctpl_'.md5($core->blog->themes_path.'/default/../../plugins/contact/contact.'.$core->blog->id.'.html').'.php');
	}
}

$core->tpl->addBlock('Contact',array('tplContact','Contact'));
$core->tpl->addValue('ContactPageUrl',array('tplContact','ContactPageUrl'));
$core->tpl->addValue('ContactPageTitle',array('tplContact','ContactPageTitle'));
$core->tpl->addValue('ContactH2Text',array('tplContact','ContactH2Text'));
$core->tpl->addValue('ContactInfoText',array('tplContact','ContactInfoText'));
$core->tpl->addBlock('ContactErrorBlock',array('tplContact','ContactErrorBlock'));
$core->tpl->addValue('ContactErrorText',array('tplContact','ContactErrorText'));
$core->tpl->addBlock('ContactPreviewBlock',array('tplContact','ContactPreviewBlock'));
$core->tpl->addValue('ContactPreviewText',array('tplContact','ContactPreviewText'));
$core->tpl->addBlock('ContactForm',array('tplContact','ContactForm'));
$core->tpl->addBlock('ContactFormInput',array('tplContact','ContactFormInput'));
$core->tpl->addValue('ContactFormInputValue',array('tplContact','ContactFormInputValue'));
$core->tpl->addValue('ContactFormAntispamImg',array('tplContact','ContactFormAntispamImg'));

/* compatibilité avec Breadcrumb */
$core->addBehavior('publicBreadcrumb',array('extContact','publicBreadcrumb'));

class extContact
{
	public static function publicBreadcrumb($context,$separator)
	{
		if ($context == 'contact') {
			return __('Contact');
		}
	}
}
?>
