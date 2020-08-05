<?php
defined('_JEXEC') or die();

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
jimport('joomla.form.helper');
jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('list');


class JFormFieldCountry extends JFormFieldList {

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
          
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$values = $db->loadObjectList();
		$options[] = JHtml::_('select.option', '', JText::_('MYMUSE_SELECT_COUNTRY'));
		
		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


}