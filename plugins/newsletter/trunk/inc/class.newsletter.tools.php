<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class newsletterTools
{
	/**
	* encodage en base64 pour une url
	*/
	public static function base64_url_encode($val)
	{
		return strtr(base64_encode($val), '+/=', '-_,');
	}

	/**
	* decodage en base64 pour une url
	*/
	public static function base64_url_decode($val)
	{
		return base64_decode(strtr($val, '-_,', '+/='));
	}

	/**
	* gÃ©nÃ¨re un code d'enregistrement
	*/
	public static function regcode() 
	{
		return md5(date('Y-m-d H:i:s', strtotime("now")) ); 
	}
	
	// use by : NewsletterFormRandom
	public static function getRandom()
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		mt_srand($seed);
		return mt_rand();
	}	

	// surcharge de la fonction cutString pour avoir un extrait d'un texte
	public static function cutString($str,$maxlength=false)
	{
		if (mb_strlen($str) > $maxlength && $maxlength) {
			return text::cutString($str,$maxlength).'...';
		} else {			
			return $str;
		}
	}

	// redirection
	public static function redirection($module='subscribers',$msg='') 
	{
		$redir = 'plugin.php?p=newsletter';

		if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
		{
			$redir = $_POST['redir'];
		}
		else
		{
			$redir .= '&m='.$module.
			($_POST['sortby'] ? '&sortby='.$_POST['sortby'] : '' ).
			($_POST['order'] ? '&order='.$_POST['order'] : '' ).
			($_POST['page'] ? '&page='.$_POST['page'] : '' ).
			($_POST['nb'] ? '&nb='.(integer) $_POST['nb'] : '' ).
			'&msg='.rawurldecode($msg);
		}
		http::redirect($redir);	

	}
	
	// recherche si le template existe dans le theme
	public static function requestTemplate(dcCore $core, $filename) 
	{	
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$system_settings =& $core->blog->settings->system;
		} else {
			$system_settings->system_settings =& $core->blog->settings;
		}				

		if (file_exists(path::real($core->blog->themes_path.'/'.$system_settings->theme).'/tpl/'.$filename))
			$folder = path::real($core->blog->themes_path.'/'.$system_settings->theme).'/tpl/';
		else
			$folder =  path::real(newsletterPlugin::folder().'..').'/default-templates/';		
		return $folder;
	}

	// recherche si le CSS existe dans le theme
	public static function requestPathFileCSS(dcCore $core, $filename) 
	{	
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$system_settings =& $core->blog->settings->system;
		} else {
			$system_settings =& $core->blog->settings;
		}		
		
		if (file_exists(path::real($core->blog->themes_path.'/'.$system_settings->theme.'/'.$filename)))
			$folder = path::real($core->blog->themes_path.'/'.$system_settings->theme);
		else
			$folder =  path::real(newsletterPlugin::folder().'..');
		return $folder;
	}
	
	/**
	 * Extrait les adresses e-mails présentes dans une chaine.
	 * La fonction retourne un tableau des adresses e-mails. Si
	 * des adresses e-mails se trouvent en doublon dans la chaine,
	 * alors la fonction ne gardera dans le tableau qu'un seul exemplaire
	 * des adresses e-mails.
	 *
	 * @author Hugo HAMON <webmaster@apprendre-php.com>
	 * @licence LGPL
	 * @param string $sChaine la chaine contenant les e-mails
	 * @return array $aEmails[0] Tableau dédoublonné des e-mails
	 */	
	public static function extractEmailsFromString($sChaine) 
	{
		if(false !== preg_match_all('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $sChaine, $aEmails)) {
			if(is_array($aEmails[0]) && sizeof($aEmails[0])>0) {
				return array_unique($aEmails[0]);
			}
		}
		return null;
	}	

	/**
	 * Le petit script qui suit permet de retourner une chaine de caractères en s'assurant 
	 * que les balises définies dans la fonction soient fermées proprement.
	 * @param $str : the string to cut
	 * @param $size : the size of the return string
	 * @return string
	 * Infos:
	 * Function based on function https://www.slashorg.net/read-17-Text-Cut.html
	 */
	public static function cutHtmlString($str, $size)  
	{
		static $tags = array ('html', 'body', 'div', 'span', 'applet', 'object', 'iframe',
			'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote', 'pre',
			'a', 'abbr', 'acronym', 'address', 'big', 'cite', 'code',
			'del', 'dfn', 'em', 'font', 'img', 'ins', 'kbd', 'q', 's', 'samp',
			'small', 'strike', 'strong', 'sub', 'sup', 'tt', 'var',
			'b', 'u', 'i', 'center',
			'dl', 'dt', 'dd', 'ol', 'ul', 'li',
			'fieldset', 'form', 'label', 'legend',
			'table', 'caption', 'tbody', 'tfoot', 'thead', 'tr', 'th', 'td'
		);
	    
	    $str_len = strlen($str);

	    if($str_len > $size) {
	    
	    	$open_tags = array ();
	    	$pos = 0;
			
	    	$str .= ' <';
	    
		    while ($pos < $str_len && $pos < $size) {
		    	 $pos = min(strpos($str, ' ', $pos), strpos($str, '<', $pos));
		
		        if ($str[$pos] == '<') {
		            if ($str[$pos + 1] == '/') {
		                array_pop($open_tags);
		            } else {
		                $sub = substr($str, $pos + 1, min(strpos($str, ' ', $pos), strpos($str, '>', $pos)) - $pos - 1);
		                if (in_array($sub, $tags)) {
		                    array_push($open_tags, $sub);
		                }
		            }
					if (strpos($str, '>', $pos) == "") {
						break;
					} else {
						$pos = strpos($str, '>', $pos) + 1;
					}
		        } else {
		            $pos++;
		        }
		
		    }
	
	    	$str = substr($str, 0, $pos);
	
		    if (count($open_tags) > 0) {
		        foreach($open_tags as $value) {
		            $str .= '</' . array_pop($open_tags) . '>';
		        }
		    }
	    
	    }
	
	    return($str);
	}	
	
	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @return string Trimmed string.
	 * 
	 * Fonction extraite : http://www.ycerdan.fr/php/tronquer-un-texte-en-conservant-les-tags-html-en-php/
	 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */
	 
	function truncateHtmlString($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
	    if (is_array($ending)) {
	        extract($ending);
	    }
	    if ($considerHtml) {
	        if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
	            return $text;
	        }
	        $totalLength = mb_strlen($ending);
	        $openTags = array();
	        $truncate = '';
	        preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
	        foreach ($tags as $tag) {
	            if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
	                if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
	                    array_unshift($openTags, $tag[2]);
	                } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
	                    $pos = array_search($closeTag[1], $openTags);
	                    if ($pos !== false) {
	                        array_splice($openTags, $pos, 1);
	                    }
	                }
	            }
	            $truncate .= $tag[1];
	 
	            $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
	            if ($contentLength + $totalLength > $length) {
	                $left = $length - $totalLength;
	                $entitiesLength = 0;
	                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
	                    foreach ($entities[0] as $entity) {
	                        if ($entity[1] + 1 - $entitiesLength <= $left) {
	                            $left--;
	                            $entitiesLength += mb_strlen($entity[0]);
	                        } else {
	                            break;
	                        }
	                    }
	                }
	 
	                $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
	                break;
	            } else {
	                $truncate .= $tag[3];
	                $totalLength += $contentLength;
	            }
	            if ($totalLength >= $length) {
	                break;
	            }
	        }
	 
	    } else {
	        if (mb_strlen($text) <= $length) {
	            return $text;
	        } else {
	            $truncate = mb_substr($text, 0, $length - strlen($ending));
	        }
	    }
	    if (!$exact) {
	        $spacepos = mb_strrpos($truncate, ' ');
	        if (isset($spacepos)) {
	            if ($considerHtml) {
	                $bits = mb_substr($truncate, $spacepos);
	                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
	                if (!empty($droppedTags)) {
	                    foreach ($droppedTags as $closingTag) {
	                        if (!in_array($closingTag[1], $openTags)) {
	                            array_unshift($openTags, $closingTag[1]);
	                        }
	                    }
	                }
	            }
	            $truncate = mb_substr($truncate, 0, $spacepos);
	        }
	    }
	 
	    $truncate .= $ending;
	 
	    if ($considerHtml) {
	        foreach ($openTags as $tag) {
	            $truncate .= '</'.$tag.'>';
	        }
	    }
	 
	    return $truncate;
	}	
	
	/**
	 * Multibyte capable wordwrap
	 *
	 * @param string $str
	 * @param int $width
	 * @param string $break
	 * @return string
	 */
	public static function mb_wordwrap($str, $width=74, $break="\r\n")
	{
		// todo optimisation -- fonction trop lente si le post est long ...
		//throw new Exception('point E - '.$str_width);
		
	    // Return short or empty strings untouched
	    if(empty($str) || mb_strlen($str, 'UTF-8') <= $width)
	        return $str;
	  
	    $br_width  = mb_strlen($break, 'UTF-8');
	    $str_width = mb_strlen($str, 'UTF-8');
	    $return = '';
	    $last_space = false;
	    
	    for($i=0, $count=0; $i < $str_width; $i++, $count++)
	    {
	        // If we're at a break
	        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
	        {
	            $count = 0;
	            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
	            $i += $br_width - 1;
	            continue;
	        }
	
	        // Keep a track of the most recent possible break point
	        if(mb_substr($str, $i, 1, 'UTF-8') == " ")
	        {
	            $last_space = $i;
	        }
	
	        // It's time to wrap
	        if ($count > $width)
	        {
	            // There are no spaces to break on!  Going to truncate :(
	            if(!$last_space)
	            {
	                $return .= $break;
	                $count = 0;
	            }
	            else
	            {
	                // Work out how far back the last space was
	                $drop = $i - $last_space;
	
	                // Cutting zero chars results in an empty string, so don't do that
	                if($drop > 0)
	                {
	                    $return = mb_substr($return, 0, -$drop);
	                }
	               
	                // Add a break
	                $return .= $break;
	
	                // Update pointers
	                $i = $last_space + ($br_width - 1);
	                $last_space = false;
	                $count = 0;
	            }
	        }
	
	        // Add character from the input string to the output
	        $return .= mb_substr($str, $i, 1, 'UTF-8');
	    }
	    return $return;
	}		
	
}

?>