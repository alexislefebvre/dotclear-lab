<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2009 Olivier Azeau and contributors. All rights  reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require_once("htmlparser/html2text.inc");

class MyFormsEmail
{
  private $to, $subject, $body, $headers, $attachments;
  private $hash, $altboundary, $mixedboundary;
  
  public function __construct() {
    $this->headers = array();
    $this->headers[] = 'MIME-Version: 1.0';
    $this->hash = md5(date('r', time()));
    
    $this->altboundary = '---alt-'.$this->hash;
    $this->bodyformat =
      '--'.$this->altboundary."\n".
      'Content-Type: text/%s; charset="iso-8859-1"'."\n".
      'Content-Transfer-Encoding: 7bit'."\n\n%s\n";
      
    $this->attachments = false;
    $this->mixedboundary = '---mixed-'.$this->hash;
    $this->attachmentformat =
      '--'.$this->mixedboundary."\n".
      'Content-Type: %s; name="%s"'."\n".
      'Content-Transfer-Encoding: base64'."\n".
      'Content-Disposition: attachment'."\n\n%s\n";
  }
  
  private function AddHeader($name,$content) {
    if(!$content)
      return;
    $contentItems = preg_split("/[\s]*,[\s]*/",trim(self::html2text($content)));
    foreach($contentItems as $item)
      $this->headers[] = $name.': '.$item;
  }
  
  public function from($sender) {
    $this->AddHeader('From',$sender);
  }
  
  public function cc($receiver) {
    $this->AddHeader('Cc',$receiver);
  }
  
  public function bcc($receiver) {
    $this->AddHeader('Bcc',$receiver);
  }
  
  public function to($receiver) {
    $this->to = self::html2text($receiver);
  }
  
  public function subject($text) {
    $this->subject = self::html2text($text);
  }
  
  public function body($text) {
    $this->body =
      sprintf($this->bodyformat, 'plain', self::html2text(nl2br($text))).
      sprintf($this->bodyformat, 'html', $text).
      '--'.$this->altboundary."\n";
  }
  
  public function attach($filename,$filetype,$path) {
    $this->attachments .= sprintf(
      $this->attachmentformat,
      $filetype,
      $filename,
      chunk_split(base64_encode(file_get_contents($path)))
    );
  }
  
  public function attachHttpUpload($httpUpload,$fieldName) {
    if( !isset($httpUpload["name"][$fieldName]) )
      return false; // no file for this field name
    if( $httpUpload["error"][$fieldName] != 0 )
      return false; // upload error
    return $this->attach($httpUpload["name"][$fieldName],$httpUpload["type"][$fieldName],$httpUpload["tmp_name"][$fieldName]);
  }
  
  public function send() {
    $alt = 'Content-Type: multipart/alternative; boundary="'.$this->altboundary.'"';
    if( $this->attachments ) {
      array_unshift($this->headers, 'Content-Type: multipart/mixed; boundary="'.$this->mixedboundary.'"');
      $message =
        '--'.$this->mixedboundary."\n".
        $alt."\n\n".
        $this->body."\n".
        $this->attachments.
        '--'.$this->mixedboundary."\n";
    } else {
      array_unshift($this->headers, $alt);
      $message = $this->body;
    }
    try
    {
      mail::sendMail($this->to,$this->subject,$message,$this->headers);
      return 0;
    }
    catch (Exception $e)
    {
      return array('email', $e->getMessage(), $e);
    }
  }
  
  private static function html2text($html) {
    $htmlToText = new Html2Text($html, 80);
    return html::decodeEntities($htmlToText->convert());
  }
  
}
?>