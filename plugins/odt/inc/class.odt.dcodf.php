<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

define("PCLZIP_TEMPORARY_DIR",realpath(DC_TPL_CACHE)."/odt/");
require_once(dirname(__FILE__).'/odtPHP0.10/library/odf.php');
require_once(dirname(__FILE__)."/class.odt.template.php");
require_once(dirname(__FILE__)."/lib.odt.odtstyle.php");

class dcODF extends odf
{
	protected $stylesXml;
	protected $autostyles = array();
	protected $styles = array();
	protected $fonts = array();
	public $filename;
	public $params = array();
	public $get_remote_images = true;
	protected $tmpfiles = array();

	public function __construct($filename)
	{
		parent::__construct($filename, array(), realpath(DC_TPL_CACHE)."/odt");
		if ($this->file->open($filename) !== true) {
		  throw new OdfException("Error while Opening the file '$filename' - Check your odt file");
		}
		if (($this->stylesXml = $this->file->getFromName('styles.xml')) === false) {
		  throw new OdfException("Nothing to parse - check that the styles.xml file is correctly formed");
		}
		$this->file->close();
		$this->filename = $filename;
	}

	function __destruct() {
		parent::__destruct();
		foreach ($this->tmpfiles as $tmp) {
			unlink($tmp);
		}
	}

	public function compile()
	{
		global $core, $_ctx;
		$t = new odtTemplate(DC_TPL_CACHE,'$core->tpl',$core, $this);
		// Compile the tags and convert to ODT XML
		$_ctx->current_tpl = basename($this->filename);
		$output = $t->getData(basename($this->filename));
		$output = $this->xhtml2odt($output);
		//print $this->contentXml;
		//print $output;
		//exit();
		$this->contentXml = $output;
		$this->addStyles($this->contentXml);
	}

	public function getContentXml()
	{
		return $this->contentXml;
	}

	public function xhtml2odt($xhtml)
	{
		// add namespace
		$xhtml = str_replace("<office:document-content", '<office:document-content xmlns="http://www.w3.org/1999/xhtml"', $xhtml);
		// replace html codes with unicode
		$xhtml = str_replace("&nbsp;","&#160;",$xhtml); // http://www.mail-archive.com/analog-help@lists.meer.net/msg03670.html
		// handle images
		$xhtml = preg_replace('#<img ([^>]*)src="http://'.$_SERVER["SERVER_NAME"].'#','<img \1src="',$xhtml);
		$xhtml = preg_replace_callback('#<img [^>]*src="(/[^"]+)"#',array($this,"handle_local_img"),$xhtml);
		if ($this->get_remote_images) {
			$xhtml = preg_replace_callback('#<img [^>]*src="(https?://[^"]+)"#',array($this,"handle_remote_img"),$xhtml);
		}
		// run the stylesheets
		$xsl = dirname(__FILE__)."/xsl";
		$xmldoc = new DOMDocument();
		$xmldoc->loadXML($xhtml); 
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl."/xhtml2odt.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsldoc);
		foreach ($this->params as $pkey=>$pval) {
			$proc->setParameter("blog",$pkey,$pval);
		}
		$output = $proc->transformToXML($xmldoc);
		if ($output === false) {
			throw new Exception('XSLT transformation failed');
		}
		return $output;
	}

	protected function handle_local_img($matches)
	{
		$file = $_SERVER["DOCUMENT_ROOT"].$matches[1];
		return $this->handle_img($file, $matches);
	}

	protected function handle_remote_img($matches)
	{
		$url = $matches[1];
		$tempfilename = tempnam(DC_TPL_CACHE,"dotclear-odt-");
		$this->tmpfiles []= $tempfilename;
		$tempfile = fopen($tempfilename,"w");
		if ($tempfile === false) {
			return $matches[0];
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $tempfile);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		$result = curl_exec($ch);
		if ($result === false) {
			return $matches[0];
		}
		curl_close($ch);
		fclose($tempfile);
		return $this->handle_img($tempfilename, $matches);
	}

