<?php
/**
 * @package	 Joomla.Tests
 * @subpackage	AcceptanceTester
 *
 * @copyright	 (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

use Codeception\Actor;
use Codeception\Lib\Friend;


/**
 * Acceptance Tester global class for entry point.
 *
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 * @method getConfig(string $string)
 *
 * @SuppressWarnings(PHPMD)
 *
 * @since	3.7.3
 */
class AcceptanceTester extends Actor
{
	use _generated\AcceptanceTesterActions;

	var $shortwait = 7;

	var $childrenDelete =	['xpath' => '//*[@id="status-group-children-delete"]/button'];

	/**
	 * Locator for the username field
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $loginUserName = ['id' => 'username'];

	/**
	 * Locator for the Password field
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $loginPassword = ['id' => 'password'];

	/**
	 * Locator for the Login Button
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $loginButton = ['xpath' => "//div[@class='com-users-login login']/form/fieldset/div[4]/div/button"];

	/**
	 * Locator for the Logout Button
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $frontEndLoginSuccess = [
		'xpath' => "//form[contains(@class, 'mod-login-logout')]/div[@class='mod-login-logout__button logout-button']"
	];

	/**
	 * Locator for the Logout Button
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $frontEndLogoutButton = [
		'xpath' => "//div[contains(@class, 'logout-button')]//button[contains(text(), 'Log out')]"
	];

	/**
	 * Locator for the Login Button
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $frontEndLoginForm = ['xpath' => "//div[contains(@class, 'login')]//button[contains(text(), 'Log in')]"];

	/**
	 * Locator for the Login Page Url
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $adminLoginPageUrl = '/administrator/index.php';

	/**
	 * Locator for the administrator username field
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $adminLoginUserName = ['id' => 'mod-login-username'];

	/**
	 * Locator for the admin password field
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $adminLoginPassword = ['id' => 'mod-login-password'];

	/**
	 * Locator for the Login Button
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $adminLoginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];

	/**
	 * Locator for the Control Panel
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $controlPanelLocator = ['css' => 'h1.page-title'];

	/**
	 * Locator for the Login URL
	 *
	 * @var	array
	 * @since	3.7.4.2
	 */
	public $frontEndLoginUrl = '/index.php?option=com_users&view=login';

	/**
	 * New Button in the Admin toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminToolbarButtonNew = ['class' => 'button-new'];

	/**
	 * Apply Button in the Admin toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminToolbarButtonApply = ['class' => 'button-apply'];

	/**
	 * Save and Close Button in the Admin toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminToolbarButtonSave = ['xpath' => '//*[@id="save-group-children-save"]/button'];


	/**
	 * Trash Button in the Admin toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminToolbarButtonTrash = ['class' => 'button-trash'];

	/**
	 * List Tracks Button in the Product toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */

	public $adminToolbarButtonListTracks = ['xpath' => '//*[@id="toolbar-featured"]/button'];

	/**
	 * List Items Button in the Products toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */

	public $adminToolbarButtonListItems = ['xpath' => '//*[@id="toolbar-featured1"]/button'];




	/**
	 * List Items Button in the ListTracks toolbar
	 *
	 * @var	array
	 * @since	3.7.5
	 */

	public $listTracksToolbarButtonUploadTracks = ['css' => 'joomla-toolbar-button[task="product.uploadtrack"] button'];

	public $listTracksToolbarButtonNewTrack = ['css' => 'joomla-toolbar-button[task="product.addfile"] button'];

	public $listTracksToolbarButtonNewAllTracks = ['css' => 'joomla-toolbar-button[task="product.new_allfiles"] button'];

	public $listTracksToolbarButtonProductReturn = ['css' => 'joomla-toolbar-button[task="product.productreturn"] button'];



	/**
	 * Admin Control Panel Text
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminControlPanelText = 'Home Dashboard';

	/**
	 * Admin Logout Dropdown
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminLogoutDropdown = ['css' => "button[title='User Menu']"];

	/**
	 * Admin Login Text
	 *
	 * @var	string
	 * @since	3.7.5
	 */
	public $adminLoginText = 'Log in';

	/**
	 * Admin Logout Text
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminLogoutText = ['xpath' => "//a[text()[contains(.,'Log out')]]"];

	/**
	 * Locator for the administrator login submit button
	 *
	 * @var	array
	 * @since	3.7.5
	 */
	public $adminLoginSubmitButton = ['id' => 'btn-login-submit'];

	/**
	 * Manage User - User Group Assignment Tab
	 *
	 * @var	string
	 * @since	3.9.1
	 */
	public $adminManageUsersUserGroupAssignmentTab = 'Assigned User Groups';

	/**
	 * Manage User - Account Details Tab
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public $adminManageUsersAccountDetailsTab = 'Account Details';

	/**
	 * Global Configuration - Site Tab
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public $adminConfigurationSiteTab = array('xpath' => "//div[@role='tablist']/button[@aria-controls='page-site']");

	/**
	 * Global Configuration - System Tab
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public $adminConfigurationSystemTab = array('xpath' => "//div[@role='tablist']/button[@aria-controls='page-system']");

	/**
	 * Global Configuration - Server Tab
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public $adminConfigurationServerTab = array('xpath' => "//div[@role='tablist']/button[@aria-controls='page-server']");

	/**
	 * Global Configuration URL
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $globalConfigurationUrl = '/administrator/index.php?option=com_config';

	/**
	 * Admin Module URL
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $moduleUrl = '/administrator/index.php?option=com_modules';

	/**
	 * Select Module Title
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $moduleTitle = '#jform_title';

	/**
	 * Select Filter Options
	 *
	 * @var	array
	 * @since	4.0.0
	 */
	public static $filterOptions = ['link' => 'Filtering Options'];

	/**
	 * Select Filter Options
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $selectModuleCategory = '#jform_params_catid';

	/**
	 * Fill Category
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $fillModuleCategory = '//div[@id="jform_params_catid_chzn"]/ul/li/input';

	/**
	 * Select Module category
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public static $moduleCategory = 'jform_params_catid_chzn';

	/**
	 * cats array
	 *
	 * @var	string
	 * @since	4.0.0
	 */
	public $cats = array();

