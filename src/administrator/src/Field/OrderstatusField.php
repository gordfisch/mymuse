<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 * 
 */

namespace Joomla\Component\Mymuse\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\SqlField;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('sql');

/**
 * Supports an HTML select list of options driven by SQL
 */
class OrderstatusField extends SqlField
{
	/**
	 * The form field type.
	 */
	public $type = 'orderstatus';

	/**
	 * Overrides parent's getinput method
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		$jinput = Factory::getApplication()->input;
		$this->value = array();

		//a filter_order_status coming in
		$filter_order_status = $jinput->get('filter_order_status', 0);
		if($filter_order_status){
			$this->value[$filter_order_status] = $filter_order_status;
		}

		if(!is_array($this->value)) $this->value = array($this->value);
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		
		//$html[] = '<select id="filter_order_status" name="filter_order_status" class="inputbox">';
		$html[] = '<select id="jform_'.$this->name.'" name="'.$this->name.'" '.trim($attr).'>';
		$filter_order_status = Factory::getApplication()->input->get('filter_order_status','0');
		// do the SQL
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query="SELECT 0 AS id, 'None selected' AS name 
		UNION ALL SELECT code as id, name FROM #__mymuse_order_status";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		
		
		// iterate through returned rows
		foreach( $rows as $row ){
			$selected = '';
			if($row->id === $filter_order_status){
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}

			$html[] = '<option value="'.$row->id.'" '.$selected.'>'.Text::_( 'COM_MYMUSE_'.strtoupper(str_replace(" ","_", $row->name)) ).'</option>';
		}

		// close the HTML select options
		$html[] = '</select>';

		// return the HTML
		return implode($html);
	}
}