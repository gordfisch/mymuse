<?php
namespace Helper;

use Codeception\Configuration;
use Codeception\Module;

class Acceptance extends Module
{
	/**
	 * Array of the configuration settings
	 *
	 * @var      array
	 * @since    3.7.3
	 */
	protected static $acceptanceSuiteConfiguration = [];
	/**
	 * Function to get Configuration from the acceptance.suite.yml to be used by a test
	 *
	 * @return  array
	 *
	 * @since   3.7.3
	 */
	public function getSuiteConfiguration()
	{
		if (empty(self::$acceptanceSuiteConfiguration))
		{
			self::$acceptanceSuiteConfiguration = Configuration::suiteSettings('acceptance', Configuration::config());
		}
		return self::$acceptanceSuiteConfiguration;
	}
}