	protected function handle_img($file, $matches)
	{
		$size = @getimagesize($file);
		if ($size === false) {
			throw new OdfException("Invalid image: ".$file);
		}
		list ($width, $height) = $size;
		$width *= self::PIXEL_TO_CM;
		$height *= self::PIXEL_TO_CM;
		$this->importImage($file);
		return str_replace($matches[1],"Pictures/".basename($file).'" width="'.$width.'cm" height="'.$height.'cm', $matches[0]);
	}
		
	/**
	 * Ajoute une image aux fichiers a importer. L'image doit etre ajoutee au texte par un autre moyen
	 *
	 * @param string $filename chemin vers une image
	 * @throws OdfException
	 * @return odf
	 */
	public function importImage($filename)
	{
		if (!is_readable($filename)) {
			throw new OdfException("Image is not readable or does not exist");
		}
		$this->images[$filename] = basename($filename);
		return $this;
	}
	protected function _parse()
	{
		parent::_parse();
		// automatic styles
		if ($this->autostyles) {
			$autostyles = implode("\n",$this->autostyles);
			if (strpos($this->contentXml, '<office:automatic-styles/>') !== false) {
				$this->contentXml = str_replace('<office:automatic-styles/>',
										'<office:automatic-styles>'.$autostyles.'</office:automatic-styles>',
										$this->contentXml);
			} else {
				$this->contentXml = str_replace('</office:automatic-styles>',
										$autostyles.'</office:automatic-styles>', $this->contentXml);
			}
		}
		// regular styles
		if ($this->styles) {
			$styles = implode("\n",$this->styles);
			$this->stylesXml = str_replace('</office:styles>',
								   $styles.'</office:styles>', $this->stylesXml);
		}
		// fonts
		if ($this->fonts) {
			$fonts = implode("\n",$this->fonts);
			$this->contentXml = str_replace('</office:font-face-decls>',
									$fonts.'</office:font-face-decls>', $this->contentXml);
		}
	}
    protected function _save()
    {
    	$this->file->open($this->tmpfile);
        $this->_parse();
        if (! $this->file->addFromString('content.xml', $this->contentXml)) {
            throw new OdfException('Error during file export');
        }
        if (! $this->file->addFromString('styles.xml', $this->stylesXml)) {
            throw new OdfException('Error during file export');
        }
        foreach ($this->images as $imageKey => $imageValue) {
            $this->file->addFile($imageKey, 'Pictures/' . $imageValue);
        }
        $this->file->close(); // seems to bug on windows CLI sometimes
    }
	/**
	 * Ajoute un style
	 *
	 * @param string $style style au format ODT
	 * @return odf
	 */
	public function importStyle($style, $mainstyle=false)
	{
		preg_match('#.*style:name="([^"]+)".*#', $style, $matches);
		$name = $matches[1];
		if (array_key_exists($name, $this->styles)) {
			return $this; // already added
		} 
		if (strpos($this->contentXml, 'style:name="'.$name.'"') !== false) {
			return $this; // already present in template
		}
		if ($mainstyle) {
			$this->styles[$name] = $style;
		} else {
			$this->autostyles[$name] = $style;
		}
		return $this;
	}
	/**
	 * Ajoute une police de caracteres
	 *
	 * @param string $style police au format ODT
	 * @return odf
	 */
	public function importFont($font)
	{
		preg_match('#.*style:name="([^"]+)".*#', $font, $matches);
		$name = $matches[1];
		if (array_key_exists($name, $this->fonts)) {
			return $this; // already added
		} 
		if ( strpos($this->contentXml, '<style:font-face style:name="'.$name.'"') !== false or
			 strpos($this->stylesXml, '<style:font-face style:name="'.$name.'"') !== false ) {
			return $this; // already present in template
		}
		$this->fonts[$name] = $font;
		return $this;
	}
	protected function addStyles($odtxml)
	{
		odtStyle::add_styles(dirname(__FILE__)."/styles/", $odtxml,
		                     array($this, "importStyle"),
		                     array($this, "importFont"));
	}
}
?>
