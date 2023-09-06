<?php
/**
 * @version		$Id: preorder.php 1932 2017-11-24 14:08:35Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\CartHelper;
/**
* MyMuse PreOrder plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/

class plgMymusePreOrder extends CMSPlugin 
{
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		$this->plgMymusePreOrder($subject, $config);
	}
	
	function plgMymusePreOrder(&$subject, $config)  {
		parent::__construct($subject, $config);

	}
		
		
	/**
	 * Offline Payment method
	 *
	 */
	function onBeforeMyMuseCheckout(&$shopper, &$store, &$cart, &$params, &$Itemid )
	{
		$msg = $this->params->get ( 'my_msg' );
		$string = '';
		$app = Factory::getApplication();
		$jinput = $app->input;
		
		if ($this->params->get ( 'which_min' ) == "number") {
			if ($cart ['idx'] < $this->params->get ( 'my_min' )) {
				$string .= '$msg = "' . Text::_ ( $msg ) . '"';
				//$jinput->set( "task", "" );
				$app->redirect( Route::_("index.php?option=com_mymuse&task=showcart&view=cart"), $msg );
			}
		} elseif ($this->params->get ( 'which_min' ) == "price") {
			require_once (JPATH_COMPONENT . DS . 'mymuse.class.php');
			$MyMuseCart = new CartHelper;
			$order = $MyMuseCart->buildOrder ( false );
			if ($order->order_subtotal < $this->params->get ( 'my_min' )) {
				$string .= '$msg = "' . Text::_ ( $msg ) . '"';
				//$jinput->set( "task", "" );
				$app->redirect( Route::_("index.php?option=com_mymuse&task=showcart&view=cart"), $msg );
			}
		}
		
		return $string;
	
	}
} ?>