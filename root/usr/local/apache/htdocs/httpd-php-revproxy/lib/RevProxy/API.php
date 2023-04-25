<?php
/** 
 * Server.php contains the RevProxy_API class
 *
 * @author T Ace
 * @package httpd-php-revproxy
 */
/**
 * RevProxy_API class contains helper methods for proxying servers
 *
 * @author T Ace
 * @package httpd-php-revproxy
 */
class RevProxy_API
{
    /**
     * The configuration array
     * @var RevProxy_Config $_config
     */
    private $_config = NULL;

    public function __construct(RevProxy_Config $config)
    {
        $this->_config = $config;
    }

	public static function sortByOneKey(array $array, $key, $asc = true) {
	    $result = array();
        
	    $values = array();
	    foreach ($array as $id => $value) {
	        $values[$id] = isset($value[$key]) ? $value[$key] : '';
	    }
        
	    if ($asc) {
	        asort($values);
	    }
	    else {
	        arsort($values);
	    }
        
	    foreach ($values as $key => $value) {
	        $result[$key] = $array[$key];
	    }
        
	    return $result;
	}

	public function getRedirects()
	{
		if (($file = yaml_parse_file($this->_config->get('redirects_file'), -1)) !== NULL)
		{
			return $file[0];
		}
		return array();
	}
	
	public function setRedirects($redirects)
	{
		file_put_contents($this->_config->get('redirects_file'), yaml_emit($redirects), LOCK_EX);
		$this->writeHtaccess();
	}
	
	public function getCredentials()
	{
		if (($file = yaml_parse_file($this->_config->get('credentials_file'), -1)) !== NULL)
		{
			return $file[0];
		}
		return array();
	}
	
	public function setCredentials($credentials)
	{
		file_put_contents($this->_config->get('credentials_file'), yaml_emit($credentials), LOCK_EX);
		$this->writeHtpasswd();
	}

	public function getIps()
	{
		if (($file = yaml_parse_file($this->_config->get('iplist_file'), -1)) !== NULL)
		{
			return $file[0];
		}
		return array();
	}
	
	public function getPermanentIps()
	{
		return array(
			"10.0.0.0/8",
			"172.16.0.0/12",
			"192.168.0.0/16",
		);
	}

	public function setIps($ips)
	{
		file_put_contents($this->_config->get('iplist_file'), yaml_emit($ips), LOCK_EX);
		$this->writeHtaccess();
	}
	
	private function writeHtaccess()
	{
		$redirects = $this->getRedirects();
		$ips = $this->getIps();
		$permanent_ips = $this->getPermanentIps();

		$htaccess_content = "# contents generated\n";
		$htaccess_content .= "# Any changes will be overwritten!\n";
		$htaccess_content .= "# Generated at ".strftime('%a %d %b %H:%M:%S %Y',time())."\n\n";

		$htaccess_content .= "RequestHeader set X-Client-IP %{REMOTE_ADDR}s\n\n";

		$default_redirect = NULL;
		$htaccess_content .= "RewriteEngine On\n";
		$htaccess_content .= "RewriteBase /\n";
		foreach ($redirects as $redirect)
		{
			if (isset($redirect['enabled']) && $redirect['enabled'] && isset($redirect['source']) && isset($redirect['destination']))
			{
				if ($redirect['source'] == 'default')
				{
					$default_redirect = $redirect;
					continue;
				}
				$htaccess_content .= "RewriteCond %{HTTP_HOST} =".$redirect['source']."\n";
				$htaccess_content .= "RewriteCond %{HTTPS} =on\n";
				$htaccess_content .= "RewriteRule ^(.*)$ https://".$redirect['destination'].'/$1 [proxy,noescape,last]' . "\n\n";

				$htaccess_content .= "RewriteCond %{HTTP_HOST} =".$redirect['source']."\n";
				$htaccess_content .= "RewriteRule ^(.*)$ http://".$redirect['destination'].'/$1 [proxy,noescape,last]' . "\n\n";
			}
		}
		
		if ($default_redirect !== NULL)
		{
			$htaccess_content .= "RewriteCond %{HTTP_HOST} !=".php_uname('n')."\n";
			$htaccess_content .= "RewriteCond %{HTTP_HOST} !=".$_SERVER['HTTP_HOST']."\n";
			$htaccess_content .= "RewriteCond %{HTTPS} =on\n";
			$htaccess_content .= "RewriteRule ^(.*)$ https://".$default_redirect['destination'].'/$1 [proxy,noescape,last]' . "\n\n";			
			$htaccess_content .= "RewriteCond %{HTTP_HOST} !=".php_uname('n')."\n";
			$htaccess_content .= "RewriteCond %{HTTP_HOST} !=".$_SERVER['HTTP_HOST']."\n";
			$htaccess_content .= "RewriteRule ^(.*)$ http://".$default_redirect['destination'].'/$1 [proxy,noescape,last]' . "\n\n";
		}

		$htaccess_content .= "Order Deny,Allow\n";
		$htaccess_content .= "Deny from all\n";
		$htaccess_content .= "\n# permanent\n";
		foreach ($permanent_ips as $ip)
		{
			$htaccess_content .= "Allow from ".$ip."\n";
		}

		foreach ($ips as $ip_set)
		{
			if (isset($ip_set['enabled']) && $ip_set['enabled'] && isset($ip_set['addresses']))
			{
				$htaccess_content .= "\n# " . $ip_set['name'] . "\n";
				foreach ($ip_set['addresses'] as $ip)
				{
					$htaccess_content .= "Allow from ".$ip."\n";
				}
			}
		}
		$htaccess_content .= "\nAuthType Basic\n";
		$htaccess_content .= "AuthName \"Development\"\n";
		$htaccess_content .= "AuthUserFile " . $this->_config->get('htpasswd_file') . "\n";
		$htaccess_content .= "Require valid-user\n";
		$htaccess_content .= "Satisfy any\n";

		file_put_contents($this->_config->get('htaccess_file'), $htaccess_content, LOCK_EX);
	}

	private function writeHtpasswd()
	{
		$creds = $this->getCredentials();

		$htpasswd_content = "# contents generated\n";
		$htpasswd_content .= "# Any changes will be overwritten!\n";
		$htpasswd_content .= "# Generated at ".strftime('%a %d %b %H:%M:%S %Y',time())."\n\n";

		foreach ($creds as $cred)
		{
			if (isset($cred['enabled']) && $cred['enabled'] && isset($cred['username']) && isset($cred['password']))
			{
				$htpasswd_content .= $cred['username'] . ":{SHA}" . base64_encode(sha1($cred['password'], true)) . "\n";
			}
		}
		file_put_contents($this->_config->get('htpasswd_file'), $htpasswd_content, LOCK_EX);
	}
}
