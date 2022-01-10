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
print_r($cart); exit;
		if($cart && $cart['idx'] > 0 && $user->username != ''){
			$return = Route::_("index.php?option=com_mymuse&view=cart&task=showcart");
			$app->setUserState('users.login.form.return', $return);
		}
		return true;
	}
	
	
	

}
