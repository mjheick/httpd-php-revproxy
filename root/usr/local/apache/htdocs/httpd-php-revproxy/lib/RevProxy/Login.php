<?php
/** 
 * Login.php contains the RevProxy_Login class
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */
/**
 * RevProxy_Login helper class for authentication
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */
final class RevProxy_Login
{
    /**
     * Ensure the user is logged in, or die.
     * @param RevProxy_Config $config The config object.
     * @return boolean true on success, calls die() on failure.
     */
    public static function ensureAuthenticated(RevProxy_Config $config)
    {
        session_start();
        // ensure SSL
        if (!isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 443) {
            header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
        
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === false || !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
			$_SESSION['authenticated'] = array();
            header("WWW-authenticate: basic realm=\"LDAP\"");
            header("HTTP/1.0 401 Unauthorized");
            echo "You are not authorized.";
            return false;
        } elseif (!isset($_SESSION['authenticated']['user']) || $_SESSION['authenticated']['user'] !== $_SERVER['PHP_AUTH_USER']) {
            list($host, $base_dn, $user_ou, $group_ou) = array(
                $config->get('ldap_host') ,
                $config->get('ldap_base_dn') ,
                $config->get('ldap_user_ou') ,
                $config->get('ldap_group_ou') ,
            );
            $user_ou = $user_ou ? $user_ou : 'people';
            $group_ou = $group_ou ? $group_ou : 'group';
            
            $ldap = new LDAP($host, $base_dn, $user_ou, $group_ou);
            try {
                $auth = $ldap->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
            
            if (!$auth) {
                header("WWW-authenticate: basic realm=\"LDAP\"");
                header("HTTP/1.0 401 Unauthorized");
                return "You are not authorized.";
            }
            
            $groups = $ldap->getGroups($_SERVER['PHP_AUTH_USER']);

			$group_quotas = array();

			foreach ($groups as $group) {
				if ($group == $_SERVER['PHP_AUTH_USER']) {
					continue;
				}
				$group_attributes = $ldap->getGroupAttributes($group);
				if (isset($group_attributes['virtualMachineCountLimit'])) {
					$group_quotas[$group] = $group_attributes['virtualMachineCountLimit'];
				}
			}
            
            $user_attributes = $ldap->getUserAttributes($_SERVER['PHP_AUTH_USER']);
            $quota = isset($user_attributes['virtualMachineCountLimit']) ? (int)$user_attributes['virtualMachineCountLimit'] : 0;
            
            session_regenerate_id();
            $_SESSION['authenticated'] = array(
                'user' => $_SERVER['PHP_AUTH_USER'],
                'groups' => $groups,
                'quota' => $quota,
                'group_quota' => $group_quotas,
            );
        }
        return true;
    }
}
