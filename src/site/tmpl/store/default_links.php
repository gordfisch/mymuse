<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;

// no direct access
defined('_JEXEC') or die;

$catlink    = $this->params->get('my_product_link', 'catid');
?>
<h3><?php echo Text::_('MYMUSE_MORE_PRODUCTS'); ?></h3>

<ol>
<?php foreach ($this->link_items as &$item) : ?>
	<li>
		<a href="<?php echo Route::_(RouteHelper::getProductRoute($item->id, $item->$catlink)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ol>