	/**
	 * Manage User - User Group Assignment Tab - User Group checkbox
	 *
	 * @param	 string	$userGroup	display name of the user group
	 *
	 * @return array
	 * @since	3.9.1
	 */
	public function adminManageUsersUserGroupAssignmentCheckbox($userGroup)
	{
		return array('xpath' => "//label[contains(text()[normalize-space()], '$userGroup')]");
	}


	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar User Panel
	 *
	 * @param	 string	$button		 The full name of the button
	 * @param	 string	$subselector	Subselector to further define the button
	 *
	 * @return	void
	 *
	 * @since	 4.0.0
	 */
	public function clickUserToolbarButton($action, $subselector = null)
	{
		
		$this->click("#toolbar-status-group");

		$input = strtolower($action);

		switch ($input)
		{
			case "new":
				$this->click("//button[contains(@class, 'button-new')]");
				break;
			case "publish":
				$this->click("#status-group-children-publish");
				break;
			case "unpublish":
				$this->click("#status-group-children-unpublish");
				break;
			case "archive":
				$this->click("#status-group-children-unblock");
				break;
			case "batch":
				$this->click("#status-group-children-batch");
				break;
			case "delete":
				$this->click("#status-group-children-delete");
				break;
			case "archive":
				$this->click("#status-group-children-archive");
				break;
			case "checkin":
				$this->click("#status-group-children-checkin");
				break;
			case "trash":
				$this->click("#status-group-children-trash");
				break;

		}
	}

	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar MyMuse Product Panel
	 *
	 * @param	 string	$button		 The full name of the button
	 * @param	 string	$subselector	Subselector to further define the button
	 *
	 * @return	void
	 *
	 * @since	 4.0.0
	 */
	public function clickProductToolbarButton($action, $subselector = null)
	{
		
		$this->click("#toolbar-status-group");

		$input = strtolower($action);

		switch ($input)
		{

			case "publish":
				$this->click("#status-group-children-publish");
				break;
			case "unpublish":
				$this->click("#status-group-children-unpublish");
				break;
			case "archive":
				$this->click("#status-group-children-unblock");
				break;
			case "batch":
				$this->click("#status-group-children-batch");
				break;
			case "delete":
				$this->click("#status-group-children-delete");
				break;
			case "archive":
				$this->click("#status-group-children-archive");
				break;
			case "checkin":
				$this->click("#status-group-children-checkin");
				break;
			case "trash":
				$this->click("#status-group-children-trash");
				break;

		}
	}
	/**
	 * Function to check for PHP Notices or Warnings.
	 *
	 * @param	 string	$page	Optional, if not given checks will be done in the current page
	 *
	 * @note	doAdminLogin() before
	 *
	 * @since	 3.7.3
	 *
	 * @return	void
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		$I = $this;

		if ($page)
		{
			$I->amOnPage($page);
		}

		$I->dontSeeInPageSource('Notice:');
		$I->dontSeeInPageSource('<b>Notice</b>:');
		$I->dontSeeInPageSource('Warning:');
		$I->dontSeeInPageSource('<b>Warning</b>:');
		$I->dontSeeInPageSource('Strict standards:');
		$I->dontSeeInPageSource('<b>Strict standards</b>:');
		$I->dontSeeInPageSource('The requested page can\'t be found');
	}

	/**
	 * Function to wait for JS to be properly loaded on page change.
	 *
	 * @param	 integer|float	$timeout	Time to wait for JS to be ready
	 *
	 * @since	 4.0.0
	 *
	 * @return	void
	 */
	public function waitForJsOnPageLoad($timeout = 1)
	{
		$I = $this;

		$I->waitForJS('return document.readyState == "complete"', $timeout);

		// Wait an additional 500ms to make sure that really all JS is loaded
		$I->wait(0.5);
	}



	/**
	* Define custom actions here
	*/



	function seePageHasElement($element)
	{
		try {
			$this->seeElement($element);
		} catch (Exception $f) {
			return false;
		}
		return true;
	 }

	function seePageHasText($text)
	{
		$this->comment('Looking for "'.$text.'"');
		try {
			$this->see($text);
		} catch (Exception $f) {
			return false;
		}
		return true;
	}

	function clearCart()
	{
		$this->amOnPage('index.php');
		$this->click("My Cart");
		if($this->seePageHasText("Clear Cart")){
			$this->click("Clear Cart");
		}
		return true;
	}

	public function selectFromDropdown($selector, $n)
	{
		$option = $this->grabTextFrom($selector . ' option:nth-child(' . $n . ')');
		$this->selectOption($selector, $option);
	}

	/**
	 * Selects an option in a Joomla Radio Field based on its id
	 *
	 * @param	 string	$id	 The text in the <label> with for attribute that links to the radio element
	 * @param	 string	$option	The text in the <option> to be selected in the chosen radio button
	 *
	 * @return	void
	 *
	 * @since	 3.0.0
	 */
	public function selectOptionInRadioFieldById($radioId, $option)
	{

	$this->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
	}



	/**
	* Uninstall Extension based on a name
	*
	* @param	 string	$extensionName	Is important to use a specific
	*
	* @return	void
	*
	* @since	 3.0.0
	* @throws	\Exception
	*/
	public function uninstallMymuse($extensionName)
	{
		$this->comment('Going to	uninstallExtension');
		$this->uninstallExtension($extensionName);

	}



