<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of PayPalButtons, a plugin for Dotclear 2.
#
# Copyright (c) http://www.exinsidephp.com/
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class PaypalCrypt{
	
    private $privateKey = '';
    private $publicKey = '';
    private $paypalKey = '';
    private $pathOpenSSL = '/usr/bin/openssl';
    private $data = array(
        'bn' => 'Boutique_BuyNow_WPS_FR',
        'cmd' => '_xclick',
        'lc' => 'FR',
        'custom' => '',
        'invoice' => '',
        'currency_code' => 'EUR',
        'charset' => 'UTF-8',
        'no_shipping' => '1'
    );
	
	public static function PayPalFormEncrypt()
	{	
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		if ($s->PayPalButtons_testing) {
			$business = $s->PayPalButtons_testing_account;
		} else {
			$business = $s->PayPalButtons_selling_account;
		}
		$paypalCrypt = new PaypalCrypt();
		$paypalCrypt->setPrivateKey($url.'/public/paypalbuttons/clepriv.pem');
		$paypalCrypt->setPublicKey($url.'/public/paypalbuttons/certpub.pem');
		$paypalCrypt->setPaypalKey($url.'/public/paypalbuttons/paypal_cert.pem');
		$paypalCrypt->setOpenSSLPath($s->PayPalButtons_OpenSSL_path);
		$paypalCrypt->addData('cert_id',$s->PayPalButtons_certificate_ID)
					->addData('business',$business)
					->addData('no_note','0')
					->addData('shipping','0')
					->addData('tax','0')
					->addData('rm','2')
					->addData('cbt','Retour à la boutique')
					->addData('custom','id_membre')
					->addData('return',$core->blog->url.$core->url->getBase('paypal').'/validate')
					->addData('cancel_return',$core->blog->url.$core->url->getBase('paypal').'/cancel')
					->addData('notify_url',$core->blog->url.$core->url->getBase('paypal').'/notify')
					->addData('amount','10')
					->addData('item_name', 'Boite à meuh')
					->addData('item_number', 'identifiant_produit');
		$data = $paypalCrypt->getCryptedData();
		$res = 
		'<form action="https://www.paypal.com/fr/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="<?php echo $data?>"/>
			<input type="submit" value="Commander" class="input_button">
		</form>';
	}
	
    public function __construct(){
        // Nothing
    }
 
    public function addData($key, $data){
        $this->data[$key] = $data;
        return $this;
    }
 
    public function setPrivateKey($privateKey){
        $this->privateKey = $privateKey;
        return $this;
    }
 
    public function setPublicKey($publicKey){
        $this->publicKey = $publicKey;
        return $this;
    }
 
    public function setPaypalKey($paypalKey){
        $this->paypalKey = $paypalKey;
        return $this;
    }
 
    public function getCryptedData(){
        if (!file_exists($this->privateKey))
            throw new Exception('ERROR: MY_KEY_FILE '.$this->privateKey.' not found');
        if (!file_exists($this->publicKey))
            throw new Exception('ERROR: MY_CERT_FILE '.$this->publicKey.' not found');
        if (!file_exists($this->paypalKey))
            throw new Exception('ERROR: PAYPAL_CERT_FILE '.$this->paypalKey.' not found');
 
        $openssl_cmd = "$this->pathOpenSSL  smime -sign -signer $this->publicKey  -inkey $this->privateKey ".
                "-outform der -nodetach -binary| $this->pathOpenSSL smime -encrypt ".
                "-des3 -binary -outform pem $this->paypalKey";
 
        $descriptors = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
 
        $process = proc_open($openssl_cmd, $descriptors, $pipes);
        if (is_resource($process)) {
            foreach ($this->data as $key => &$value)
                if ($value != "")
                    fwrite($pipes[0], "$key=$value\n");
            fflush($pipes[0]);
            fclose($pipes[0]);
 
            $output = "";
            while (!feof($pipes[1]))
                $output .= fgets($pipes[1]);
 
            fclose($pipes[1]);
            $return_value = proc_close($process);
            return $output;
        }
        throw new Exception('ERROR: encryption failed');
    }
 
    public function setOpenSSLPath($path){
        if(!file_exists($path))
            throw new Exception('OpenSSLPath "'.$path.'" does not exist');
        $this->pathOpenSSL = $path;
    }
}
?>