<?php
/**
 * class Shared_Model_AgentType
 * USER_AGENT判定クラス
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_AgentType
{

	const AGENT_TYPE_IPHONE  = 1;
	const AGENT_TYPE_IPAD    = 2;
	const AGENT_TYPE_IPOD    = 3;
	
	const AGENT_TYPE_ANDROID = 10;
	
	const AGENT_TYPE_WINDOWSPHONE = 20;
	
	const AGENT_TYPE_PC      = 100;
	

    /**
     * getAgentType
     * @param none
     */
    public static function getAgentType($ua)
    {
			
		//$useragents = array(
		//'iPhone',         // Apple iPhone
		//'iPad',           // Apple iPad
		//'iPod',           // Apple iPod touch
		//'Android',        // 1.5+ Android
		//'Windows Phone',
		//);
		//$pattern = implode("|", $useragents);
		
		if(preg_match('/iPhone/', $ua)){
			return self::AGENT_TYPE_IPHONE;

		}else if(preg_match('/iPad/', $ua)){
			return self::AGENT_TYPE_IPAD;
					
		}else if(preg_match('/iPod/', $ua)){
			return self::AGENT_TYPE_IPOD;		

		}else if(preg_match('/Android/', $ua)){
			return self::AGENT_TYPE_ANDROID;

		}else if(preg_match('/Windows Phone/', $ua)){
			return self::AGENT_TYPE_WINDOWSPHONE;
				
		} else {
			return self::AGENT_TYPE_PC;
			
		
		}
    }


}