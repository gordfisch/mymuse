<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$params = json_decode($this->parent->params);


?>

<div class="categories-list<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<h2><?php echo $this->parent->title; ?></h2>

<?php if ($this->params->get('show_base_description')) : ?>
	
	<?php if (isset($params->image)): ?>
	<div class="list_image">
	<img src="<?php echo  $params->image; ?>" 
	alt="<?php echo htmlspecialchars($params->image); ?>" border="0" /></div>


	<?php endif; ?>
		<?php if($this->params->get('categories_description')) : ?>
			<?php echo  HTMLHelper::_('content.prepare', $this->params->get('categories_description'), '', 'com_content.categories'); ?>
		<?php  else: ?>
			<?php //Otherwise get one from the database if it exists. ?>
			<?php  if ($this->parent->description) : ?>
				<div class="category-desc">
					<?php  echo HTMLHelper::_('content.prepare', $this->parent->description, '', 'com_content.categories'); ?>
				</div>
			<?php  endif; ?>
		<?php  endif; ?>
	<?php endif; ?>



<?php
echo $this->loadTemplate('items');
?>
</div>
