<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fmymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Administrator\Extension;
defined('JPATH_PLATFORM') or die;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Mymuse\Administrator\Service\HTML\AdministratorService;
use Psr\Container\ContainerInterface;
/**
 * Component class for com_foos
 *
 * @since  5.0.0
 */
class MymuseComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface
{
	use CategoryServiceTrait;
	use HTMLRegistryAwareTrait;
	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   5.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('mymuseadministrator', new AdministratorService);
	}
}