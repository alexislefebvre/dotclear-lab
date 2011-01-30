<?php
if (!defined('DC_RC_PATH')){return;}

class oAuthClient
{
	public static function info($core,$service,$info='')
	{
		try
		{
			$behaviors = $core->getBehaviors('oAuthManagerClientLoader');
			if (empty($behaviors)) return false;
			
			foreach($behaviors as $behavior)
			{
				$client = call_user_func($behavior);
				
				if (!empty($client) && !empty($client['id']) 
				&& $client['id'] == $service)
				{
					if (empty($info))
					{
						return $client;
					}
					elseif (!empty($client['info']))
					{
						return $client['info'];
					}
					else
					{
						return false;
					}
				}
			}
		}
		catch (Exception $e) {}
		
		return false;
	}
	
	public static function load($core,$service,$config)
	{
		try
		{
			$behaviors = $core->getBehaviors('oAuthManagerClientLoader');
			if (empty($behaviors)) return false;
			
			foreach($behaviors as $behavior)
			{
				$client = call_user_func($behavior);
				
				if (!empty($client) && !empty($client['id']) 
				&& $client['id'] == $service && !empty($client['loader'])) 
				{
					return new $client['loader']($core,$config);
				}
			}
		}
		catch (Exception $e) {}
		
		return false;
	}
}
?>