<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2022 Arboreta Internet Services <https://www.arboreta.ca>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Install Joomla
 *
 * @since  3.7.3
 */
class UninstallMymuseCest
{
	/**
	 * Uninstall MyMuse.
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since   3.7.3
	 *
	 * @return  void
	 */
	public function uninstallMymuse(AcceptanceTester $I)
	{
		$I->clearMenus();

		$I->clearUsers();


		$I->am('Administrator');
		$I->doAdministratorLogin(null, null, false);
		$I->uninstallExtension('Mymuse');
	
	}


}
