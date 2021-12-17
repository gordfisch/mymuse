<?php

/**
 *
 * @version     $Id$
 * @package    MyMuse
 * @author Gordon Fisch
 * @link https://joomlamymuse.com
 * @copyright Copyright (c) 2021 Arboreta Internet Service. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

namespace Joomla\Component\Mymuse\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;


class CountriesField extends ListField {

	/**
	 * Element name
	 * @access    protected
	 * @var        string
	 */
	var $type = 'Countries';

	protected function getInput() {
		$this->multiple=true;
		return parent::getInput();
	}

	protected function getOptions() {
		$options = array();
		$this->multiple=true;

		$query = 'SELECT `id` AS value, `country_name` AS text FROM `#__mymuse_country`
               		ORDER BY `country_name` ASC ';
          
		$db = Factory::getDBO();
		$db->setQuery($query);
		$values = $db->loadObjectList();
		foreach ($values as $v) {
			$options[] = JHtml::_('select.option', $v->value, $v->text);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


}