	 /**
	* Create mymuse categories
	*
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function createMymuseCategories()
	 {
	 

	 	//MyMuse
	 	$this->comment('Create Category MyMuse ');
	 	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->comment('Click new category button');
	 	$this->click($this->adminToolbarButtonNew);

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->fillField(array('id' => 'jform_title'), 'MyMuse');

	 	$this->comment('Click new category apply button');

	$this->click($this->adminToolbarButtonApply);
	 	$this->comment('see a success message after saving the category');
	 	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['MyMuse'] = $id;
	$this->click($this->adminToolbarButtonSave);


	 	//Artists
	 	$this->comment('Create Category Artists ');
	 	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->comment('Click new category button');
	 	$this->click($this->adminToolbarButtonNew);

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->fillField(array('id' => 'jform_title'), 'Artists');

	 	//choose the parent
	 	try
	{
		//$this->seeElement(['css' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
		
		$this->seeElement(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);

	}
	catch (Exception $e)
	{
		$this->comment("No Parent button");

		exit;
	}
	$this->click(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
	$this->click(['css' => '.choices__item[data-value="'.$this->cats['MyMuse'].'"']);


	 	$this->comment('Click new category apply button');
	 	
	$this->click($this->adminToolbarButtonApply);
	$this->comment('see a success message after saving the category');
	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['Artists'] = $id;
	$this->click($this->adminToolbarButtonSave);


	 	//Genres
	 	$this->comment('Create Category Genres');
	 	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->comment('Click new category button');
	 	$this->click($this->adminToolbarButtonNew);

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->fillField(array('id' => 'jform_title'), 'Genres');

	 	//choose the parent
	 	try
	{
		$this->seeElement(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
		
	}
	catch (Exception $e)
	{
		$this->comment("No Parent button");

		exit;
	}
	$this->click(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
	$this->click(['css' => '.choices__item[data-value="'.$this->cats['MyMuse'].'"']);
	 	$this->comment('Click new category apply button');
	 	
	$this->click($this->adminToolbarButtonApply);
	$this->comment('see a success message after saving the category');
	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['Genres'] = $id;
	$this->click($this->adminToolbarButtonSave);



	 	//Iron Brew
	 	$this->comment('Create Category Iron Brew');
	 	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->comment('Click new category button');
	 	$this->click($this->adminToolbarButtonNew);

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->fillField(array('id' => 'jform_title'), 'Iron Brew');
	 	//choose the parent
	 	try
	{
		$this->seeElement(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
		
	}
	catch (Exception $e)
	{
		$this->comment("No Parent button");

		exit;
	}
	$this->click(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
	$this->click(['css' => '.choices__item[data-value="'.$this->cats['Artists'].'"']);


	 	$this->comment('Click new category apply button');
	 	
	$this->click($this->adminToolbarButtonApply);
	$this->comment('see a success message after saving the category');
	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['Iron Brew'] = $id;
	$this->click($this->adminToolbarButtonSave);


	 	//World Beat
	 	$this->comment('Category MyMuse creation in /administrator/ ');
	 	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->comment('Click new category button');
	 	$this->click($this->adminToolbarButtonNew);

	 	$this->waitForElement(array('class' => 'page-title'));

	 	$this->fillField(array('id' => 'jform_title'), 'World Beat');
	 	//choose the parent
	 	try
	{
		$this->seeElement(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
		
	}
	catch (Exception $e)
	{
		$this->comment("No Parent button");

		exit;
	}
	$this->click(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
	$this->click(['css' => '.choices__item[data-value="'.$this->cats['Genres'].'"']);


	 	$this->comment('Click new category apply button');
	 	
	$this->click($this->adminToolbarButtonApply);
	$this->comment('see a success message after saving the category');
	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['World Beat'] = $id;
	$this->click($this->adminToolbarButtonSave);


	//Cats in the Kitchen
	$this->comment('Category MyMuse creation in /administrator/ ');
	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

	$this->waitForElement(array('class' => 'page-title'));

	$this->comment('Click new category button');
	$this->click($this->adminToolbarButtonNew);

	$this->waitForElement(array('class' => 'page-title'));

	$this->fillField(array('id' => 'jform_title'), 'Cats in the Kitchen');

	 	//choose the parent
	 	try
	{
		$this->seeElement(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
		
	}
	catch (Exception $e)
	{
		$this->comment("No Parent button");

		exit;
	}
	$this->click(['xpath' => '//*[@id="general"]/div/div[2]/fieldset/div[1]/div[2]/joomla-field-fancy-select']);
	$this->click(['css' => '.choices__item[data-value="'.$this->cats['Artists'].'"']);


	$this->comment('Click new category apply button');
	
	$this->click($this->adminToolbarButtonApply);
	$this->comment('see a success message after saving the category');
	$this->see('Category saved', '#system-message-container');

	$this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
	$id = $this->executeJS("return document.getElementById('jform_id').value;");
	$this->comment('Created category with id '.$id);
	$this->cats['Cats in the Kitchen'] = $id;
	$this->click($this->adminToolbarButtonSave);


	return $this->cats;
	 }


 

	 /**
	* Create mymuse product
	* @param object mock : object of a product 
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 
	 	public function createMymuseProduct($mock)
	{


		$this->comment('Product creation in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=products');
		$this->waitForElement(array('class' => 'page-title'));

		$this->comment('Click new product button');
		$this->click($this->adminToolbarButtonNew);
		$this->waitForElement(array('class' => 'page-title'));

		$this->comment('Fill in image fields');
		$this->waitForElement(array('css' => '#myTab button[aria-controls="images"'));
		$this->click('Images');
		$this->waitForElement(array('id' => 'jform_list_image'));
		$this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
		$this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

		$this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
		$this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

		if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){

		 	$this->selectOption('#jform_product_images', $mock->jform_product_images);
			$this->wait(1);
		}

		$this->comment('Fill in recording details fields');
		$this->click('Recording Details');
		
		$this->fillField(array('id' => 'jform_recording_product_full_time'), $mock->jform_product_full_time);
		$this->fillField(array('id' => 'jform_recording_product_studio'), $mock->jform_product_studio);
		$this->fillField(array('id' => 'jform_recording_product_publisher'), $mock->jform_product_publisher);
		$this->fillField(array('id' => 'jform_recording_product_producer'), $mock->jform_product_producer);

		if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){

			$this->click(["css" => "#jform_recording_product_country"]);
			$this->click(["css" => 'option[value='.$mock->jform_product_country.']']);
		}
		

		$this->comment('Fill in Physical fields');
		$this->click('Physical');
		$this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
		$this->fillField(array('id' => 'jform_physical_product_weight'), $mock->jform_product_weight);
		$this->fillField(array('id' => 'jform_physical_product_length'), $mock->jform_product_length);
		$this->fillField(array('id' => 'jform_physical_product_width'), $mock->jform_product_width);
		$this->fillField(array('id' => 'jform_physical_product_height'), $mock->jform_product_height);


		$this->comment('Fill in Details fields');
		$this->click('Details');
		$this->fillField(array('id' => 'jform_title'), $mock->jform_title);

		$this->selectOption(['id' => 'jform_artistid'], 'Iron Brew');
		$this->selectOption(['id' => 'jform_catid'], 'World Beat');
		$this->fillField(array('id' => 'jform_product_release_date'), $mock->jform_product_made_date);
		

		if(isset($mock->jform_product_physical)){
			$this->selectOption(['id' => 'jform_product_physical'], 'Yes');
		}

		if(isset($mock->jform_attribs_product_price_physical)){
			$this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
		}else{
			$this->fillField(array('id' => 'jform_price'), $mock->jform_price);
		}

		if(isset($mock->jform_attribs_product_price_mp3)){
			$this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
		}
		if(isset($mock->jform_attribs_product_price_mp3_all)){
			$this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
		}
		if(isset($mock->jform_attribs_product_price_wav)){
			$this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
		}
		if(isset($mock->jform_attribs_product_price_wav_all)){
			$this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
		}
		

		
		$this->waitForElement(array('id' => 'jform_articletext_ifr'));
		$editor_frame_name = 'articletext-frame';
		$this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
		$this->switchToIFrame($editor_frame_name);
		$this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


		$this->switchToIFrame();
		
		$this->comment('Click Apply button to save');
		$this->click($this->adminToolbarButtonApply);

		$this->see('saved');
		$id = $this->grabValueFrom('input[id=jform_id]');
		$this->comment('Created product with id '.$id);
		return $id;
	}


	 /**
	* Edit MyMuse Product
	* @param object mock : object of a product
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function editMymuseProduct($mock)
	 {
	 
	$this->comment('Product editing in /administrator/ ');
	$this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
	$this->waitForElement(array('class' => 'page-title'));


	$this->comment('Fill in image fields');
	$this->waitForElement(array('css' => '#myTabTabs > li:nth-child(2)'));
	$this->click('Images');
	$this->waitForElement(array('id' => 'jform_list_image'));
	$this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
	$this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

	$this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
	$this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

	if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){
		$this->selectOptionInChosenById('jform_product_images', $mock->jform_product_images);
	}

	$this->comment('Fill in recording details fields');
	$this->click('Recording Details');
	$this->fillField(array('id' => 'jform_product_made_date'), $mock->jform_product_made_date);
	$this->fillField(array('id' => 'jform_product_full_time'), $mock->jform_product_full_time);

	if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){
	 	$this->click(["css" => "#jform_product_country_chzn > a.chzn-single > span"]);
		$this->click(["xpath" => "//div[@id='jform_product_country_chzn']/div/ul/li[$mock->jform_product_country]"]);
	}
	

	$this->fillField(array('id' => 'jform_product_publisher'), $mock->jform_product_publisher);
	$this->fillField(array('id' => 'jform_product_producer'), $mock->jform_product_producer);
	$this->fillField(array('id' => 'jform_product_studio'), $mock->jform_product_studio);

	$this->comment('Fill in Dimensions fields');
	$this->click('Dimensions');
	$this->fillField(array('id' => 'jform_product_weight'), $mock->jform_product_weight);
	$this->fillField(array('id' => 'jform_product_length'), $mock->jform_product_length);
	$this->fillField(array('id' => 'jform_product_width'), $mock->jform_product_width);
	$this->fillField(array('id' => 'jform_product_height'), $mock->jform_product_height);


	$this->comment('Fill in Details fields');
	$this->click('Details');
	$this->fillField(array('id' => 'jform_title'), $mock->jform_title);

	$this->selectOptionInChosenById('jform_artistid', $mock->jform_artist);
	$this->selectOptionInChosenById('jform_catid', $mock->jform_cat);


	$this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
	

	if(isset($mock->jform_product_physical)){
		$this->selectOptionInChosenById('jform_product_physical', $mock->jform_product_physical);
	}

	if(isset($mock->jform_attribs_product_price_physical)){
		$this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
	}else{
		$this->fillField(array('id' => 'jform_price'), $mock->jform_price);
	}

	if(isset($mock->jform_attribs_product_price_mp3)){
		$this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
	}
	if(isset($mock->jform_attribs_product_price_mp3_all)){
		$this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
	}
	if(isset($mock->jform_attribs_product_price_wav)){
		$this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
	}
	if(isset($mock->jform_attribs_product_price_wav_all)){
		$this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
	}

	if(isset($mock->jform_attribs_special_status)){
		$this->selectOptionInChosenById('jform_attribs_special_status', $mock->jform_attribs_special_status);
	}
	

	
	$this->waitForElement(array('id' => 'jform_articletext_ifr'));
	$editor_frame_name = 'articletext-frame';
	$this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
	$this->switchToIFrame($editor_frame_name);
	$this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


	$this->switchToIFrame();
	$this->comment('I leave time to the iframe to close');
	$this->wait(2);
	
	$this->comment('Click Apply button to save');
	$this->click($this->adminToolbarButtonApply);

	$this->see('saved');
	$this->comment('Edited product with id '.$mock->id);



	}


	/**
	 * Edit MyMuse Product Field
	 * @param object mock : object of a product field
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function editMymuseProductField($mock)
	{


		$this->comment('Product edit Field in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
		$this->waitForElement(array('class' => 'page-title'));

		$this->click($mock->tab);
		foreach($mock->select as $select){
			if($select['type'] == "select"){
				$this->selectOptionInChosenById($select['option'], $select['value']);
			}elseif($select['type'] == "radio"){
				$this->selectOptionInRadioField($select['option'], $select['value']);

			}elseif($select['type'] == "multiSelect"){
				$this->selectMultipleOptionsInChosen($select['option'], $select['value']);
				$this->wait(1); 
				$this->click($mock->tab);
			}elseif($select['type'] == "text"){
				$this->fillField(array('id' => $select['option']), $select['value']);

			}	 
		}
		$this->comment('Click Apply button to save');
		$this->click($this->adminToolbarButtonApply);

		$this->see('saved');
		$this->comment('Edited product with id '.$mock->id);

	}



	 /**
	* Create mymuse product tracks
	* @param object mock : object of a track 
	* @param object config : the current config
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function createMymuseTrack($mock, $config)
	 {
	 
		$joomla_folder = dirname(dirname(dirname(dirname(__FILE__))));

		if($config['my_copy_tracks']){
			//move some tracks in there
			$from_dir	 = $joomla_folder.'/mymuse-downloads';
			$download_dir = $joomla_folder.$config['my_download_dir'];
			$preview_dir	= $joomla_folder.$config['my_preview_dir'];

			if($config['my_download_dir_format'] == "0"){
				$download_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
				$preview_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
			}

			$this->comment('joomla_folder = '.$joomla_folder);
			$this->comment('download dir = '.$download_dir);
			$this->comment('preview dir = '.$preview_dir);



			//preview
			if(!file_exists($preview_dir)){
				if(!mkdir($preview_dir, 0755)){
					$this->comment('Could not make dir '.$preview_dir.'/wav');
					return false;
				}
			}

			if(!file_exists($preview_dir.'/'.$mock->preview )){
				if(!file_exists($from_dir.'/'.$mock->preview )){
					$this->comment('Could not find preview '.$from_dir.'/'.$mock->preview);
					return false;
				}
				copy($from_dir.'/'.$mock->preview, $preview_dir.'/'.$mock->preview);
			}


			// downloads
			if(!file_exists($download_dir)){
				if(!mkdir($download_dir, 0755)){
					$this->comment('Could not make dir '.$download_dir.'/wav');
					return false;
				}
			}

			//main file(s)
			if($config['my_download_dir_format'] == "1"){
				if(in_array('wav',$config['my_formats'])){
					if(!file_exists($download_dir.'/wav')){
						if(!mkdir($download_dir.'/wav', 0755)){
							$this->comment('Could not make dir '.$download_dir.'/wav');
							return false;
						}
					}
				}
				if(in_array('mp3',$config['my_formats'])){
					if(!file_exists($download_dir.'/mp3')){
						if(!mkdir($download_dir.'/mp3', 0755)){
							$this->comment('Could not make dir '.$download_dir.'/mp3');
							return false;
						}
					}
				}
				if(!file_exists($download_dir.'/wav/'.$mock->wav)){
					if(!file_exists($from_dir.'/'.$mock->wav )){
						$this->comment('Could not find jform_attribs_product_price_wav_all $from_dir / '.$mock->wav);
						return false;
					}
					copy($from_dir.'/'.$mock->wav, $download_dir.'/wav/'.$mock->wav);
				}

				if(!file_exists($download_dir.'/mp3/'.$mock->mp3)){
					if(!file_exists($from_dir.'/'.$mock->mp3 )){
						$this->comment('Could not find mp3 '.$from_dir.'/'.$mock->mp3);
						return false;
					}
					copy($from_dir.'/'.$mock->mp3, $download_dir.'/mp3/'.$mock->mp3);
				}
			}else{
				if(!file_exists($download_dir.'/'.$mock->mp3)){
					if(!file_exists($from_dir.'/'.$mock->mp3 )){
						$this->comment('Could not find mp3 '.$from_dir.'/'.$mock->mp3);
						return false;
					}
					copy($from_dir.'/are-you-my-sister.mp3', $download_dir.'/'.$mock->mp3);
				}

			}
		}

		$this->comment('Track creation in /administrator/ ');
		$this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->parentid);
		
		$this->comment('List Tracks');
		$this->click($this->adminToolbarButtonListTracks);

		$this->comment('New Track');
		$this->click($this->listTracksToolbarButtonNewTrack);


		$this->comment('Fill in Fields');
		$this->fillField(array('id' => 'jform_title'), $mock->jform_title);
		$this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

		if($config['my_price_by_product'] != "1"){
			$this->fillField(array('id' => 'jform_price'), $mock->jform_price);
		}

		$this->comment('Choose Track');
		$this->click(['css' => 'button[aria-controls="tracks"]']);
		for($i = 0; $i < count($config['my_formats']); $i++){
			$this->selectOption(['id' => 'select_file'.$i], $mock->{$config['my_formats'][$i]});
			$this->selectOption(['id' => 'formats'.$i], $config['my_formats'][$i]);
		}

		$this->comment('Choose Preview');
		$this->click('Previews');

		$this->selectOption(['id' => 'file_preview'], $mock->preview);

		$this->comment('Save');
		$this->click($this->adminToolbarButtonApply);

		$this->see('File saved');
		$id = $this->grabValueFrom('input[id=jform_id]');
		$this->comment('Created track with id '.$id);
		return $id;

	}

	 /**
	* Create mymuse all track
	* @param object mock : object of a all track 
	* @param object config : the current config
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function createMymuseAllTrack($mock, $config)
	{
		$this->comment('ALL Track creation in /administrator/ ');
		$this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->parentid);

		$this->comment('List Tracks');
		$this->click($this->adminToolbarButtonListTracks);

		$this->comment('New Track');
		$this->click($this->listTracksToolbarButtonNewAllTracks);

		$this->comment('Fill in Fields');
		$this->fillField(array('id' => 'jform_title'), $mock->jform_title);
		$this->fillField(array('id' => 'jform_alias'), $mock->jform_alias);
		$this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

		if($config['my_download_dir_format'] != "1"){
			$this->fillField(array('id' => 'jform_price'), $mock->jform_price);
		}
		if(isset($mock->jform_product_discount)){
			$this->fillField(array('id' => 'jform_product_discount'), $mock->jform_product_discount);
		}
		$this->comment('Save');
		$this->click($this->adminToolbarButtonApply);

		$this->see('All File saved');

	}

	/**
	* Create mymuse product items
	* @param object mock : object of an item 
	* @param object config : the current config 
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function createMymuseItems($mock, $config)
	 {
	 
		$this->comment('Item creation in /administrator/ for itemid '.$mock->parentid);
		$this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->parentid);

		$this->comment('List Items');
		$this->click($this->adminToolbarButtonListItems);


		//$this->comment('Check attributes '.print_r($mock->jform_attribute, true));
		if(isset($mock->jform_attribute) && is_array($mock->jform_attribute)){
			$att_count = count($mock->jform_attribute);
			foreach($mock->jform_attribute as &$attr){
				$this->comment('New Attribute '.$attr['name']);
				$this->click(["css" => 'joomla-toolbar-button[task="productattributesku.add"] button']);
				$this->wait(1);
				$this->fillField(array('id' => 'jform_name'), $attr['name']);
				$this->fillField(array('id' => 'jform_extra_base'), $attr['extra_base']);
				$this->fillField(array('id' => 'jform_extra_css'), $attr['extra_css']);
				$this->click(["css" => 'joomla-toolbar-button[task="productattributesku.save"] button']);
				$this->wait(1);
				$this->see('Item saved');
			}
				$this->click(['css' => 'joomla-toolbar-button[task="productattributesku.myreturn"] button']);
		}

		$this->click('Create ITEMS');
		$this->wait(2);
		$this->see('Changes to Item saved');
	}

	 /**
	* Create mymuse taxes
	* @param object mock : object of a taxrate 
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function createMymuseTaxrate($mock)
	 {
		$this->comment('Taxrate creation in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=taxrates');
		$this->waitForElement(array('class' => 'page-title'));

		$this->comment('Click new Taxrate button');
		$this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.add\');"]']);
		$this->waitForElement(array('class' => 'page-title'));

		$this->fillField(array('id' => 'jform_tax_name'), $mock->tax_name);
		$this->fillField(array('id' => 'jform_tax_rate'), $mock->tax_rate);
		$this->selectOption(array('id' => 'jform_tax_applies_to'), $mock->tax_applies_to);
		$this->selectOption(array('id' => 'jform_country'), $mock->country);
		$this->selectOption(array('id' => 'jform_province'), $mock->province);
		$this->selectOption(array('id' => 'jform_compounded'), $mock->compounded);


		$this->comment('Save');
		$this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.apply\');"]']);
		$this->see('Item successfully saved');
		$this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.save\');"]']);
	}


	 /**
	* Create mymuse coupon
	* @param object mock : object of a coupon 
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function createMymuseCoupon($mock)
	 {
	 

		$this->comment('Coupon creation in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=coupons');
		$this->waitForElement(array('class' => 'page-title'));

		$this->comment('Click new Coupon button');
		$this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'coupon.add\');"]']);
		$this->waitForElement(array('class' => 'page-title'));

		$this->fillField(array('id' => 'jform_title'), $mock->title);
		$this->fillField(array('id' => 'jform_code'), $mock->code);
		$this->selectOption(array('id' => 'jform_coupon_type'), $mock->coupon_type);

		if($mock->coupon_type == "Per Product" && isset($mock->product_id) && $mock->product_id > 0){
			$this->selectOption(array('id' => 'jform_product_id'), $mock->product_id);
		}

		$this->fillField(array('id' => 'jform_coupon_value'), $mock->coupon_value);
		$this->selectOptionInChosenById('jform_currency_id', $mock->currency_id);
		$this->selectOptionInChosenById('jform_coupon_value_type', $mock->coupon_value_type);
		$this->fillField(array('id' => 'jform_coupon_max_uses'), $mock->coupon_max_uses);
		$this->fillField(array('id' => 'jform_coupon_max_uses_per_user'), $mock->coupon_max_uses_per_user);
		$this->fillField(array('id' => 'jform_start_date'), $mock->start_date);
		$this->fillField(array('id' => 'jform_expiration_date'), $mock->expiration_date);

		$this->comment('Save');
		$this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'coupon.apply\');"]']);
		$this->see('Item successfully saved');

	}


	/**
	* MakeMenus
	* @param object mock : object of a menu 
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function makeMenus($mock)
	{
		//$menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All'
	 	$this->comment("I open the main menus page");
	 	$this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
	 	$this->waitForElement(array('class' => 'page-title'));
	 	//$this->checkForPhpNoticesOrWarnings();

	 	$this->comment("I click new");
	 	$this->click($this->adminToolbarButtonNew);
	 	$this->waitForElement(array('class' => 'page-title'));
	 	//$this->checkForPhpNoticesOrWarnings();
	 	$this->fillField(['id' => 'jform_title'], $mock->jform_title );


	 	$this->comment("Open the menu types iframe");
	 	$this->click(['css' => '#jform_type + button']);
	 	$this->waitForElement(['id' => 'menuTypeModal'], "30");
	 	$this->wait(1);
	 	$this->switchToIFrame("Menu Item Type");
	 	$this->comment("Choose the menu item type: $mock->menu_type");
	 	$this->wait(1);
	 	$this->click($mock->menu_item_type);
	 	$this->wait(1);
	 	$this->click($mock->menu_type);

	 	$this->comment('I switch back to the main window');
	 	$this->switchToIFrame();
	 	$this->wait(1);

	 	if($mock->jform_request_id_id && $mock->jform_request_id_name){
		// product menu
			$this->comment('Select Product '.$mock->jform_request_id_name.' '.$mock->jform_request_id_id);
			$this->click(['css'=> '#jform_request_id_name + button']);
			$this->wait(1);
			$this->switchToIFrame("Select a product");
			$this->wait(1);
			try 
			{
				$this->click(['css' => '.js-stools-btn-clear']);
			}
			catch (Exception $e)
			{
				$this->comment('Clear not enabled');
			}
			$this->click(['css'=> 'a[data-id="'.$mock->jform_request_id_id.'"]']);
			$this->switchToIFrame();
			$this->wait(1);
		}


	 	$this->comment('I save the menu');
	 	$this->click("Save");

	 	$this->waitForText('Menu item saved.', '15', ['id' => 'system-message-container']);

	 	return;

 
	 }

	 public function createMenuItem2($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
	 {
		$this->comment("I open the main menus page");
		$this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$this->waitForElement(array('class' => 'page-title'));
		try
		{
			$this->click(['css' => '.js-stools-btn-clear']);
		}
		catch (Exception $e)
		{
			$this->comment("Clear button not enabled, continue");

		}
		

		$this->comment("I click new");
		$this->click($this->adminToolbarButtonNew);
		$this->waitForElement(array('class' => 'page-title'));
		//$this->checkForPhpNoticesOrWarnings();
		$this->fillField(['id' => 'jform_title'], $menuTitle );

		$this->comment("Open the menu types iframe");
		$this->click(['css' => '#jform_type + button']);
		$this->waitForElement(['id' => 'menuTypeModal'], "30");
		$this->wait(1);
		$this->switchToIFrame("Menu Item Type");
		$this->comment("Choose the menu item type: $menuCategory");
		$this->wait(1);
		$this->click($menuCategory);
		$this->wait(1);
		$this->comment("Choose the menu item type: $menuItem");
		$this->wait(1);
	 	$this->click($menuItem);
	 	$this->wait(1);

		$this->comment('I switch back to the main window');
		$this->switchToIFrame();
		$this->comment('I leave time to the iframe to close');
		$this->wait(2);
		$this->comment('I save the menu');
		$this->click("Save");
		$this->wait(2);
		$this->waitForText('Menu item saved', 30, array('id' => 'system-message-container'));
	}


	/**
	* placeIteminCart
	* @param object mock : object of an item
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function placeIteminCart($mock)
	{
		$this->comment('Select Page '.$mock->menu_link);
		$this->comment('mock = '.print_r($mock, true));
		$this->amOnPage('index.php');
		$this->click($mock->menu_link);
		foreach($mock->select as $select){
			$this->click(['id' => $select]);
			$this->wait($this->shortwait);		 
		}

	}

	/**
	* changeStoreConfig
	* @param object mock : object of an item
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function changeStoreConfig($mock)
	{
		//$this->comment('mock = '.print_r($mock, true));
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=store&layout=edit&id=1');
		$this->waitForElement(['class' => 'page-title']);
		$this->click($mock->tab);

		foreach($mock->select as $select){
			//$this->scrollTo(array('css' => '#'.$select['option']));
			if($select['type'] == "select"){
				$this->selectOption(['id' => $select['option']], $select['value']);

			}elseif($select['type'] == "radio"){
				$this->selectOptionInRadioField($select['option'], $select['value']);

			}elseif($select['type'] == "multiSelect"){
				$this->selectMultipleOptionsInChosen($select['option'], $select['value']);
				$this->wait(1); 
				$this->click($mock->tab);
			}elseif($select['type'] == "text"){
				$this->scrollTo("#".$select['option']);
				$this->fillField(array('id' => $select['option']), $select['value']);
				if($select['option'] == 'jform_params_my_delay_fadeout'){
					$this->shortwait = 4;
				}
			}	 
		}
		
		$this->click(['xpath' => '//*[@id="toolbar-apply"]/button']);
		$this->see('Item saved');
	}

	/**
	* changeGlobalOptions
	* @param object mock : object of an item
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function changeGlobalOptions($mock)
	{

		//$this->comment('mock = '.print_r($mock, true));
		$this->amOnPage('administrator/index.php?option=com_config&view=component&component='.$mock->component);
		$this->waitForElement(array('class' => 'page-title'));
		$this->click($mock->tab);
		foreach($mock->select as $select){
			if($select['type'] == "select"){
				$this->selectOptionInChosenById($select['option'], $select['value']);
				$this->wait(1);	 
			}elseif($select['type'] == "radio"){
				$this->selectOptionInSwitcherRadioField($select['option']);
				$this->wait(1); 
			}elseif($select['type'] == "text"){
				$this->fillField(array('id' => $select['option']), $select['value']);
			}

		}
		$this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'config.save.component.save\');"]']);
		//$this->see('Configuration saved');
	}

	/**
	* changeReportOptions
	* @param object mock : object of an item
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function changeReportOptions($mock)
	{

		//$this->comment('mock = '.print_r($mock, true));
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=reports');
		$this->waitForElement(array('class' => 'page-title'));
		foreach($mock->select as $select){
			if($select['type'] == "select"){
				$this->selectOptionInChosenById($select['option'], $select['value']);
				$this->wait(1);	 
			}elseif($select['type'] == "radio"){
				$this->selectOptionInRadioField($select['option'], $select['value']);
				$this->wait(1); 
			}elseif($select['type'] == "text"){
				$this->fillField(array('id' => $select['option']), $select['value']);
			}
		}
		$this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'config.save.component.save\');"]']);
		//$this->see('Configuration saved');
	}


	/**
	 * Changes the module options
	 *
	 * @param	 string	$module	The full name of the module
	 *
	 * @return	void
	 *
	 * @since	 3.0.0
	 * @throws	\Exception
	 */
	public function editModule($mock)
	{
			$this->amOnPage('administrator/index.php?option=com_modules');
		$this->waitForElement(array('class' => 'page-title'));
		$this->searchForItem($mock->module);
		$this->click(array('link' => $mock->module));
		$this->wait(1);
		if($mock->tab){
			$this->click($mock->tab);
		}

		foreach($mock->select as $select){
			if($select['type'] == "select"){
				$this->selectOptionInChosenById($select['option'], $select['value']);
			}elseif($select['type'] == "radio"){
				$this->selectOptionInRadioField($select['option'], $select['value']);

			}elseif($select['type'] == "multiSelect"){
				$this->selectMultipleOptionsInChosen($select['option'], $select['value']);
				$this->wait(1); 
				$this->click($mock->tab);
			}elseif($select['type'] == "text"){
				$this->fillField(array('id' => $select['option']), $select['value']);

			}	 
		}

		$this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'module.apply\');"]']);
		$this->see('Module saved');

	}
	/**
	* fillFullRegForm
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function fillFullRegForm($mock_user)
	{
		$this->amOnPage('index.php/edit-profile?view=registration');
		$this->fillField(array('id' => 'jform_name'), $mock_user->jform_user);
		$this->fillField(array('id' => 'jform_username'), $mock_user->jform_username);
		$this->fillField(array('id' => 'jform_password1'), $mock_user->jform_password1);
		$this->fillField(array('id' => 'jform_password2'), $mock_user->jform_password2);
		$this->fillField(array('id' => 'jform_email1'), $mock_user->jform_email1);
		$this->fillField(array('id' => 'jform_email2'), $mock_user->jform_email2);
		$this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
		$this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
		$this->fillField(array('id' => 'jform_profile_city'), $mock_user->jform_profile_city);
		$this->fillField(array('id' => 'jform_profile_postal_code'), $mock_user->jform_profile_postal_code);
		$this->fillField(array('id' => 'jform_profile_phone'), $mock_user->jform_profile_phone);
		$this->fillField(array('id' => 'jform_profile_mobile'), $mock_user->jform_profile_mobile);
		$this->selectOptionInChosenByIdUsingJs('jform_profile_country', $mock_user->jform_profile_country); 
		$this->wait(1);
		$this->selectOptionInChosenByIdUsingJs('jform_profile_region', $mock_user->jform_profile_region);
		$this->wait(1);
		$this->click('Register');
	}


	/**
	* fillNoRegForm
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	public function fillNoRegForm($mock_user)
	{
		$this->fillField(array('id' => 'jform_profile_first_name'), $mock_user->jform_profile_first_name);
		$this->fillField(array('id' => 'jform_profile_last_name'), $mock_user->jform_profile_last_name);
		$this->fillField(array('id' => 'jform_profile_email'), $mock_user->jform_profile_email);
		$this->selectOptionInChosenByIdUsingJs('jform_profile_country', $mock_user->jform_profile_country); 
		$this->wait(1);
		$this->selectOptionInChosenByIdUsingJs('jform_profile_region', $mock_user->jform_profile_region);
		$this->wait(1);
		//$this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
		//$this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
		$this->click('Save');

	}










	 /* ########################## clear functions ############################### */


	/**
	 * Clear orders
	 *
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function clearOrders()
	{
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
		$this->waitForElement(array('class' => 'page-title'));
		try
		{
			$this->click(["xpath" => "//input[@name='checkall-toggle']"]);
		}
		catch (Exception $e)
		{
			$this->comment("No orders to delete");

			return;
		}
		$this->click("#toolbar-status-group");
		$this->wait(1);
		$this->click($this->childrenDelete);
		$this->wait(2);
		$this->acceptPopup();
	}


	/**
	 * Clear menus
	 *
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function clearMenus()
	{
	 
		$this->comment('Clear MyMuse Menus: put items in trash');
		$this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$this->waitForElement(array('class' => 'page-title'));

		if($this->seePageHasElement('#cb1')){

			$this->comment('Check All on main page');
			$this->click(["xpath" => "//input[@name='checkall-toggle']"]);
			$this->click(["css" => "#cb0"]);
			$this->comment('Trash the menu items');
			$this->click($this->clickUserToolbarButton('trash'));

		}

		// clear trashed menus

		$this->comment('Check the trash');
		$this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$this->wait(2);
		
		try
		{
			$this->click(['class' => 'js-stools-btn-filter']);
		}
		catch (Exception $e)
		{
			$this->comment("Search tools button does not exist on this page. No menus to delete");

			return;
		}
		
		$this->wait(1);
		$this->selectOption('#filter_published', 'Trashed');
		$this->wait(2);

		$this->comment('In status Trashed. Check for checkall-toggle');
		try
		{	
			$this->seeElement(["xpath" => "//input[@name='checkall-toggle']"]);
			
		}
		catch (Exception $e)
		{
			$this->comment("Check all does not exist on this page, skipping");
			return;
		}
		$this->click(["xpath" => "//input[@name='checkall-toggle']"]);
		$this->comment('Click on Trash button ');
		$this->click(['css' => '#toolbar-delete button']);
		$this->acceptPopup();
		$this->waitForText('deleted', '30', array('id' => 'system-message-container'));
		$this->click(['css' => '.js-stools-btn-clear']);
		$this->comment('Menus are clear ');

	}
	/**
	 * Clear trashed menus
	 *
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function clearTrashedMenus()
	{
	 
		$this->comment('Clear MyMuse Trashed Menus ');
		$this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
		$this->waitForElement(array('class' => 'page-title'));


		$this->comment('Open Search Tools ');
		$this->click(["css" => ".js-stools-btn-filter"]);
		$this->wait(2);

		$this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

		$this->executeJS("document.getElementById('filter_published').style.display = 'block';");


		$this->comment('Select Status Trashed ');
		$this->selectOptionInChosenById('filter_published', 'Trashed');

		if($this->seePageHasText("Single")){
			$this->waitForElement(array("name" => "checkall-toggle"));

			$this->comment('Check All Trashed Items');
			$this->click(["name" => "checkall-toggle"]);

			$this->comment('Empty Trash ');
			$this->click(["css" => "#toolbar-delete > button"]);

			$this->acceptPopup();
			$this->waitForText('deleted', '30', array('id' => 'system-message-container'));
		}


	}
	/**
	 * Clear users
	 *
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function clearUsers()
	{
	 
		$this->comment('Clear MyMuse Users ');
		$this->amOnPage('administrator/index.php?option=com_users&view=users');
		$this->waitForElement(array('class' => 'page-title'));

		$this->searchForItem("Test User");
		if(!$this->seePageHasText("No Matching Results")){
			$this->click(['id' => 'cb0']);
			$this->clickUserToolbarButton('delete');
			//$this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

			$this->acceptPopup();
			$this->waitForText('deleted', '30', array('id' => 'system-message-container'));
		}
		$this->searchForItem("Buyer");
		if(!$this->seePageHasText("No Matching Results")){
			$this->click(['id' => 'cb0']);
			$this->clickUserToolbarButton('delete');
			//$this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

			$this->acceptPopup();
			$this->waitForText('deleted', '30', array('id' => 'system-message-container'));
		}

		
	}

	/**
	 * Clear mymuse categories
	 *
	 * @return	void
	 *
	 * @since	 3.7.5
	 * @throws	\Exception
	 */
	public function clearMymuseCategories()
	{
	 

		$this->comment('Clear Categories	in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');
		$this->waitForElement(array('class' => 'page-title'));

		$this->comment('Check All');
		try
		{
			$this->seeElement(["xpath" => "//input[@name='checkall-toggle']"]);
			$this->click(["xpath" => "//input[@name='checkall-toggle']"]);
			$this->comment('Click on Trash button ');
			$this->click($this->clickUserToolbarButton('trash'));
		}
		catch (Exception $e)
		{
			$this->comment("Check all does not exist on this page, skipping");
		}


		$this->comment('Open Search Tools ');
		$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');
		try
		{
			$this->seeElement(['class' => 'js-stools-btn-filter']);
		}
		catch (Exception $e)
		{
			$this->comment("Search tools button does not exist on this page. No categories to delete");

			return;
		}

		$this->click(['class' => 'js-stools-btn-filter']);
		$this->wait(2);


		$this->selectOption('#filter_published', 'Trashed');
		$this->wait(2);

		$this->waitForElement(array("name" => "checkall-toggle"));

		$this->comment('Check All Trashed Items');
		$this->click(["name" => "checkall-toggle"]);

		$this->comment('Empty Trash ');
		$this->click(["css" => "#toolbar-delete > button"]);

		$this->acceptPopup();
		$this->waitForText('deleted', '30', array('id' => 'system-message-container'));

	}


		/**
	* Clear mymuse products
	*
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 	public function clearMymuseProducts()
	 	{
	 

		$this->comment('Clear products	in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=products');

		$this->comment('Check All');
		try
		{
				$this->seeElement(["xpath" => "//input[@name='checkall-toggle']"]);
				$this->click(["xpath" => "//input[@name='checkall-toggle']"]);
				$this->comment('Click on Trash button ');
				$this->click($this->adminToolbarButtonTrash);
				$this->see('successfully trashed');
				
		}
		catch (Exception $e)
		{
				$this->comment("Check all does not exist on this page, skipping");
		}


		try
		{
			$this->seeElement(['class' => 'js-stools-btn-filter']);
		}
		catch (Exception $e)
		{
			$this->comment("Search tools button does not exist on this page, skipping");

			return;
		}

		$this->click(['class' => 'js-stools-btn-filter']);
		$this->wait(2);


		$this->selectOption('#filter_published', 'Trashed');
		$this->wait(2);

		try
		{
			$this->seeElement(["xpath" => "//input[@name='checkall-toggle']"]);
		}
		catch (Exception $e)
		{
			$this->comment("Check all toggle was not found. No products to delete.");

			return;
		}

		$this->comment('Check All Trashed Items');
		$this->click(["name" => "checkall-toggle"]);

		$this->comment('Empty Trash ');
		$this->click(["css" => "#toolbar-delete > button"]);

		//$this->waitForText('No Matching Results', '30', array('id' => 'system-message-container'));

		}


	/**
	* Clear mymuse Coupons
	*
	* @return	void
	*
	* @since	 3.7.5
	* @throws	\Exception
	*/
	 public function clearMymuseCoupons()
	 {
	 

		$this->comment('Clear Coupons	in /administrator/ ');
		$this->amOnPage('administrator/index.php?option=com_mymuse&view=coupons');
		if($this->seePageHasText('Trash')){
			$this->selectOptionInChosenById('filter_published', '- Select Status -');
			$this->waitForElement(["xpath" => "//input[@name='checkall-toggle']"]);

			$this->comment('Check All');
			$this->click(["xpath" => "//input[@name='checkall-toggle']"]);



			$this->comment('Click on Trash button ');
			$this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);
			$this->see('successfully trashed');

			// $this->selectOption(array('id' => 'filter_published'), 'Trashed');
			$this->selectOptionInChosenById('filter_published', 'Trashed');

			$this->click(["css" => 'input[name="checkall-toggle"]']);
			$this->click(["css" => '.button-delete']);
		}
	 }

}
