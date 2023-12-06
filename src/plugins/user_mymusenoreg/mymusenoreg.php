<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2022 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 * 
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A profile plugin for no registration.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */

class plgUserMymusenoreg extends CMSPlugin 
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  4.0.0
     */
    protected $app;

    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     * @since  4.0.0
     */
    protected $db;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Date of birth.
     *
     * @var    string
     *
     * @since  3.1
     */
    private $date = '';


    /**
     * Constructor
     *
     * @param   object  $subject  The object to observe
     * @param   array   $config   An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
    	parent::__construct($subject, $config);
    	FormHelper::addFieldPath(__DIR__ . '/fields');
    	$lang = Factory::getLanguage();
    	$lang->load('plg_user_mymusenoreg', JPATH_ADMINISTRATOR);

    	//$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    	//$wa->useScript('jquery');
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
    public function onUserLogin($user, $options = array())
    {

    	$instance = $this->_getUser($user, $options);
    	
    	// Load the profile data from the database.
    	$app = Factory::getApplication();
    	$myparams = MymuseHelper::getParams();
    	$profile_key = $myparams->get('my_profile_key', 'mymuse');
    	$userId = $instance->id;
    	$db = Factory::getContainer()->get('DatabaseDriver');
    	$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
    			' WHERE user_id = '.(int) $userId." AND profile_key LIKE '$profile_key.%'" .
    			' ORDER BY ordering';

    	// Check for a database error.
    	try
    	{
    		$db->setQuery( $query);
    		$results = $db->loadRowList();
    	}
    	catch (RuntimeException $e)
    	{
    	    $this->_subject->setError($e->getMessage());
    	    return false;
    	}

    	
    	// Merge the profile data.
    	$instance->profile = array();
    	
    	foreach ($results as $v)
    	{
    		$k = str_replace('mymuse.', '', $v[0]);
    		$instance->profile[$k] = json_decode($v[1], true);
    		if ($instance->profile[$k] === null)
    		{
    			$instance->profile[$k] = $v[1];
    		}
    		if($k == "region"){
    			
    			
    			
    		}
    	}
    	$session = Factory::getSession();
    	$session->set('user', $instance);

    
    }

	
	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_mymuse.noreg'))) {
			return true;
		}

		$myparams = MymuseHelper::getParams();
		$profile_key = $myparams->get('my_profile_key', 'mymuse');
		
		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0) {

				// Load the profile data from the database.
				$db = Factory::getContainer()->get('DatabaseDriver');
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $userId." AND profile_key LIKE '$profile_key.%'" .
					' ORDER BY ordering'
				);
				
				// Check for a database error.
				try
				{
					$db->setQuery( $query);
					$results = $db->loadRowList();
				}
				catch (RuntimeException $e)
				{
				    $this->_subject->setError($e->getMessage());
				    return false;
				}

				// Merge the profile data.
				$data->profile = array();

				foreach ($results as $v)
				{
					$k = str_replace('mymuse.', '', $v[0]);
					$val = json_decode($v[1], true);
					if($k == "region"){
						if(!isset($_REQUEST['layout'])){
							$val = $this->_getStateName($val);
						}
					}
					if($k == "shipping_region"){
						if(!isset($_REQUEST['layout'])){
							$val = $this->_getStateName($val);
						}
					}
					$data->profile[$k] = $val;
					
					if ($data->profile[$k] === null)
					{
						if($k == "region"){
							if(!isset($_REQUEST['layout'])){
								$v[1] = $this->_getStateName($v[1]);
							}
						}
						$data->profile[$k] = $v[1];
					}
					if ($data->profile[$k] === null)
					{
						if($k == "shipping_region"){
							if(!isset($_REQUEST['layout'])){
								$v[1] = $this->_getStateName($v[1]);
							}
						}
						$data->profile[$k] = $v[1];
					}
				}
			}

			if (!HTMLHelper::isRegistered('users.url')) {
				HTMLHelper::register('users.url', array(__CLASS__, 'url'));
			}
			if (!HTMLHelper::isRegistered('users.calendar')) {
				HTMLHelper::register('users.calendar', array(__CLASS__, 'calendar'));
			}
			if (!HTMLHelper::isRegistered('users.tos')) {
				HTMLHelper::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}
	

	/**
	 * Returns an anchor tag generated from a given value
	 *
	 * @param   string  $value  URL to use
	 *
	 * @return  mixed|string
	 */

	public static function url($value)
	{
		if (empty($value))
		{
			return HTMLHelper::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if(substr ($value, 0, 4) == "http") {
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else {
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	/**
	 * Returns html markup showing a date picker
	 *
	 * @param   string  $value  valid date string
	 *
	 * @return  mixed
	 */
	public static function calendar($value)
	{
		if (empty($value)) {
			return HTMLHelper::_('users.value', $value);
		} else {
			return HTMLHelper::_('date', $value, null, null);
		}
	}

	/**
     * Return the translated strings yes or no depending on the value
     *
     * @param   boolean  $value  input value
     *
     * @return  string
     */
	public static function tos($value)
	{
		if ($value) {
			return Text::_('JYES');
		}
		else {
			return Text::_('JNO');
		}
	}

	/**
	 * @param	Form	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		$name = $form->getName();

		if (!in_array($name, ['com_mymuse.noreg'])) {
            return true;
        }

        // Add the registration fields to the form.
        FormHelper::addFieldPrefix('Joomla\\Plugin\\User\\Mymusenoreg\\Field');
        FormHelper::addFormPath(__DIR__ . '/forms');

        $form->loadFile('profile');


		$params 		    = MymuseHelper::getParams();

		$shipping_needed = 0;
		$session = Factory::getSession();
		if (!$session->get("cart",0)) {
			$cart = array();
			$cart["idx"] = 0;
		}else{
			$cart = $session->get("cart");
		}
		for ($i=0;$i<$cart["idx"];$i++) {
        	if(isset($cart[$i]["product_physical"]) && $cart[$i]["product_physical"] && $params->get('my_use_shipping')){
        		$shipping_needed = 1;
        	}
        }

		global $doneUserForm;
		if (!($form instanceof Form))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		if($doneUserForm){
			return true;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array($name, array('com_mymuse.noreg'))) {
			return true;
		}
		
		$country_states_done 	= 0;
		$changeDynaList  		= 0;


		// Add the registration fields to the form.
		FormHelper::addFieldPrefix('Joomla\\Plugin\\User\\Mymuseuser\\Field');
		Form::addFormPath(dirname(__FILE__).'/forms');
		$form->loadFile('profile');

		$fields = MymuseHelper::getNoRegFields();
	
		$tosarticle = $this->params->get('register_tos_article');
		$tosenabled = $this->params->get('register-require_tos', 0);
		
		// We need to be in the registration form, field needs to be enabled and we need an article ID
		if ($name != 'com_mymuse.noreg' || !$tosenabled || !$tosarticle)
		{
			// We only want the TOS in the registration form
			$form->removeField('tos', 'profile');
		}
		else
		{
			// Push the TOS article ID into the TOS field.
			$form->setFieldAttribute('tos', 'article', $tosarticle, 'profile');
		}
	
 
		foreach ($fields as $field) {

			// Case registration
			if ($name == 'com_mymuse.noreg') {
				// Toggle whether the field is required.
				
				if ($this->params->get('register-require_' . $field, 1) > 0) {

					$boolean = 0;

					if(($this->params->get('register-require_' . $field) == 2) 
					&& 
					(
						(strstr($field, 'shipping') && $shipping_needed) || !strstr($field, 'shipping')
					)) {
						$boolean = 1;
					}
					$form->setFieldAttribute($field, 'required', ($boolean) ? 'required' : '', 'profile');

				if($field == 'region'){
						$q = "SELECT '' as value, '".Text::_('COM_MYMUSE_SELECT_STATE')."' as region, 0 as country_id  UNION 
								SELECT id as value, state_name as region, country_id FROM #__mymuse_state 
						ORDER by country_id, region";
						$form->setFieldAttribute($field, 'query', $q, 'profile');
					}
				if($field == 'country'){
					$db = Factory::getContainer()->get('DatabaseDriver');
					$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
					$db->setQuery($query);
					$store = $db->loadObject();
					$sparams = new Registry($store->params);
					$country_2_code = $sparams->get('country');
					$query = "SELECT country_3_code FROM #__mymuse_country WHERE 
					country_2_code='$country_2_code'";
		
					$db->setQuery($query);
					$country = $db->loadResult();
					
					$form->setFieldAttribute($field, 'default', $country, 'profile');
					
					$q = "SELECT '' as value, '".Text::_('COM_MYMUSE_SELECT_COUNTRY')."' as country, 0 as ordering 
								UNION SELECT country_3_code as value, country_name as country, ordering 
								FROM #__mymuse_country ORDER by country";
					
					$form->setFieldAttribute($field, 'query', $q, 'profile');
			
						
					$countrystates = $this->listCountryState();
					$javascript = '
					var countrystates = new Array;
					';
					$i = 0;
					foreach ($countrystates as $k=>$items) {
						foreach ($items as $v) {
							$javascript .= "countrystates[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
						}
					}
					
					$document = Factory::getDocument();
					$document->addScriptDeclaration($javascript);
					$country_states_done = 1;
					
					$js = "/**
					* Changes a dynamically generated list
					* @param html obj The name of the list to change
					* @param html obj The instigator of the change
					* @param array A javascript array of list options in the form [key,value,text]
					* @param string The original key that was selected
					* @param string The original item value that was selected
					*/
						function changeDynaList2( list, source, myarr, orig_key, orig_val) {

							var key = source.options[source.selectedIndex].value;

							// empty the list
							for (i in list.options.length) {
								list.options[i] = null;
							}
							i = 0;
							for (x in myarr) {
								if (myarr[x][0] == key) {
									opt = new Option();
									opt.value = myarr[x][1];
									opt.text = myarr[x][2];
						
									if ((orig_key == key && orig_val == opt.value) || i == 0) {
										opt.selected = true;
									}
									list.options[i++] = opt;
								}
							}
							list.length = i;
						}
						jQuery(document).ready(function(){
							changeDynaList2(Form_profile_region, Form_profile_country, countrystates,0,0); 
						});

						";
						
						$document->addScriptDeclaration($js);
						$changeDynaList = 1;
					}
					
					if($field == 'shipping_region'){
						$q = "SELECT '' as value, '".Text::_('COM_MYMUSE_SELECT_REGION')."' as shipping_region, 0 as country_id  UNION
								SELECT id as value, state_name as shipping_region, country_id FROM #__mymuse_state
						ORDER by country_id, shipping_region";
						$form->setFieldAttribute($field, 'query', $q, 'profile');
					}
					if($field == 'shipping_country'){
						/*
						$db = Factory::getContainer()->get('DatabaseDriver');
						$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
						$db->setQuery($query);
						$store = $db->loadObject();
						$sparams = new Registry($store->params);
						$country_2_code = $sparams->get('country');
						$query = "SELECT country_3_code FROM #__mymuse_country WHERE country_2_code='$country_2_code'";
					
						$db->setQuery($query);
						$country = $db->loadResult();

						$form->setFieldAttribute($field, 'default', $country, 'profile');
						*/
						$q = "SELECT '' as value, '".Text::_('COM_MYMUSE_SELECT_COUNTRY')."' as 
								shipping_country UNION SELECT country_3_code as value, 
								country_name as shipping_country FROM #__mymuse_country 
								ORDER by shipping_country";
						
						$form->setFieldAttribute($field, 'query', $q, 'profile');

						if(!$country_states_done){
							$countrystates = $this->listCountryState();
							$javascript = '
							var countrystates = new Array;
							';
							$i = 0;
							foreach ($countrystates as $k=>$items) {
								foreach ($items as $v) {
									$javascript .= "countrystates[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
								}
							}
							
							$document = Factory::getDocument();
							$document->addScriptDeclaration($javascript);
						}
						$js = '';
						if(!$changeDynaList){
							$js .= "/**
							* Changes a dynamically generated list
							* @param html obj The name of the list to change
							* @param html obj The instigator of the change
							* @param array A javascript array of list options in the form [key,value,text]
							* @param string The original key that was selected
							* @param string The original item value that was selected
							*/
								function changeDynaList2( list, source, myarr, orig_key, orig_val) {

									var key = source.options[source.selectedIndex].value;

									// empty the list
									for (i in list.options.length) {
										list.options[i] = null;
									}
									i = 0;
									for (x in myarr) {
										if (myarr[x][0] == key) {
											opt = new Option();
											opt.value = myarr[x][1];
											opt.text = myarr[x][2];
								
											if ((orig_key == key && orig_val == opt.value) || i == 0) {
												opt.selected = true;
											}
											list.options[i++] = opt;
										}
									}
									list.length = i;
								}
								;

								";
							}

							$js .= "
							jQuery(document).ready(function(){
									changeDynaList2(Form_profile_shipping_region, Form_profile_shipping_country, countrystates,0,0); 
								})
								";
							
							$document->addScriptDeclaration($js);
					}
				}
				else {
					$form->removeField($field, 'profile');
				}
				if($field == 'shopper_group'){
					$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
				}
				if($field == 'category_owner'){
					$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
				}
			}
			// Case profile in site or admin
			elseif ($name == 'com_admin.profile') {
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0) {
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
					if($field == 'shopper_group' && $name == 'com_users.profile'){
						$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
					}
					if($field == 'category_owner' && $name == 'com_users.profile'){
						$form->setFieldAttribute($field, 'type', 'hidden', 'profile');
					}
				
				}
				else {
					$form->removeField($field, 'profile');
				}
			}
		}

		return true;
	}










	
    /**
     * listCountryState
     * Print a select box
     *
     * @param string $list_name
     * @param string $value
     * @return bool
     */
   function listCountryState($country_select='', $state_select='', $store_country='') {

		$db	= Factory::getContainer()->get('DatabaseDriver');
		//echo "country = $country_select state = $state_select"; exit;
		$javascript = "onchange=\"changeDynaList( 'state', countrystates, document.adminForm.country.options[document.adminForm.country.selectedIndex].value, 0, 0);\"";
		
		$countries[] = HTMLHelper::_('select.option', '0', '- '.Text::_('COM_MYMUSE_SELECT_COUNTRY').' -');
		$query = "SELECT id, country_3_code as value, country_name as text from #__mymuse_country ORDER BY country_name ASC";
		$db->setQuery($query);
		$dbcountries = $db->loadObjectList();
		$countries = array_merge($countries, $dbcountries);
		$lists['country'] = HTMLHelper::_('select.genericlist',  $countries, 'country', 'class="inputbox" size="1" '.$javascript, 'value', 'text', $country_select);

	
		foreach ($dbcountries as $country)
		{
			$country_list[] = (int) $country->id;

			if ($country_select != '') {
				if ($country->value == $country_select) {
					$contentCountry = $country->text;
				}
			} 
		}

		$countrystates = array ();
		$countrystates[-1] = array ();
		$countrystates[-1][] = HTMLHelper::_('select.option', '-1', Text::_( 'COM_MYMUSE_SELECT_COUNTRY' ), 'id', 'title');
		$country_list = implode('\', \'', $country_list);

		$query = 'SELECT #__mymuse_state.id as code, state_name as title, #__mymuse_state.id as id, country_3_code, country_id' .
				' FROM #__mymuse_state,#__mymuse_country' .
				' WHERE country_id IN ( \''.$country_list.'\' )' .
				' AND #__mymuse_state.country_id=#__mymuse_country.id' .
				' ORDER BY country_id,state_name';

		$db->setQuery($query);
		$state_list = $db->loadObjectList();
		
		foreach ($dbcountries as $country)
		{

			$countrystates[$country->value] = array ();
			$rows2 = array ();
			foreach ($state_list as $state)
			{
				if ($state->country_3_code == $country->value) {
					$rows2[] = $state;
				}
			}
			foreach ($rows2 as $row2) {
				$countrystates[$country->value][] = HTMLHelper::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}

		$countrystates['-1'][] = HTMLHelper::_('select.option', '-1', Text::_( 'COM_MYMUSE_SELECT_STATE' ), 'id', 'title');

		return $countrystates;
		
   }
   
   /**
    * This method will return a user object
    *
    * If options['autoregister'] is true, if the user doesn't exist yet he will be created
    *
    * @param	array	$user		Holds the user data.
    * @param	array	$options	Array holding options (remember, autoregister, group).
    *
    * @return	object	A User object
    * @since	1.5
    */
   protected function _getUser($user, $options = array())
   {
   	$instance = User::getInstance();
   	if ($id = intval(UserHelper::getUserId($user['username'])))  {
   		$instance->load($id);
   		return $instance;
   	}
   
   	//TODO : move this out of the plugin
   	jimport('joomla.application.component.helper');
   	$config	= ComponentHelper::getParams('com_users');
   	// Default to Registered.
   	$defaultUserGroup = $config->get('new_usertype', 2);
   
   	$acl = Factory::getACL();
   
   	$instance->set('id'			, 0);
   	$instance->set('name'			, $user['fullname']);
   	$instance->set('username'		, $user['username']);
   	$instance->set('password_clear'	, $user['password_clear']);
   	$instance->set('email'			, $user['email']);	// Result should contain an email (check)
   	$instance->set('usertype'		, 'deprecated');
   	$instance->set('groups'		, array($defaultUserGroup));
   
   	//If autoregister is set let's register the user
   	$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);
   
   	if ($autoregister) {
   		if (!$instance->save()) {
   			Factory::getApplication()->enqueueMessage('SOME_ERROR_CODE', $instance->getError());
   		}
   	}
   	else {
   		// No existing user and autoregister off, this is a temporary user.
   		$instance->set('tmp_user', true);
   	}
   
   	return $instance;
   }
   
   /** _getStateName
    * 
    * get state name based id
    * 
    * @param int id
    * @return string the state name
    */
   function _getStateName($id=0)
   {

   		if(!$id){
   			return '';
   		}
   		$db = Factory::getContainer()->get('DatabaseDriver');
   		$query = "SELECT state_name FROM #__mymuse_state WHERE id=$id";
   		$db->setQuery($query);
   		$name = $db->loadResult();
   		return $name;
   }
}
