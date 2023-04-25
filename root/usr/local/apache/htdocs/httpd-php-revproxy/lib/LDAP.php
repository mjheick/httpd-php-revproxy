<?php
/** 
 * LDAP.php contains the LDAP class
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */

/**
 * LDAP helper class for authentication
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */
final class LDAP
{
	/**
	 * The LDAP host
	 * @var string $_ldap_host
	 */
	private $_ldap_host = NULL;

	/**
	 * The LDAP base DN for where to look for users.
	 * @var string $_base_dn
	 */
	private $_base_dn = NULL;

	/**
	 * The Users organizational unit
	 * @var string $_user_ou
	 */
	private $_user_ou = NULL;

	/**
	 * The groups organizational unit
	 * @var string $_group_ou
	 */
	private $_group_ou = NULL;

	/**
	 * The LDAP connection
	 * @var resource $_ldap
	 */
	private $_ldap = NULL;
	
	/**
	 * Class constructor
	 * @param string $ldap_host the ldap connection url
	 * @param string $base_dn the ldap base DN
	 * @param string $user_ou the user OU
	 * @param string $group_ou the user OU
	 */
	public function __construct($ldap_host, $base_dn, $user_ou = "people", $group_ou = "group")
	{
		$this->_ldap_host = $ldap_host;
		$this->_base_dn   = $base_dn;
		$this->_user_ou   = $user_ou;
		$this->_group_ou  = $group_ou;
	}

	/**
	 * Authenticate a user via LDAP
	 * @param string $username The LDAP user to authenticate
	 * @param string $password The LDAP password to authenticate
	 * @return boolean TRUE on success, FALSE on failure, exception on connection/configuration failures
	 */
	public function authenticate($username, $password)
	{
		$this->_connect();

		return @ldap_bind($this->_ldap, "uid={$username},ou={$this->_user_ou},{$this->_base_dn}", $password);
	}

	/**
	 * Retruns the groups this user belongs to
	 * @param string $username
	 * @return array An array of group common names
	 */
	public function getGroups($username)
	{
		// get the primary group first
		$user_attributes = $this->getUserAttributes($username);
		$group_id = $user_attributes['gidNumber'];

		// get the group name
		$group_attributes = $this->_search("ou={$this->_group_ou},{$this->_base_dn}", "(gidNumber={$group_id})");
		$groups = array($username, $group_attributes['cn']);

		$group_results = $this->_search("ou={$this->_group_ou},{$this->_base_dn}", "(memberUid={$username})");
		if (isset($group_results[0])) {
			foreach ($group_results as $group)
			{
				$groups[] = $group['cn'];
			}
		} elseif (isset($group_results['cn'])) {
			$groups[] = $group_results['cn'];
		}

		return array_values(array_unique($groups));
	}

	/**
	 * Retrieves all attributes associated with a user
	 * @param string $username
	 * @return array User attributes array (LDAP specific meanings), or exception on connection/configuration failures
	 */
	public function getUserAttributes($username)
	{
		$this->_connect();

		if (!@ldap_bind($this->_ldap))
		{
			throw new InvalidArgumentException("Unable to bind anonymous '{$this->_ldap_host}'");
		}

		// get the primary group first
		$user_attributes = $this->_search("ou={$this->_user_ou},{$this->_base_dn}", "(uid={$username})");

		return $user_attributes;
	}

	/**
	 * Retrieves all attributes associated with a group
	 * @param string $groupname
	 * @return array group attributes array (LDAP specific meanings), or exception on connection/configuration failures
	 */
	public function getGroupAttributes($groupname)
	{
		$this->_connect();

		if (!@ldap_bind($this->_ldap))
		{
			throw new InvalidArgumentException("Unable to bind anonymous '{$this->_ldap_host}'");
		}

		// get the primary group first
		$group_attributes = $this->_search("ou={$this->_group_ou},{$this->_base_dn}", "(cn={$groupname})");

		return $group_attributes;
	}
		
	/**
	 * Initiate a connection
	 * @return boolean TRUE on success, exception on error
	 */
	private function _connect()
	{
		if ($this->_ldap !== NULL)
		{
			return true;
		}	
		$this->_ldap = @ldap_connect($this->_ldap_host);
		if ($this->_ldap === false)
		{
			throw new InvalidParameterException("Failed to connect to LDAP host '{$this->_ldap_host}'");
		}
		return true;
	}

	/**
	 * perform an ldap search and flatten the results
	 * @param string $base_dn
	 * @param string $filter
	 * @returns array The results
	 */
	private function _search($base_dn, $filter)
	{
		$this->_connect();

		if (!@ldap_bind($this->_ldap))
		{
			throw new InvalidArgumentException("Unable to bind anonymous '{$this->_ldap_host}'");
		}

		if (!($search_result = @ldap_search($this->_ldap, $base_dn, $filter)))
		{
			trigger_error("Possible misconfiguration, groups search failed in dn 'ou={$this->_group_ou},{$this->_base_dn}' with filter '(memberUid={$username})'", E_USER_WARNING);
			return array();
		}

		$results = array();
		for ($entry = @ldap_first_entry($this->_ldap, $search_result); $entry != false; $entry = ldap_next_entry($this->_ldap, $entry))
		{
			$attributes = ldap_get_attributes($this->_ldap, $entry);
			$results[] = $attributes;
		}
	
		return $this->_flattenSearchResults($results);
	}

	/**
	 * flattens the LDAP returned search results to be easier to use
	 * @param array $array The array to flatten
	 */
	private function _flattenSearchResults($array, $depth = 1)
	{
		if (count($array) == 1 && isset($array[0]))
		{
			$array = $array[0];
		}

		if (!is_array($array))
		{
			return $array;
		}

		$result = array();
		if (isset($array['count']))
		{
			for ($x = 0; $x < $array['count']; $x++)
			{
				if (isset($array[$array[$x]]))
				{
					// [count] => 0
					// [0] => "foo"
					// [foo] => value
					$result[$array[$x]] = $array[$array[$x]];
				}
				else
				{
					$result[$x] = $array[$x];
				}
			}
		}
		else
		{
			$result = $array;
		}

		foreach ($result as $key => $value)
		{
			if (is_array($value))
			{
				$result[$key] = $this->_flattenSearchResults($value, $depth + 1);
				if (count($result[$key]) == 1)
				{
					$result[$key] = $result[$key][0];
				}
			}
		}
		return $result;
	}
}
