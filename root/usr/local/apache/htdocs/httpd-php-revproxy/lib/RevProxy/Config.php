<?php
/** 
 * Config.php contains the RevProxy_Config class
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */

/**
 * RevProxy_Config helper class for parsing YAML
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */
class RevProxy_Config
{
	/**
	 * The basedir to load YAML files
	 * @var string $_base_dir
	 */
	private $_base_dir = NULL;

	/**
	 * The cache of configuration data read from YAML
	 * @var string $_config
	 */
	private $_config = array();

	/**
	 * Constructor
	 * @param string $base_dir Directory of where to locate .yml files
	 */
	public function __construct($base_dir)
	{
		$this->_base_dir = $base_dir;
	}

	/**
	 * Retrieve a value from a configuration file
	 *
 	 * If $source is specified, configuration will be read from the
	 * $base_dir/$source.yml file (defaults to 'main.yml')
	 *
	 * @param string $key The key to retrieve from the YAML file
	 * @param string $source Optional source filename (e.g. 'main') without the .yml extension
	 * @return The configuration setting or NULL, E_WARNING raised on YAML parse errors
	 */ 
	public function get($key, $source='main')
	{
		if (!isset($this->_config[$source]))
		{
			$this->_config[$source] = $this->_yaml_load($this->_base_dir . "$source.yml");
		}
		if (isset($this->_config[$source][$key]))
		{
			return $this->_config[$source][$key];
		}

		return NULL;
	}

	/**
	 * Retrieves all values from a configuration file
	 * @param string $source source filename (e.g. 'main') without the .yml extension
	 * @return array Array of configuration settings, E_WARNING raised and array() returned on bad YAML
	 */
	public function getAll($source)
	{
		if (!isset($this->_config[$source]))
		{
			$this->_config[$source] = $this->_yaml_load($this->_base_dir . "$source.yml");
		}
		return $this->_config[$source];
	}

	/**
	 * Loads a yaml file
	 *
	 * If the YAML file contains multiple documents, they are merged together.
	 * @param string $filename the path to the YAML file to load
	 * @return array Array of yaml, E_WARNING raised and array() returned on bad YAML
	 */
	private function _yaml_load($filename)
	{
		$documents = yaml_parse_file($filename, -1);
		$config = array();
		foreach ($documents as $document)
		{
			if (is_array($document))
			{
				$config = array_merge($config, $document);
			}
		}
		return $config;
	}
}
