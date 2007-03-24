<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse2, a plugin for DotClear. 
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
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



class bayesian
{
	private $core;
	private $con;
	private $table;
	private $val_hapax;
	private $sct_spam;
	private $sct_ham;
	private $bias;
	private $retrain_limit;
	private	$training_mode;
	private $tum_maturity;
			
	public function __construct(&$core)
	{	
		$this->core =& $core;
		$this->con =& $core->con;
		$this->table = $core->prefix.'spam_token';
		$this->val_hapax = 0.45; # hapaxial value
		$this->sct_spam = 0.9999; # single corpus token (spam) probability
		$this->sct_ham = 0.0001; # single corpus token (ham) probability
		$this->bias = 1; # bias used in the computing of the word probability
		$this->retrain_limit = 5; # number of retries when retraining a message
		$this->tum_maturity = 20; # number of hits for a token to be considered as mature 
		$this->training_mode = 'TUM'; 
		/* valid values for training_mode are  :
			'TEFT' : train everything
				+ works well if the amount of spam is not greater than 80% of the amount of ham
				+ can cope with blogs having constantly changing comments
				- can cause errors if the amount of spam >> amount of ham	
				- resource hungry, not for large volume of comments
				- creates about 70% of uninteresting data in the dataset
			'TOE' : train on error
				+ can deal with large volume of spams
				+ disk space use much lower than TEFT
				+ works well if the spam ratio is greater than 90%
				- false positives, very poor accuracy for blogs with constantly changing comments
				- slow at learning new types of spam
			'TUM' : train until mature
				+ middle ground between TEFT and TOE
				+ like TEFT, learns new data but stops when it has matured
				+ quick retrain
				+ best for medium volume of comments
		*/
	}
	

	public function handle_new_message($author,$email,
		$site,$ip,$content) {
		$spam = 0;
		$tok = $this->tokenize($author,$email,
		$site,$ip,$content);
		$proba = $this->get_probabilities($tok);
		$p = $this->combine($proba);
		if ($p > 0.5) {
			$spam = 1;
		}
		if ($this->training_mode != 'TOE') {
			$this->basic_train($tok, $spam);
		}
		
		$result = null;
		if ($p < 0.1) {
			$result = false;
		} else if ($p > 0.5) {
			$result = true;
		}
		return $result;	
	}

	public function retrain($author,$email,$site,$ip,$content, $spam) {

		$tok = $this->tokenize($author,$email,$site,$ip,$content);
		# we neutralize the dataset for this message
		# FIXME : check if this is necessary
		#$this->basic_train($tok, $spam, true);
	
		# we retrain the dataset with this message until the
		#	probability of this message to be a spam changes
		$init_spam = $current_spam = 0;
		$proba = $this->get_probabilities($tok);
		$p = $this->combine($proba);
		if ($p > 0.5) {
			$init_spam = $current_spam = 1;
		}
		$count = 0;
		do {
			$proba = $this->get_probabilities($tok);
			$p = $this->combine($proba);
			if ($p > 0.5) {
				$current_spam = 1;
			} else {
				$current_spam = 0;
			}
			$count++;
			$this->basic_train($tok, $spam, true);
		} while (($init_spam == $current_spam) && ($count < $this->retrain_limit));
	}
	
	/**
	@function decode
		decodes the input string, 
		for the moment, it deletes the html tags and comments
		TODO : decode the urls
	@param	string	$s		input string
	@return string			output string
	*/
	private function decode($s) {
		$s = preg_replace('/&lt;/ism', '<', $s);
		$s = preg_replace('/&gt;/ism', '>', $s);
		$s = preg_replace('/&quot;/ism', '"', $s);				
		
		$s = preg_replace('/<a href="([^"\'>]*)">([^<]+)<\/a>/ism', ' $2 $1 ', $s);
		$s = preg_replace('/<!-- .* -->/Uism', ' ', $s);
		# TODO : shall we keep the content of the html comments? cf comments storage in dc
		$s = strip_tags($s);
		$s = trim($s); 

		return $s;
	}


