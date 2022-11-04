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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\Component\Mymuse\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Mymuse\Administrator\Service\HTML\Icon;
use Psr\Container\ContainerInterface;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Factory;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseStorage;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * Component class for com_mymuse
 *
 * @since  5.0.0
 */
class MymuseComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface, AssociationServiceInterface, RouterServiceInterface
{
	use CategoryServiceTrait;
	use AssociationServiceTrait;
	use HTMLRegistryAwareTrait;
	use RouterServiceTrait;

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_NAMES = [
		self::CONDITION_PUBLISHED   => 'JPUBLISHED',
		self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
		self::CONDITION_ARCHIVED    => 'JARCHIVED',
		self::CONDITION_TRASHED     => 'JTRASHED',
	];

	/**
	 * The archived condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_ARCHIVED = 2;

	/**
	 * The published condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_PUBLISHED = 1;

	/**
	 * The unpublished condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_UNPUBLISHED = 0;

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	const CONDITION_TRASHED = -2;

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
		$this->getRegistry()->register('mymuseicon', new Icon($container->get(SiteApplication::class)));

		/*
		$res = Factory::getApplication()->triggerEvent('onMymuseGetStorage', array('com_mymuse'));
		if(isset($res[0]) && is_object($res[0])){
			$GLOBALS['mymuseStorage'] = $res[0];
		}else{
			//no plugin. load the default storage class
			$GLOBALS['mymuseStorage'] = new MymuseStorage();
		}
		MyMuseHelper::setParam('storage', $GLOBALS['mymuseStorage']->type);
		*/
        MymuseHelper::setParam('storage', 'regular');

        
        $Doc = Factory::getDocument();
        $Doc->addStyleSheet( Uri::base() . 'components/com_mymuse/assets/css/mymuse.css' );


        $session  = Factory::getSession();
        if($must_updrade =  $session->get('com_mymuse.convertTo4', false)){
			echo "<h1>SESSION SET MUST UPGRADE</h1>";
		}

        $v3File = JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'manifest.xml';

        if(file_exists($v3File)){

        	echo '<a href="index.php?option=com_mymuse&task=product.update">MUST UPGRADE DB!!!!!!</a>';
        
        }

	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   5.0.0
	 */
	public function countItems(array $items, string $section)
	{
		try
		{
			$config = (object) array(
				'related_tbl'   => $this->getTableNameForSection($section),
				'state_col'     => 'published',
				'group_col'     => 'catid',
				'relation_type' => 'category_or_group',
			);

			ContentHelper::countRelations($items, $config);
		}
		catch (\Exception $e)
		{
			// Ignore it
		}
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   5.0.0
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return ($section === 'category' ? 'categories' : 'mymuse_product');

	}

	/**
	 * Returns the state column for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   5.0.0
	 */
	protected function getStateColumnForSection(string $section = null)
	{
		return 'published';
	}
}