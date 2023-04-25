<?php
/** autoload.php contains the class autoloader
 *
 * @author K Minkler
 * @package httpd-php-revproxy
 */

date_default_timezone_set("UTC");

define('CONFIG_ROOT', preg_match('/^\/usr\/local/', __FILE__) ? '/opt/httpd-php-revproxy/config/' : dirname(__FILE__) . '/../../../../../../opt/httpd-php-revproxy/config/');

spl_autoload_register('revproxy_autoload');

/** 
 * The class autoloader.  Conforms to PSR-0
 *
 * @param string $class_name the name of the class to autoload.
 */
function revproxy_autoload($class_name) 
{
		$class_name = ltrim($class_name, '\\');
		$filename = dirname(__FILE__) . '/';
		$namespace = '';
		if ($last_namespace_position = strripos($class_name, '\\')) 
		{
			$namespace = substr($class_name, 0, $last_namespace_position);
			$class_name = substr($class_name, $last_namespace_position + 1);
			$filename .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$filename .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

		if (file_exists($filename))
		{
			require $filename;
		}
}

?>
