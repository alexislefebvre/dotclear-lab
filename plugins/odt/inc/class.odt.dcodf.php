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

require_once(dirname(__FILE__)."/class.odt.template.php");


class OdfException extends Exception {}


/**
 * Gestion d'un fichier ODT basé sur un modèle (un autre fichier ODT)
 */
class dcODF
{
	protected $odtfile;
	protected $odtfilepath;
	protected $tmpfiles = array();
	protected $contentXml;
	protected $stylesXml;
	protected $images = array();
	public $template;
	public $xslparams = array();
	public $get_remote_images = true;
	const PIXEL_TO_CM = 0.026458333;

	public function __construct($template)
	{
		$this->template = $template;
		if (! class_exists('ZipArchive')) {
			throw new OdfException('Zip extension not loaded - check your php settings, PHP5.2 minimum with zip extension
			 is required for using OdtExport'); ;
		}
		// Chargement du content.xml et du styles.xml du modele
		$this->odtfile = new ZipArchive();
		if ($this->odtfile->open($template) !== true) {
		  throw new OdfException("Error while Opening the file '$template' - Check your odt file");
		}
		if (($this->contentXml = $this->odtfile->getFromName('content.xml')) === false) {
			throw new OdfException("Nothing to parse - check that the content.xml file is correctly formed");
		}
		if (($this->stylesXml = $this->odtfile->getFromName('styles.xml')) === false) {
		  throw new OdfException("Nothing to parse - check that the styles.xml file is correctly formed");
		}
		$this->odtfile->close();
		$tmp = tempnam(DC_TPL_CACHE, md5(uniqid()));
		copy($template, $tmp);
		$this->odtfilepath = $tmp;
	}

	public function __destruct()
	{
		if (file_exists($this->odtfilepath)) {
			unlink($this->odtfilepath);
		}
		foreach ($this->tmpfiles as $tmp) {
			unlink($tmp);
		}
	}

	public function __toString()
	{
		return $this->contentXml;
	}

	/**
	 * Fonction principale qui ordonnance toutes les autres
	 */
	public function compile()
	{
		global $core, $_ctx;
		$t = new odtTemplate(DC_TPL_CACHE,'$core->tpl',$core, $this);
		// Compile the tags and convert to ODT XML
		$_ctx->current_tpl = basename($this->template);
		$output = $t->getData(basename($this->template));
		$output = $this->xhtml2odt($output);
		//print $this->contentXml;
		//print $output;
		//exit();
		$this->contentXml = $output;
		$this->addStyles();
	}

	public function getContentXml()
	{
		return $this->contentXml;
	}

	public function cleanupInput($xhtml)
	{
		// add namespace
		$xhtml = str_replace("<office:document-content", '<office:document-content xmlns="http://www.w3.org/1999/xhtml"', $xhtml);
		// replace html codes with unicode
		$xhtml = str_replace("&nbsp;","&#160;",$xhtml); // http://www.mail-archive.com/analog-help@lists.meer.net/msg03670.html
		/*
		 * I'd love to run tidy here to make sure the input HTML is
		 * well-formed, but I don't have XHTML as input, I have ODT XML. Thus
		 * I have to use the input-xml option, and it does strange things
		 * like removing the white space after links. I can't stop it.

		if (extension_loaded('tidy')) {
			$tidy_config = array(
					'indent' => false,
					'input-xml' => true,
					'output-xml' => true,
					'wrap' => 0,
					'tidy-mark' => false,
					'output-encoding' => "utf8",
					'char-encoding' => "utf8",
					'preserve-entities' => true,
					'drop-empty-paras' => false,
					"literal-attributes" => true,
					"quote-nbsp" => false,
				);
			$tidy = new tidy;
			$tidy->parseString($xhtml, $tidy_config, 'utf8');
			$tidy->cleanRepair();
			$xhtml = "$tidy";
		}
		*/
		return $xhtml;
	}

	/**
	 * Conversion de XHTML vers ODT par l'utiliation de XHTML2ODT (XSL)
	 *
	 * @param string $xhtml le XHTML à convertir
	 * @return string le ODT XML résultat de la conversion
	 */
	public function xhtml2odt($xhtml)
	{
		$xhtml = self::cleanupInput($xhtml);
		// handle images
		$xhtml = preg_replace('#<img ([^>]*)src="http://'.$_SERVER["SERVER_NAME"].'#','<img \1src="',$xhtml);
		$xhtml = preg_replace_callback('#<img [^>]*src="(/[^"]+)"#',array($this,"handleLocalImg"),$xhtml);
		if ($this->get_remote_images) {
			$xhtml = preg_replace_callback('#<img [^>]*src="(https?://[^"]+)"#',array($this,"handleRemoteImg"),$xhtml);
		}
		// run the stylesheets
		$xsl = dirname(__FILE__)."/xsl";
		$xmldoc = new DOMDocument();
		$xmldoc->loadXML($xhtml); 
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl."/xhtml2odt.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsldoc);
		foreach ($this->xslparams as $pkey=>$pval) {
			$proc->setParameter("blog",$pkey,$pval);
		}
		$output = $proc->transformToXML($xmldoc);
		if ($output === false) {
			throw new OdfException('XSLT transformation failed');
		}
		return $output;
	}

	/**
	 * Gestion des images locales (sur ce serveur)
	 *
	 * Doit être appelé comme callback d'une expression rationelle. Délègue
	 * tout le travail d'insertion à $this.handleImg()
	 *
	 * @param array $matches correspondances de l'expression rationelle
	 * @return string remplacement de l'expr. rat.
	 */
	protected function handleLocalImg($matches)
	{
		$file = $_SERVER["DOCUMENT_ROOT"].$matches[1];
		if (!is_readable($file)) { // fichier introuvable
			if ($this->get_remote_images) { // on essaye de le télécharger
				$matches[0] = str_replace($matches[1], "http://".$_SERVER["SERVER_NAME"].$matches[1], $matches[0]);
				$matches[1] = "http://".$_SERVER["SERVER_NAME"].$matches[1];
				return $this->handleRemoteImg($matches);
			} else {
				return $matches[0]; // tant pis, on laisse un lien distant
			}
		}
		return $this->handleImg($file, $matches);
	}

	/*
	 * Téléchargement des images distantes avec cURL
	 *
	 * Doit être appelé comme callback d'une expression rationelle. Délègue
	 * tout le travail d'insertion à $this.handleImg()
	 *
	 * @param array $matches correspondances de l'expression rationelle
	 * @return string remplacement de l'expr. rat.
	 */
	protected function handleRemoteImg($matches)
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
		return $this->handleImg($tempfilename, $matches);
	}