	/**
	@function tokenize
		tokenization of a comment
	@param	FIXME doc
	@return array			token array
	*/
	private function tokenize($m_author,$m_email,
		$m_site,$m_ip,$m_content) {

		$url_t = new url_tokenizer();
		$email_t = new email_tokenizer();
		$ip_t = new ip_tokenizer();
		$html_t = new html_tokenizer();
		$red_t = new redundancies_tokenizer();
		$rea_t = new reassembly_tokenizer();

		# headers handling
			$nom = $mail = $site = $ip = $contenu = array();

		# name
		$elem = $url_t->create_token($this->decode($m_author), 'Hname');
		$nom = array($elem);
		$nom = $url_t->tokenize($nom);	
		$nom = $email_t->tokenize($nom);	
		$nom = $ip_t->tokenize($nom);
		$nom = $html_t->tokenize($nom);
		$nom = $red_t->tokenize($nom);
		$nom = $rea_t->tokenize($nom);
		$nom = $rea_t->default_tokenize($nom);

		
		# mail
		$elem = $url_t->create_token($this->decode($m_email), 'Hmail');
		$mail = array($elem);
		$mail = $email_t->tokenize($mail);
		$mail = $email_t->default_tokenize($mail);
		
		# website
		$elem = $url_t->create_token($this->decode($m_site), 'Hsite');
		$site = array($elem);
		$site = $url_t->tokenize($site);
		$site = $url_t->default_tokenize($site);
		

		# ip

		$elem = $url_t->create_token($this->decode($m_ip), 'Hip');
		$ip = array($elem);
		$ip = $ip_t->tokenize($ip);
		$ip = $ip_t->default_tokenize($ip);

		
		# content handling
		$elem = $url_t->create_token($this->decode($m_content), '');
		$contenu = array($elem);
		$contenu = $url_t->tokenize($contenu);
		$contenu = $email_t->tokenize($contenu);
		$contenu = $ip_t->tokenize($contenu);
		$contenu = $html_t->tokenize($contenu);
		$contenu = $red_t->tokenize($contenu);
		$contenu = $rea_t->tokenize($contenu);
		$contenu = $rea_t->default_tokenize($contenu);	

		# result
		$tok = array_merge($nom, $mail, $site, $ip, $contenu);
		$tok = $this->clean_tokenized_string($tok);
		return $tok;
	}

	/**
	@function clean_tokenized_string
		gives a simple array of strings from an array of tokens
	@param  array	$tok		array of tokens
	@return array			array of tokens	or array of strings	
	*/
	private function clean_tokenized_string($tok) {
		$token = array();

		foreach ($tok as $i) {
			$token[] = $i['elem'];
		}

		return $token;
	}

	/**
	@function get_probabilities
		gives probabilities for each token
	@param  array	$tok		array of strings
	@return array				array of probabilities
	*/
	private function get_probabilities($tok) {
		$proba = array();

		foreach ($tok as $i) {
			$p = $this->val_hapax;
			$strReq = 'SELECT token_nham, token_nspam, token_p FROM '.$this->table.' WHERE token_id = \''.$i.'\''; 	
			$rs = $this->con->select($strReq);		
			if (!$rs->isEmpty()) {
					$p = $rs->token_p;	
			}
			$proba[] = $p;
		}

		return $proba;
	}

	/**
	@function basic_train_unit
	param array 	$t			one token
	param integer	$spam		1 if spam
	param boolean	$retrain	true if the message was already trained
	*/

