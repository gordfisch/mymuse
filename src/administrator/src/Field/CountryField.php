<?php

/**
 *
 * @version     $Id$
 * @package    MyMuse
 * @author Gordon Fisch
 * @link https://joomlamymuse.com
 * @copyright Copyright (c) 2018 Arboreta Internet Service. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */
namespace Joomla\Component\Mymuse\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class CountryField extends ListField {

	/**
	 * Element name
	 * @access    protected
	 * @var        string
	 */
	var $type = 'Country';

	protected function getInput() {
		$this->multiple=false;
		return parent::getInput();
	}

	protected function getOptions() {
		$options = array();
		$this->multiple=false;

		$query = 'SELECT `country_2_code` AS value, `country_name` AS text FROM `#__mymuse_country`
               		ORDER BY `country_name` ASC ';
          
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery($query);
		$values = $db->loadObjectList();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_MYMUSE_SELECT_COUNTRY'));
		
		foreach ($values as $v) {
			$options[] = HTMLHelper::_('select.option', $v->value, $v->text);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


}