<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('ODT',array('tplOdt','odtLink'));

class urlOdt extends dcUrlHandlers
{
	public static function odt($args)
	{
		global $core, $_ctx;
		
		$core->blog->withoutPassword(false);
		
		$args_array = explode("/",$args);
		$params = new ArrayObject();
		$params['post_type'] = $args_array[0];
		$params['post_url'] = implode("/",array_slice($args_array,1));

		$_ctx->posts = $core->blog->getPosts($params);
		
		/*
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['content'] = '';
		$_ctx->comment_preview['rawcontent'] = '';
		$_ctx->comment_preview['name'] = '';
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		$_ctx->comment_preview['preview'] = false;
		$_ctx->comment_preview['remember'] = false;
		*/
		
		$core->blog->withoutPassword(true);
		
		if ($_ctx->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '')
		{
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
			
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
			{
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			}
			else
			{
				self::serveDocument('password-form.html','text/html',false);
				exit;
			}
		}
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']) &&
			$_ctx->posts->commentsActive();
		
		# The entry
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('post.odt', 'application/vnd.oasis.opendocument.text');
		exit;
	}

	protected static function serveDocument($tpl,$content_type='text/html',$http_cache=true,$http_etag=true)
	{
		global $odf;
		if ($content_type != 'application/vnd.oasis.opendocument.text') {
			return parent::serveDocument($tpl,$content_type,$http_cache,$http_etag);
		}

		$odtphp_path = "odtPHP0.9";

		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if ($_ctx->nb_entry_per_page === null) {
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_page;
		}
		
		$tpl_file = $core->tpl->getFilePath($tpl);
		
		if (!$tpl_file) {
			throw new Exception('Unable to find template');
		}
		
		/*
		if ($http_cache) {
			$GLOBALS['mod_files'][] = $tpl_file;
			http::cache($GLOBALS['mod_files'],$GLOBALS['mod_ts']);
		}
		*/

		//print "Excerpt:\n";
		//print $_ctx->posts->getExcerpt(1);
		//print "Content:\n";
		//print $_ctx->posts->getContent(1);
		////print_r($core->tpl->getData("post.html"));
		//return;
		require_once($odtphp_path.'/library/odf.php');
		$odf = new odf($tpl_file);

		try {
			$odf->setVars('EntryTitle', $_ctx->posts->post_title, true);
			$odf->setVars('EntryExcerpt', self::xhtml2odt($_ctx->posts->getExcerpt(1)), false, "utf-8");
			$odf->setVars('EntryContent', self::xhtml2odt($_ctx->posts->getContent(1)), false, "utf-8");
		}
		catch (OdfException $e)
		{
			// No such tag: ignore
		}

		// On exporte le fichier
		$odf->exportAsAttachedFile(str_replace('"','',$_ctx->posts->post_title).".odt");
		return;
		
		$result = new ArrayObject;
		
		header('Content-Type: '.$content_type.'; charset=UTF-8');
		$_ctx->current_tpl = $tpl;
		$result['content'] = $core->tpl->getData($tpl);
		$result['content_type'] = $content_type;
		$result['tpl'] = $tpl;
		$result['blogupddt'] = $core->blog->upddt;
		
		# --BEHAVIOR-- urlHandlerServeDocument
		$core->callBehavior('urlHandlerServeDocument',$result);
		
		if ($http_cache && $http_etag) {
			http::etag($result['content'],http::getSelfURI());
		}
		echo $result['content'];
	}
	
	protected static function xhtml2odt($xhtml)
	{
		global $core, $_ctx;
		//print $xhtml;exit;
		$xhtml = str_replace('<img src="http://'.$_SERVER["SERVER_NAME"],'<img src="',$xhtml);
		$xhtml = preg_replace_callback('#<img src="(/[^"]+)"#',array(self,"_handle_img"),$xhtml);

		$xsl = dirname(__FILE__)."/xsl";
		$xmldoc = new DOMDocument();
		$xmldoc->loadXML("<html>".$xhtml."</html>"); 
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl."/docbook.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsldoc);
		$proc->setParameter("blog","domain",$_SERVER["SERVER_NAME"]);
		$output = $proc->transformToXML($xmldoc);

		$output = str_replace('<?xml version="1.0"?>','',$output);
		$output = preg_replace('# xmlns:[a-z0-9]+="[^"]+"#','',$output);
		$output = "</text:p>".$output.'<text:p text:style-name="Standard">';
		$output = str_replace("\n"," ",$output);

		self::_addStyles($output);

		return $output;
		#xsltproc  --stringparam part manifest  ./xsl/docbook.xsl Commenter-en-anonyme-sur-Dotclear.html | sed -r -e 's/ xmlns:[a-z0-9]+="[^"]+"//g' > content.xml
	}

	protected static function _handle_img($matches)
	{
		global $odf;
		$file = $_SERVER["DOCUMENT_ROOT"].$matches[1];
		$filename = basename($file);
		$size = @getimagesize($file);
		if ($size === false) {
		    throw new OdfException("Invalid image");
		}
		list ($width, $height) = $size;
		$width *= Odf::PIXEL_TO_CM;
		$height *= Odf::PIXEL_TO_CM;
		$odf->importImage($file);
		return str_replace($matches[1],"Pictures/".$filename.'" width="'.$width.'cm" height="'.$height.'cm', $matches[0]);
	}
		
	protected static function _addStyles($odtxml)
	{
		global $odf;
		if (strpos($odtxml,'text:style-name="Heading_20_1"') !== false) {
			$odf->importStyle(
			'<style:style style:name="Heading_20_1" style:display-name="Heading 1"
			              style:family="paragraph" style:parent-style-name="Heading"
			              style:next-style-name="Text_20_body" style:class="text"
			              style:default-outline-level="1">
				<style:text-properties fo:font-size="115%" fo:font-weight="bold"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Heading_20_2"') !== false) {
			$odf->importStyle(
			'<style:style style:name="Heading_20_2" style:display-name="Heading 2"
			              style:family="paragraph" style:parent-style-name="Heading"
			              style:next-style-name="Text_20_body" style:class="text"
			              style:default-outline-level="2">
				<style:text-properties fo:font-size="110%" fo:font-weight="bold"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="sup"') !== false) {
			$odf->importStyle(
			'<style:style style:name="sup" style:family="text">
			 	<style:text-properties style:text-position="33% 80%"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Strong_20_Emphasis"') !== false) {
			$odf->importStyle(
			'<style:style style:name="Strong_20_Emphasis"
			              style:display-name="Strong Emphasis" style:family="text">
				<style:text-properties fo:font-weight="bold"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Emphasis"') !== false) {
			$odf->importStyle(
			'<style:style style:name="Emphasis"
			              style:display-name="Emphasis" style:family="text">
				<style:text-properties fo:font-style="italic"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="center"') !== false) {
			$odf->importStyle(
			'<style:style style:name="center" style:family="paragraph"
			              style:parent-style-name="Text_20_body">
				<style:paragraph-properties fo:text-align="center"
				                            style:justify-single-word="false"/>
			</style:style>');
		}
		if (strpos($odtxml,'draw:style-name="image-inline"') !== false) {
			$odf->importStyle(
			'<style:style style:name="image-inline" style:family="graphic"
			              style:parent-style-name="Graphics">
				<style:graphic-properties style:vertical-pos="middle"
				                          style:vertical-rel="text"/>
			</style:style>');
		}
	}
}

class tplOdt
{
	public static function odtLink($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("odt")').'; ?>';
	}
}

?>
