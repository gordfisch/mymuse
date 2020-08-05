<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Taxrate Table class.
 *
 * @since  1.0
 */
class TaxrateTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $_jsonEncode = array('params', 'metadata');

	/**
	 * Hold product_data table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_data = null;

	/**
	 * Hold product_physical table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_physical = null;

	/**
	 * Hold product_digital table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_digital = null;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_mymuse.product';
		$this->setColumnAlias('published', 'state');
		$this->storage = $GLOBALS['mymuseStorage'];
		$this->$_product_data		= Table::getInstance('Productdata', 'MymuseTable', array());
		$this->$_product_physical	= Table::getInstance('Productphysical', 'MymuseTable', array());
		$this->$_product_digital	= Table::getInstance('Productdigital', 'MymuseTable', array());

		parent::__construct('#__mymuse_product', 'id', $db);


	}

	public function bind($array, $ignore = '')
	{
		$input 	= JFactory::getApplication()->input;
		$form 	= $input->get('jform', array(), 'ARRAY'); 

		if(isset($form['articletext'])){
			$array['articletext'] = $form['articletext'];
		}
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext'])) {
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos	= preg_match($pattern, $array['articletext']);

			if ($tagPos == 0) {
				$this->introtext	= $array['articletext'];
				$this->fulltext         = '';
			} else {
				list($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}

		}

		if (isset($array['attribs']) && is_array($array['attribs'])) {
			$registry = new JRegistry;
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		if (!isset($array['alias']) || trim($array['alias']) == '') {
			$array['alias'] = $array['title'];
		}
		$array['alias'] =  ApplicationHelper::stringURLSafe($array['alias']);


		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		$app = JFactory::getApplication();
		$this->title = trim($this->title);

		if ($this->title == '') {
			$this->setError(JText::_('MYMUSE_FILE_MUST_HAVE_A_TITLE'));
			return false;
		}

		if (trim($this->alias) == '') {
			$this->alias = $this->title;
		}

		$this->alias =  JApplicationHelper::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up) {
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}
		
		//set a default publish up
		if(!isset($this->publish_up) || $this->publish_up == "" || $this->publish_up == "0000-00-00 00:00:00"){
			$this->publish_up = JFactory::getDate()->format('Y-m-d H:i:s');
		}
		
		if($this->publish_down == "1970-01-01 00:00:01" || $this->publish_down == $this->publish_up){
			$this->publish_down = '';
		}
		
		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// Only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();

			foreach($keys as $key) {
				if (trim($key)) {
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}
		


		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) {
			$this->ordering = self::getNextOrder();
		}

		return true;
	}
	/**
	 * Stores a taxrate.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		if (is_null($this->checked_out))
		{
			$this->checked_out = 0;
		}

		return parent::store($updateNulls);
	}




	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return 'com_mymuse.taxrate';
	}
}
