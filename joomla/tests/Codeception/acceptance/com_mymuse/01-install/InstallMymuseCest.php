<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2022 Arboreta Internet Services <https://www.arboreta.ca>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Install MyMuse
 *
 * @since  3.7.3
 */
class InstallMymuseCest
{
	/**
	 * Install MyMuse.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   3.7.3
	 *
	 * @return  void
	 */
	public function installMymuse(AcceptanceTester $I)
	{
		$path = "com_mymuse-latest.zip";
		$I->am('Administrator');
		$I->doAdministratorLogin(null, null, false);
		$I->installExtensionFromFileUpload($path);
	
	}


}
