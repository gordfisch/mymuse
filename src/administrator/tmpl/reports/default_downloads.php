<?php
/**
 * @version     $$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$lists =& $this->lists;
$orders_total = isset($this->orders_summary->total_orders)? $this->orders_summary->total_orders : 0;
$rows =& $this->downloads;
?>
<h2><?php echo Text::_('Filter'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_mymuse&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="downloads" />
	<div class="row">

			<div class="js-stools-field-filter col-md-3">
				<div class="control-label">
					<?php echo $this->form->getLabel('catid'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('catid'); ?>
				</div>
				
			</div>
	

			<div class="js-stools-field-filter col-md-2">
				<div class="control-label">
					<?php echo $this->form->getLabel('filter_order_status'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('filter_order_status'); ?>
				</div>
				
			</div>
	


			<div class="js-stools-field-filter col-md-2">
				<div class="control-label">
					<?php echo $this->form->getLabel('filter_start_date'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('filter_start_date'); ?>
				</div>


			</div>

			<div class="js-stools-field-filter col-md-2">
				<div class="control-label">
					<?php echo $this->form->getLabel('filter_end_date'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('filter_end_date'); ?>
				</div>

			</div>

			<div class="js-stools-field-filter col-md-1">
				<div class="control-label">
					<?php echo Text::_('JSUBMIT'); ?>
				</div>
				<div class="controls">
					<button type="submit" class="filter-search-bar__button btn btn-primary" aria-label="Search">
            <span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>
        </button>
				</div>
				
			</div>
		</div>

		
		<?php echo HTMLHelper::_( 'form.token' ); ?>
		</form>
		<br />
		<h2><?php echo Text::_('COM_MYMUSE_DOWNLOADS_REPORT'); ?></h2>

		
		<table id="articleList" class="table table-striped">
	
		<thead>
			<tr>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_ID'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_USER_ID'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_USER_NAME'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_CONTACT_EMAIL_LABEL'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_DATE'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_PRODUCT_ID'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_TITLE_PRODUCT'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_TRACK_ID'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_FORMAT_ID'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_TITLE_TRACK'); ?>
				</th>
				<th scope="col" class="w-1 ">
					<?php echo Text::_('COM_MYMUSE_UPLOADER_FILENAME'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if(count( $rows )){ ?>
		<?php
		$k = 0;
		$total = 0.00;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row =& $rows[$i];
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->id; ?></td>
				<td>
					 <a href="index.php?option=com_users&view=user&layout=edit&id=<?php echo $row->user_id; ?>"><?php echo $row->user_id; ?></a>
				</td>
				<td>
					<?php echo $row->user_name; ?>
				</td>
				<td>
					<?php echo $row->user_email; ?>
				</td>
				<td>
					<?php echo $row->date; ?>
				</td>
				<td>
					<a href="index.php?option=com_mymuse&view=product&layout=edit&id=<?php echo $row->parentid; ?>"><?php echo $row->parentid; ?></a> 
				</td>
				<td>
					<a href="index.php?option=com_mymuse&view=product&layout=edit&id=<?php echo $row->parentid; ?>"><?php echo $row->parent; ?></a> 
				</td>
				<td>
					<a href="index.php?option=com_mymuse&task=product.editfile&type=file&id=<?php echo $row->track_parentid; ?>&parentid=<?php echo $row->parentid; ?>"><?php echo $row->track_parentid; ?></a> 
				</td>
				<td>
					<a href="index.php?option=com_mymuse&task=product.editfile&type=file&id=<?php echo $row->track_parentid; ?>&parentid=<?php echo $row->parentid; ?>"><?php echo $row->product_id ?></a>
				</td>
				<td>
					<a href="index.php?option=com_mymuse&task=product.editfile&type=file&id=<?php echo $row->track_parentid; ?>&parentid=<?php echo $row->parentid; ?>"><?php echo $row->track_parent; ?></a> 
				</td>
				
				<td>
					<?php echo $row->product_filename; ?>
				</td>

				<?php
				$k = 1 - $k;
				?>
			</tr>
			<?php
		}
		?>
		<?php }else{ 
	echo Text::_('COM_MYMUSE_NOTHING_TO_REPORT');
} ?>
		</tbody>
		</table>

</div>