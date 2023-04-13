<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

defined('JPATH_BASE') or die;

/**
 * Plugin class for login redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.logout
 */
class plgUserRedirectonlogin extends CMSPlugin 
{
	/**
	 * Application object
	 *
	 * @var    CMSApplicationInterface
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Database Driver Instance
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

    /**
     * Constructor
     *
     * @param   object  $subject  The object to observe
     * @param   array   $config   An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->app = Factory::getApplication();

    }

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */

	public function onUserLogin($user, $options = [])
	{

		
		$session = Factory::getSession();
		$cart = $session->get('cart');
		$user = Factory::getUser();

		if($cart && $cart['idx'] > 0 && $user->username != ''){
			$return = Route::_("index.php?option=com_mymuse&view=cart&task=showcart");
			$this->app->setUserState('users.login.form.return', $return);
		}
		return true;
	}
	
    /**
     * Hooks on the Joomla! login event. Detects silent logins and disables the Multi-Factor
     * Authentication page in this case.
     *
     * Moreover, it will save the redirection URL and the Captive URL which is necessary in Joomla 4. You see, in Joomla
     * 4 having unified sessions turned on makes the backend login redirect you to the frontend of the site AFTER
     * logging in, something which would cause the Captive page to appear in the frontend and redirect you to the public
     * frontend homepage after successfully passing the Two Step verification process.
     *
     * @param   array  $options  Passed by Joomla. user: a User object; responseType: string, authentication response type.
     *
     * @return void
     * @since  4.2.0
     */
    public function onUserAfterLogin(array $options): void
    {
        if (!($this->app->isClient('administrator')) && !($this->app->isClient('site'))) {
            return;
        }

        $this->disableMfaOnSilentLogin($options);

        $session = Factory::getSession();
        $cart = $session->get('cart');
        $user = $options['user'];

        if($cart && $cart['idx'] > 0 && $user->username != ''){
        	$return = Route::_("index.php?option=com_mymuse&view=cart&task=showcart");
        	$this->app->setUserState('users.login.form.return', $return);
        }

    }

    /**
     * Detect silent logins and disable MFA if the relevant com_users option is set.
     *
     * @param   array  $options  The array of login options and login result
     *
     * @return  void
     * @since   4.2.0
     */
    private function disableMfaOnSilentLogin(array $options): void
    {
        $userParams         = ComponentHelper::getParams('com_users');
        $doMfaOnSilentLogin = $userParams->get('mfaonsilent', 0) == 1;

        // Should I show MFA even on silent logins? Default: 1 (yes, show)
        if ($doMfaOnSilentLogin) {
            return;
        }

        // Make sure I have a valid user
        /** @var User $user */
        $user = $options['user'];

        if (!is_object($user) || !($user instanceof User) || $user->guest) {
            return;
        }

        $silentResponseTypes = array_map(
            'trim',
            explode(',', $userParams->get('silentresponses', '') ?: '')
        );
        $silentResponseTypes = $silentResponseTypes ?: ['cookie', 'passwordless'];

        // Only proceed if this is not a silent login
        if (!in_array(strtolower($options['responseType'] ?? ''), $silentResponseTypes)) {
            return;
        }

        // Set the flag indicating that MFA is already checked.
        $this->app->getSession()->set('com_users.mfa_checked', 1);
    }	

}
