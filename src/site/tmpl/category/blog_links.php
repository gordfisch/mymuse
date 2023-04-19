<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
$catlink = $this->params->get('my_product_link', 'catid');
?>

<ol class="com-content-blog__links">
    <?php foreach ($this->link_items as $item) : ?>
        <li class="com-content-blog__link">
            <a href="<?php echo Route::_(RouteHelper::getProductRoute($item->slug, $item->$catlink, $item->language)); ?>">
                <?php echo $item->title; ?></a>
        </li>
    <?php endforeach; ?>
</ol>
