<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

/**
 * Content Component HTML Helper
 *
 * @since  5.0.0
 */
class Icon
{
	/**
	 * The application
	 *
	 * @var    CMSApplication
	 *
	 * @since  5.0.0
	 */
	private $application;

	/**
	 * Service constructor
	 *
	 * @param   CMSApplication  $application  The application
	 *
	 * @since   5.0.0
	 */
	public function __construct(CMSApplication $application)
	{
		$this->application = $application;
	}

	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 *
	 * @return  string  The HTML markup for the create item link
	 *
	 * @since  5.0.0
	 */
	public static function create($category, $params, $attribs = array())
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_mymuses&task=mymuse.add&return=' . base64_encode($uri) . '&id=0&catid=' . $category->id;

		$text = LayoutHelper::render('joomla.content.icons.create', array('params' => $params, 'legacy' => false));

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] .= ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		$output = '<span class="hasTooltip" title="' . HTMLHelper::_('tooltipText', 'COM_MYMUSE_CREATE_FOO') . '">' . $button . '</span>';

		return $output;
	}

	/**
	 * Display an edit icon for the mymuse.
	 *
	 * This icon will not display in a popup window, nor if the mymuse is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object    $mymuse  The mymuse information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string   The HTML for the mymuse edit icon.
	 *
	 * @since   5.0.0
	 */
	public static function edit($mymuse, $params, $attribs = array(), $legacy = false)
	{
		$user = Factory::getApplication()->getIdentity();
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return '';
		}

		// Ignore if the state is negative (trashed).
		if ($mymuse->published < 0)
		{
			return '';
		}

		// Set the link class
		$attribs['class'] = 'dropdown-item';

		// Show checked_out icon if the mymuse is checked out by a different user
		if (property_exists($mymuse, 'checked_out')
			&& property_exists($mymuse, 'checked_out_time')
			&& $mymuse->checked_out > 0
			&& $mymuse->checked_out != $user->get('id'))
		{
			$checkoutUser = Factory::getApplication()->getIdentity($mymuse->checked_out);
			$date         = HTMLHelper::_('date', $mymuse->checked_out_time);
			$tooltip      = Text::_('JLIB_HTML_CHECKED_OUT') . ' :: ' . Text::sprintf('COM_MYMUSE_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br /> ' . $date;

			$text = LayoutHelper::render('joomla.content.icons.edit_lock', array('tooltip' => $tooltip, 'legacy' => $legacy));

			$output = HTMLHelper::_('link', '#', $text, $attribs);

			return $output;
		}

		if (!isset($mymuse->slug))
		{
			$mymuse->slug = "";
		}

		$mymuseUrl = RouteHelper::getFooRoute($mymuse->slug, $mymuse->catid, $mymuse->language);
		$url        = $mymuseUrl . '&task=mymuse.edit&id=' . $mymuse->id . '&return=' . base64_encode($uri);

		if ($mymuse->published == 0)
		{
			$overlib = Text::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = Text::_('JPUBLISHED');
		}

		if (!isset($mymuse->created))
		{
			$date = HTMLHelper::_('date', 'now');
		}
		else
		{
			$date = HTMLHelper::_('date', $mymuse->created);
		}

		if (!isset($created_by_alias) && !isset($mymuse->created_by))
		{
			$author = '';
		}
		else
		{
			$author = $mymuse->created_by_alias ?: Factory::getApplication()->getIdentity($mymuse->created_by)->name;
		}

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= Text::sprintf('COM_MYMUSE_WRITTEN_BY', htmlspecialchars($author, ENT_COMPAT, 'UTF-8'));

		$icon = $mymuse->published ? 'edit' : 'eye-slash';

		if (strtotime($mymuse->publish_up) > strtotime(Factory::getDate())
			|| ((strtotime($mymuse->publish_down) < strtotime(Factory::getDate())) && $mymuse->publish_down != Factory::getDbo()->getNullDate()))
		{
			$icon = 'eye-slash';
		}

		$text = '<span class="hasTooltip fa fa-' . $icon . '" title="'
			. HTMLHelper::tooltipText(Text::_('COM_MYMUSE_EDIT_FOO'), $overlib, 0, 0) . '"></span> ';
		$text .= Text::_('JGLOBAL_EDIT');

		$attribs['title'] = Text::_('COM_MYMUSE_EDIT_FOO');
		$output           = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $output;
	}
}