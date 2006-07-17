<?php
/*
TODO :
- dans le token array, remplacer final = 0,1 par true, false
- vérifier que tout marche bien en uppercase / lowercase
- vérifier que tout marche bien en utf 
	-> passer les regexp en utf
	-> vérifier que toutes les fonctions de manipulation de chaines utilisées marchent bien en utf
	-> vérifier que default_tokenize, token_reassembly marchent bien en utf (pb lecture caractère par caractère)
- commenter, phpdoc, documenter
- faire le lien avec le dataset
 
*/

require_once(dirname(__FILE__).'/tokenizers/class.url_tokenizer.php');
require_once(dirname(__FILE__).'/tokenizers/class.email_tokenizer.php');
require_once(dirname(__FILE__).'/tokenizers/class.ip_tokenizer.php');
require_once(dirname(__FILE__).'/tokenizers/class.html_tokenizer.php');
require_once(dirname(__FILE__).'/tokenizers/class.redundancies_tokenizer.php');
require_once(dirname(__FILE__).'/tokenizers/class.reassembly_tokenizer.php');



class bayesian_filter
{
	private $core;
	private $con;
	private $table;
	private $val_hapax;
	private $sct_spam;
	private $sct_ham;
	private $bias;
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $core->con;
		$this->table = $core->prefix.'spam_token';
		$this->val_hapax = 0.45; # hapaxial value
		$this->sct_spam = 0.99; # single corpus token (spam) probability
		$this->sct_ham = 0.01; # single corpus token (ham) probability
		$this->bias = 1; # bias used in the computing of the word probability 
		
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
	@param	array	$comment	comment
	@return array			token array
	*/
	public function tokenize(&$comment) {

		$url_t = new url_tokenizer();
		$email_t = new email_tokenizer();
		$ip_t = new ip_tokenizer();
		$html_t = new html_tokenizer();
		$red_t = new redundancies_tokenizer();
		$rea_t = new reassembly_tokenizer();

		# headers handling
			$nom = $mail = $site = $ip = $contenu = array();

		# name
		$elem = $url_t->create_token($this->decode($comment->comment_author), 'Hname', 0);
		$nom = array($elem);
		$nom = $url_t->tokenize($nom);
		$nom = $email_t->tokenize($nom);
		$nom = $ip_t->tokenize($nom);
		$nom = $html_t->tokenize($nom);
		$nom = $red_t->tokenize($nom);
		$nom = $rea_t->tokenize($nom);
		$nom = $rea_t->default_tokenize($nom);	
		
		# mail
		$elem = $url_t->create_token($this->decode($comment->comment_email), 'Hmail', 0);
		$mail = array($elem);
		$mail = $email_t->tokenize($mail);
		$mail = $email_t->default_tokenize($mail);
		
		# website
		$elem = $url_t->create_token($this->decode($comment->comment_site), 'Hsite', 0);
		$site = array($elem);
		$site = $url_t->tokenize($site);
		$site = $url_t->default_tokenize($site);
		

		# ip
		$elem = $url_t->create_token($this->decode($comment->comment_ip), 'Hip', 0);
		$ip = array($elem);
		$ip = $ip_t->tokenize($ip);
		$ip = $ip_t->default_tokenize($ip);
		
		# content handling
		$elem = $url_t->create_token($this->decode($comment->comment_content), '', 0);
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
	@function basic train

	*/
	private function basic_train($tok, $spam) {

		$strReq = 'SELECT COUNT(token_nham) FROM '.$this->table; 	
		$rs = $this->con->select($strReq);
		$total_ham = $rs->f(0);
		
		$strReq = 'SELECT COUNT(token_nspam) FROM '.$this->table; 	
		$rs = $this->con->select($strReq);
		$total_spam = $rs->f(0);	



		foreach ($tok as $i) {
			$strReq = 'SELECT token_nham, token_nspam, token_p FROM '.$this->table.' WHERE token_id = \''.$i.'\''; 	
			$rs = $this->con->select($strReq);
		
			if (!$rs->isEmpty()) {
				# update
				# nr of occurences in each corpuses
				$nspam = 0;
				$nham = 0;
				if ($spam) {
					$nspam = $rs->token_nspam +1;
					$total_spam++;
					$nham = $rs->token_nham;
				} else {
					$nspam = $rs->token_nspam;
					$nham = $rs->token_nham+1;
					$total_ham++;
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
				}
				$strReq = 'UPDATE '.$this->table.' SET token_nham='.$nham.', token_nspam='.$nspam.', token_mdate=\''.date('Y-m-d H:i:s').'\', token_p=\''.$p.'\' WHERE token_id=\''.$i.'\'';
				#echo $strReq;
				$this->con->execute($strReq);
			} else {
				#insert an hapax
				$nspam = 0;
				$nham = 0;
				if ($spam) {
					$nspam = 1;
				} else {
					$nham = 1;
				}
				$p = $this->val_hapax;
				$strReq = 'INSERT INTO '.$this->table.' (token_id, token_nham, token_nspam, token_mdate, token_p) VALUES (\''.$i.'\','.$nham.','.$nspam.',\''.date('Y-m-d H:i:s').'\' ,\''.$p.'\')';
				$this->con->execute($strReq);
			}
		}

	}

	/**
	@function compute_proba

	*/
	private function compute_proba($nham, $nspam, $total_ham, $total_spam) {
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

	public function handle_new_message(&$msg) {
		$spam = 0;
		$tok = $this->tokenize($msg);
		//print_r($tok);
		$proba = $this->get_probabilities($tok);
		//print_r($proba);
		$p = $this->combine($proba);
		if ($p > 0.5) {
			$spam = 1;
		}
		$this->basic_train($tok, $spam);
		
		return $spam;	
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
