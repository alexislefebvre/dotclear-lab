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

require_once(dirname(__FILE__).'/odtPHP0.9/library/odf.php');
require_once(dirname(__FILE__)."/class.odt.template.php");

class dcODF extends odf
{
	const DELIMITER_LEFT = '{{tpl:';
	const DELIMITER_RIGHT = '}}';
	public $filename;
	public $params = array();
	public $get_remote_images = true;
	protected $tmpfiles = array();

	public function __construct($filename)
	{
		parent::__construct($filename);
		$this->filename = $filename;
	}

	function __destruct() {
		unlink($this->tmpfile);
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
		$xhtml = str_replace("&nbsp;","&#160;",$xhtml); // http://www.mail-archive.com/analog-help@lists.meer.net/msg03670.html
		$xhtml = preg_replace('#<img ([^>]*)src="http://'.$_SERVER["SERVER_NAME"].'#','<img \1src="',$xhtml);
		$xhtml = preg_replace_callback('#<img [^>]*src="(/[^"]+)"#',array($this,"handle_local_img"),$xhtml);
		if ($this->get_remote_images) {
			$xhtml = preg_replace_callback('#<img [^>]*src="(https?://[^"]+)"#',array($this,"handle_remote_img"),$xhtml);
		}

		$xsl = dirname(__FILE__)."/../xsl";
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
		    throw new OdfException("Invalid image");
		}
		list ($width, $height) = $size;
		$width *= self::PIXEL_TO_CM;
		$height *= self::PIXEL_TO_CM;
		$this->importImage($file);
		return str_replace($matches[1],"Pictures/".basename($file).'" width="'.$width.'cm" height="'.$height.'cm', $matches[0]);
	}
		
	protected function addStyles($odtxml)
	{
		// Headers
		if (strpos($odtxml,'text:style-name="Heading_20_1"') !== false) {
			$this->importStyle(
			'<style:style style:name="Heading_20_1" style:display-name="Heading 1"
			              style:family="paragraph" style:parent-style-name="Heading"
			              style:next-style-name="Text_20_body" style:class="text"
			              style:default-outline-level="1">
				<style:text-properties fo:font-size="115%" fo:font-weight="bold"/>
			</style:style>', true);
		}
		if (strpos($odtxml,'text:style-name="Heading_20_2"') !== false) {
			$this->importStyle(
			'<style:style style:name="Heading_20_2" style:display-name="Heading 2"
			              style:family="paragraph" style:parent-style-name="Heading"
			              style:next-style-name="Text_20_body" style:class="text"
			              style:default-outline-level="2">
				<style:text-properties fo:font-size="110%" fo:font-weight="bold"
				                       fo:font-style="italic"/>
			</style:style>', true);
		}
		if (strpos($odtxml,'text:style-name="Heading_20_3"') !== false) {
			$this->importStyle(
			'<style:style style:name="Heading_20_3" style:display-name="Heading 3"
			              style:family="paragraph" style:parent-style-name="Heading"
			              style:next-style-name="Text_20_body" style:class="text"
			              style:default-outline-level="3">
				<style:text-properties fo:font-size="100%" fo:font-weight="bold"/>
			</style:style>', true);
		}
		// Inline text
		if (strpos($odtxml,'text:style-name="sup"') !== false) {
			$this->importStyle(
			'<style:style style:name="sup" style:family="text">
			 	<style:text-properties style:text-position="33% 80%"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Strong_20_Emphasis"') !== false) {
			$this->importStyle(
			'<style:style style:name="Strong_20_Emphasis"
			              style:display-name="Strong Emphasis" style:family="text">
				<style:text-properties fo:font-weight="bold"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Emphasis"') !== false) {
			$this->importStyle(
			'<style:style style:name="Emphasis"
			              style:display-name="Emphasis" style:family="text">
				<style:text-properties fo:font-style="italic"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Source"') !== false) {
			$this->importStyle(
			'<style:style style:name="Source"
			              style:display-name="Source Text" style:family="text">
				<style:text-properties style:font-name="Bitstream Vera Sans Mono"
				                       fo:font-size="9pt"/>
			</style:style>');
			$this->importFont(
			'<style:font-face style:name="Bitstream Vera Sans Mono"
			                  svg:font-family="&apos;Bitstream Vera Sans Mono&apos;"
			                  style:font-family-generic="swiss" style:font-pitch="fixed"/>');
		}
		// Paragraph
		if (strpos($odtxml,'text:style-name="center"') !== false) {
			$this->importStyle(
			'<style:style style:name="center" style:family="paragraph"
			              style:parent-style-name="Text_20_body">
				<style:paragraph-properties fo:text-align="center"
				                            style:justify-single-word="false"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Quotations"') !== false) {
			$this->importStyle(
			'<style:style style:name="Quotations" style:family="paragraph"
			              style:parent-style-name="Text_20_body" style:class="html">
				<style:paragraph-properties fo:margin-left="1cm" fo:margin-right="1cm"
				                            fo:margin-top="0cm" fo:margin-bottom="0.5cm"
									   fo:text-indent="0cm" style:auto-text-indent="false"
									   fo:padding="0.2cm"
									   fo:border-left="0.088cm solid #999999"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Preformatted_20_Text"') !== false) {
			$this->importStyle(
			'<style:style style:name="Preformatted_20_Text"
			              style:display-name="Preformatted Text" style:family="paragraph"
					    style:parent-style-name="Standard" style:class="html">
				<style:paragraph-properties fo:margin-left="1cm" fo:margin-right="1cm"
				                            fo:margin-top="0cm" fo:margin-bottom="0.2cm"/>
				<style:text-properties style:font-name="Bitstream Vera Sans Mono"
				                       fo:font-size="9pt"/>
			</style:style>');
			$this->importFont(
			'<style:font-face style:name="Bitstream Vera Sans Mono"
			                  svg:font-family="&apos;Bitstream Vera Sans Mono&apos;"
			                  style:font-family-generic="swiss" style:font-pitch="fixed"/>');
		}
		if (strpos($odtxml,'text:style-name="Horizontal_20_Line"') !== false) {
			$this->importStyle(
			'<style:style style:name="Horizontal_20_Line" style:display-name="Horizontal Line"
			              style:family="paragraph" style:parent-style-name="Standard"
			              style:next-style-name="Text_20_body" style:class="html">
				<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.5cm"
				             style:border-line-width-bottom="0.002cm 0.035cm 0.002cm"
				             fo:padding="0cm" fo:border-left="none" fo:border-right="none"
				             fo:border-top="none" fo:border-bottom="0.04cm double #808080"
				             text:number-lines="false" text:line-number="0"
				             style:join-border="false"/>
				<style:text-properties fo:font-size="6pt"/>
			</style:style>');
		}
		// Lists
		if (strpos($odtxml,'text:style-name="list-item-bullet"') !== false) {
			$this->importStyle(
			'<style:style style:name="list-item-bullet" style:family="paragraph"
			              style:parent-style-name="Text_20_body"
			              style:list-style-name="List_20_1"/>');
			$ul_styles = '<text:list-style style:name="List_20_1" style:display-name="List 1">';
			for ($i=1;$i<=10;$i++) {
				$ul_styles .= '<text:list-level-style-bullet text:level="'.$i.'"
				                    text:style-name="Numbering_20_Symbols"
				                    text:bullet-char="•">
								<style:list-level-properties
									text:space-before="'.(0.4*($i-1)).'cm"
									text:min-label-width="0.4cm"/>
						 		<style:text-properties style:font-name="StarSymbol"/>
					  		</text:list-level-style-bullet>';
			}
			$ul_styles .= '</text:list-style>';
			$this->importStyle($ul_styles);
		}
		if (strpos($odtxml,'text:style-name="list-item-number"') !== false) {
			$this->importStyle(
			'<style:style style:name="list-item-number" style:family="paragraph"
			              style:parent-style-name="Text_20_body"
			              style:list-style-name="Numbering_20_1"/>');
			$ol_styles = '<text:list-style style:name="Numbering_20_1" style:display-name="Numbering 1">';
			for ($i=1;$i<=10;$i++) {
				$ol_styles .= '<text:list-level-style-number text:level="'.$i.'"
				                    text:style-name="Numbering_20_Symbols"
				                    style:num-suffix="." style:num-format="1">
								<style:list-level-properties
									text:space-before="'.(0.5*($i-1)).'cm"
									text:min-label-width="0.5cm"/>
					  		</text:list-level-style-number>';
			}
			$ol_styles .= '</text:list-style>';
			$this->importStyle($ol_styles);
		}
		// Images
		if (strpos($odtxml,'draw:style-name="image-inline"') !== false) {
			$this->importStyle(
			'<style:style style:name="image-inline" style:family="graphic"
			              style:parent-style-name="Graphics">
				<style:graphic-properties style:vertical-pos="middle"
				                          style:vertical-rel="text"/>
			</style:style>');
		}
		// Tables
		if (strpos($odtxml,'text:style-name="Table_20_Contents"') !== false) {
			$this->importStyle(
			'<style:style style:name="Table_20_Contents" style:display-name="Table Contents"
			              style:family="paragraph" style:parent-style-name="Standard"
			              style:class="extra">
				<style:paragraph-properties text:number-lines="false" text:line-number="0"/>
			</style:style>');
		}
		if (strpos($odtxml,'text:style-name="Table_20_Heading"') !== false) {
			$this->importStyle(
			'<style:style style:name="Table_20_Heading" style:display-name="Table Heading"
			              style:family="paragraph" style:parent-style-name="Table_20_Contents"
			              style:class="extra">
				<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"
				                            text:number-lines="false" text:line-number="0"/>
				<style:text-properties fo:font-weight="bold"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-A1"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-A1" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.03cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="0.03cm solid #000000"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-B1"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-B1" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="0.03cm solid #000000"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-C1"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-C1" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="0.03cm solid #000000"
				                             fo:border-top="0.03cm solid #000000"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-A2"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-A2" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.03cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="none"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-B2"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-B2" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="none"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-C2"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-C2" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="0.03cm solid #000000"
				                             fo:border-top="none"
				                             fo:border-bottom="0.01cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-A3"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-A3" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.03cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="none"
				                             fo:border-bottom="0.03cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-B3"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-B3" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="none"
				                             fo:border-top="none"
				                             fo:border-bottom="0.03cm solid #000000"/>
			</style:style>');
		}
		if (strpos($odtxml,'table:style-name="table-default.cell-C3"') !== false) {
			$this->importStyle(
			'<style:style style:name="table-default.cell-C3" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.05cm"
				                             fo:border-left="0.01cm solid #000000"
				                             fo:border-right="0.03cm solid #000000"
				                             fo:border-top="none"
				                             fo:border-bottom="0.03cm solid #000000"/>
			</style:style>');
		}
	}
}