	private function basic_train_unit($t, $spam, $retrain = false) {

		#echo "**".$t."**\n";

		$strReq = 'SELECT COUNT(token_nham) FROM '.$this->table; 	
		$rs = $this->con->select($strReq);
		$total_ham = $rs->f(0);
		
		$strReq = 'SELECT COUNT(token_nspam) FROM '.$this->table; 	
		$rs = $this->con->select($strReq);
		$total_spam = $rs->f(0);	

		$known_tokens = array();
		$n_known_tokens = 0;
		$n_unknown_tokens = 0;

		# we determine if the token is already in the dataset
		$strReq = 'SELECT token_nham, token_nspam, token_p, token_mature FROM '.$this->table.' WHERE token_id = \''.$t.'\''; 	
		$rs = $this->con->select($strReq);
	
		if (!$rs->isEmpty()) {
			$known_tokens[] = array('token_id' => $t, 'token_nham' => $rs->token_nham, 'token_nspam' => $rs->token_nspam, 'token_p' => $rs->token_p, 'token_mature' => $rs->token_mature);
			$n_known_tokens++;
			if ($retrain) {
				# we test if it is possible to move the state of the token
				# if it is present in 0 ham and we try to pass it in spam, we have a problem
				if (!(($spam && (!$rs->token_nham)) || ((!$spam) && (!$rs->token_nspam)))) {
					$known_tokens[] = array('token_id' => $t, 'token_nham' => $rs->token_nham, 'token_nspam' => $rs->token_nspam, 'token_p' => $rs->token_p, 'token_mature' => $rs->token_mature);
					$n_known_tokens++;
				} else {
					return;	
				}
			} else {
				$known_tokens[] = array('token_id' => $t, 'token_nham' => $rs->token_nham, 'token_nspam' => $rs->token_nspam, 'token_p' => $rs->token_p, 'token_mature' => $rs->token_mature);
				$n_known_tokens++;
			}
		} else {
			$n_unknown_tokens++;
		}
		

		# we compute the new values for total_spam and total_ham
		if ($spam) {
			$total_spam += $n_known_tokens;
			$total_spam += $n_unknown_tokens;
			if ($retrain) {
				$total_ham  -= $n_known_tokens;
			}
		} else {
			$total_ham  += $n_known_tokens;
			$total_ham  += $n_unknown_tokens;
			if ($retrain) {
				$total_spam -= $n_known_tokens;
			}
		}



		if ($n_known_tokens != 0) {
			$i = $known_tokens[0];
			if (($this->training_mode != 'TUM') || ($i['token_mature'] != 1)) {

	
				# update
				# nr of occurences in each corpuses
				$nspam = 0;
				$nham = 0;
				$nr = 0;
				if ($spam) {
					$nspam = $i['token_nspam'] + 1;
					if ($retrain) {
						$nham = $i['token_nham'] - 1;
					} else {
						$nham = $i['token_nham'];
					}
				} else {
					if ($retrain) {
						$nspam = $i['token_nspam'] - 1;
					} else {
						$nspam = $i['token_nspam'];
					}
					$nham = $i['token_nham'] + 1;
				}
				$nr = $nspam*2 + $nham; # number of occurences in the two corpuses
					
				# hapaxes handling
				if ($nr < 5) {
					$p = $this->val_hapax;
				} else if ($nspam == 0) { # single corpus token handling
					$p = $this->sct_ham;
				} else if ($nspam == 0) {
					$p = $this->sct_spam;
				} else {
					$p = $this->compute_proba($nham, $nspam, $total_ham, $total_spam);
					if ($p >= 1) {
						$p = $this->sct_spam;
					}
					if ($p <= 0) {
						$p = $this->sct_ham;
					}	
				}
				if ($this->training_mode == 'TUM') {
					# evaluate token maturity
					$maturity = ($nr >= $this->tum_maturity)?1:0;
					$strReq = 'UPDATE '.$this->table.' SET token_nham='.$nham.', token_nspam='.
							$nspam.', token_mdate=\''.date('Y-m-d H:i:s').'\', token_p=\''.
							$p.'\', token_mature=\''.$maturity.'\' WHERE token_id=\''.$i['token_id'].'\'';
					#echo $strReq."\n";
					$this->con->execute($strReq);						
				} else {
					$strReq = 'UPDATE '.$this->table.' SET token_nham='.$nham.', token_nspam='.
							$nspam.', token_mdate=\''.date('Y-m-d H:i:s').'\', token_p=\''.
							$p.'\' WHERE token_id=\''.$i['token_id'].'\'';
					#echo $strReq."\n";
					$this->con->execute($strReq);
				}
			}
		}
		
		if ($n_unknown_tokens != 0) { # unknown token
			#insert an hapax
			$nspam = 0;
			$nham = 0;
			if ($spam) {
				$nspam = 1;
			} else {
				$nham = 1;
			}
			$p = $this->val_hapax;
			$strReq = 'INSERT INTO '.$this->table.' (token_id, token_nham, token_nspam, token_mdate, token_p) VALUES (\''.$t.'\','.$nham.','.$nspam.',\''.date('Y-m-d H:i:s').'\' ,\''.$p.'\')';
			#echo $strReq."\n";
			$this->con->execute($strReq);
		}
	}

