<?php
if (!defined('DC_RC_PATH')){return;}

class joliprintSoCialMeSharerService extends soCialMeService
{
	protected $define = array(
		'id' => 'joliprint',
		'name' => 'Joliprint',
		'home' => 'http://joliprint.com',
		'icon' => 'index.php?pf=joliprint/icon.png'
	);
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	protected $available = true;
	protected $part = 'sharer';
	
	public function playIconContent($record)
	{
		return $this->parseHTML('joliprint-icon.png',$record);
	}
	
	public function playBigContent($record)
	{
		return $this->parseHTML('joliprint-share-style.png',$record);
	}
	
	public function playSmallContent($record)
	{
		return $this->parseHTML('joliprint-share-button.png',$record);
	}
	
	private function parseHTML($type,$record)
	{
		if (!$record || empty($record['url'])) return;
		
		return soCialMeUtils::preloadBox(joliprint::toHTML(array('url'=>$record['url'],'button'=>$type)));
	}
}
?>