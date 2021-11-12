<?php
/**
 * @version		$$
 * @package		mymuse2.5
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$item = $this->item;
$params = $this->params;



JFilterOutput::objectHTMLSafe( $item );

?>
<!-- product edit_allfiles.php -->


		<form action="index.php" method="post" name="adminForm" id="adminForm">
<h2><?php echo $item->parent->title; ?>: <?php echo JText::_("COM_MYMUSE_ALL_FILES"); ?></h2>
<div class="edittracks row">
	<div class="col-6 col-lg-6">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('alias'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_sku'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_sku'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('ordering'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('ordering'); ?>
				</div>
			</div>
			
	<?php if(!$this->params->get('my_price_by_product')){ ?>			
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>
	<?php } ?>		
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_discount'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_time'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('file_time'); ?>
				</div>
			</div>

	</div>			
	<div class="col-6 col-lg-6">

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('language'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('language'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('id'); ?>
				</div>
			</div>
	

	</div>
</div>
<div class="col-12 col-lg-12">
		<fieldset class="adminform">

			<legend></legend>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('articletext'); ?>

		</fieldset>
    </div>
<div class="clr"></div>
		<input type="hidden" name="product_allfiles" value="1">
		<input type="hidden" name="jform[product_allfiles]" value="1">
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parent->id ?>" />
		<input type="hidden" name="parentid" value="<?php echo $item->parent->id ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="jform[artistid]" value="<?php echo $item->parent->artistid ?>" />
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="jform[product_downloadable]" value="1" />
		<input type="hidden" name="subtype" value="allfiles" />

		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		