	private function basic_train($tok, $spam, $retrain = false) {
		foreach ($tok as $t) {
			$this->basic_train_unit($t, $spam, $retrain);
		}
		exit;	
	}


	/**
	@function compute_proba

	*/
	private function compute_proba($nham, $nspam, $total_ham, $total_spam) {

		if ($total_spam == 0) 
			$total_spam++;

		if ($total_ham == 0)
			$total_ham++;

		$a = ($nspam / $total_spam);
		$b = ($nham / $total_ham);
		if ($this->bias) {
			$b = 2*$b;	
		}
		$p = $a / ($a + $b);
		
		return $p;
	}


	/**
	@function inverse_chi_square
			computes the inverse chi square function
			adapted from C version ("Ending Spam", Jonathan Zdziarski, p. 79)
	*/
	private function inverse_chi_square($x, $v) {
		$i = 0;
		
		$m = $x / 2;
		$s = exp(0-$m);
		$t = $s;
		
		for ($i=1; $i<($v/2); $i++) {
			$t *= $m / $i;
			$s += $t;	
		}
		return ($s < 1)? $s : 1;
	}


	/**
	@function combine
			computes final probability of a message using Fisher-Robinson's inverse Chi-Square
	*/
	private function combine($proba) {
		# filter useful data (probability in [0;0.1] or [0.9;1]
		foreach ($proba as $key => $p) {
			if (($p> 0.1) && ($p<0.9)) {
				unset($proba[$key]);	
			}
		}
		
		$n = count($proba);
		$i = 0.5;
		if ($n != 0) {
			$prod1 = 1;
			$prod2 = 1;
			foreach ($proba as $p) {
				$prod1 *= $p;
				$prod2 *= (1-$p);
			}
			
			$h = $this->inverse_chi_square(-2* log($prod1), 2*$n);
			$s = $this->inverse_chi_square(-2* log($prod2), 2*$n);
			$i = (1 + $h - $s) /2;
		}	
		return $i;
	}



	/**
	@function test
		test this class
	*/
	public function test() {

		$s1 = 'http://192.168.0.1/plop.coin?turlut lala <a href="http://plop.com/truc">test</a> machin<!-- commentaire -->'; // url1 ip
		$s2 = '<a href="http://plop.com/truc">test</a> machin<!-- commentaire -->'; // url2
		$s3 = 'plop viagra!!!!!!???!!!'; // clean_redundancies
		$s4 = 'plop v.i.a.g.r.a soja'; // token reassembly
		$s5 = ' <a href="mailto:evil-spammer@pr0n.org">unsubscribe</a> '; // email
		$s6 = ' Description int preg_match ( string pattern, string subject [, array &matches [, int flags [, int offset]]] )Searches subject for a match to the regular expression given in pattern. If matches is provided, then it is filled with the results of search. $matches[0] will contain the 192.168.0.1 text that matched the full pattern, $matches[1] will have the text that matched the first captured parenthesized subpattern, and so on. '; // content

		$s = $s1.$s2.$s3.$s4.$s5.$s6;
	
		echo '<pre>';
		$elem = array(	'elem' => $this->decode(''),
				'prefix' => 'plop', 
				'final' => 0
				);

		echo $this->decode($s).'<br />'."\n";
		$contenu = array($elem);
/*		$contenu = $this->tokenize_url($contenu);
		$contenu = $this->tokenize_email($contenu);
		$contenu = $this->tokenize_ip($contenu);
		$contenu = $this->tokenize_html($contenu);
		$contenu = $this->clean_redundancies($contenu);
		$contenu = $this->token_reassembly($contenu);
		$contenu = $this->default_tokenize($contenu);
		$contenu = $this->clean_tokenized_string($contenu);
*/
		print_r($contenu);
		echo '</pre>';
	}

}

#$t = new bayesian_filter();
#$t->test();

?>