	/**
	 * Gestion de l'insertion de l'image dans le fichier ODT
	 *
	 * @param string $file le chemin vers le fichier image à inclure
	 * @param array $matches le tableau des correspondances de l'expr. rat.
	 * @return string le replacement de l'expr. rat.
	 */
	protected function handleImg($file, $matches)
	{
		$size = @getimagesize($file);
		if ($size === false) {
			global $core;
			$size = array($core->blog->settings->odt_img_width,
			              $core->blog->settings->odt_img_height);
		}
		list ($width, $height) = $size;
		$width *= self::PIXEL_TO_CM;
		$height *= self::PIXEL_TO_CM;
		$this->importImage($file);
		return str_replace($matches[1],"Pictures/".basename($file).'" width="'.$width.'cm" height="'.$height.'cm', $matches[0]);
	}
		
	/**
	 * Ajoute une image aux fichiers à importer
	 *
	 * L'image doit être ajoutée au texte par un autre moyen.
	 *
	 * @param string $filename chemin vers une image
	 * @throws OdfException
	 */
	public function importImage($filename)
	{
		if (!is_readable($filename)) {
			throw new OdfException("Image is not readable or does not exist");
		}
		$this->images[$filename] = basename($filename);
	}

	/**
	 * Sauvegarde interne
	 *
	 * @throws OdfException
	 */
	protected function _save()
	{
		$this->odtfile->open($this->odtfilepath, ZIPARCHIVE::CREATE);
		if (! $this->odtfile->addFromString('content.xml', $this->contentXml)) {
			throw new OdfException('Error during file export');
		}
		if (! $this->odtfile->addFromString('styles.xml', $this->stylesXml)) {
			throw new OdfException('Error during file export');
		}
		foreach ($this->images as $imageKey => $imageValue) {
			$this->odtfile->addFile($imageKey, 'Pictures/' . $imageValue);
		}
		$this->odtfile->close();
	}

	/**
	 * Exporte le fichier par HTTP en tant qu'attachement
	 *
	 * @param string $name nom du fichier à télécharger (optionnel)
	 * @throws OdfException
	 */
	public function exportAsAttachedFile($name="")
	{
		$this->_save();
		if (headers_sent($filename, $linenum)) {
			throw new OdfException("headers already sent ($filename at $linenum)");
		}
		if( $name == "" ) {
			$name = md5(uniqid()) . ".odt";
		}
		header('Content-type: application/vnd.oasis.opendocument.text');
		header('Content-Disposition: attachment; filename="'.$name.'"');
		readfile($this->odtfilepath);
	}

	/**
	 * Ajout de tous les styles manquants au document ODT
	 */
	protected function addStyles()
	{
		// prepare the stylesheet
		$xsl = dirname(__FILE__)."/xsl";
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl."/styles.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsldoc);
		// process content.xml
		$contentXml = new DOMDocument();
		$contentXml->loadXML($this->contentXml); 
		$this->contentXml = $proc->transformToXML($contentXml);
		if ($this->contentXml === false) {
			throw new OdfException('XSLT transformation failed (adding styles to content.xml)');
		}
		// process content.xml
		$stylesXml = new DOMDocument();
		$stylesXml->loadXML($this->stylesXml); 
		$this->stylesXml = $proc->transformToXML($stylesXml);
		if ($this->stylesXml === false) {
			throw new OdfException('XSLT transformation failed (adding styles to styles.xml)');
		}
	}

}


?>
