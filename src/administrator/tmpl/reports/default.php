<?php
/**
 * @version     $Id$
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
$orders_summary = isset($this->orders_summary)? $this->orders_summary : array();
$rows =& $this->items_summary;



	?>

<h2><?php echo Text::_('Filter'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_mymuse&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
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
			<div class="js-stools-field-filter col-md-2">
				<div class="control-label">
					<?php echo $this->form->getLabel('filter_show_format'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('filter_show_format'); ?>
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
<br />	
<?php if($orders_total >0){ ?>
		<h2><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY'); ?></h2>
		<b><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY_EXPLANATION'); ?></b><br />
		<style>
			#orderLinks {
				display: none;
			}
		</style>
		<script>
			jQuery(document).ready(function($)
			{
			$('#toggleOrders').on('click', function(){

        		$('#orderLinks').toggle();

    		});
		});
		</script>
		<a id="toggleOrders"><?php echo Text::_( 'COM_MYMUSE_HIDE_SHOW' ); ?></a>
		<div id="orderLinks">
			<?php echo $this->orderlinks; ?>
		</div>

		<table class="paramlist admintable">
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( 'COM_MYMUSE_TOTAL_NO_ORDERS' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_total;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( 'COM_MYMUSE_TOTAL_SUBTOTAL' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_subtotal;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( 'COM_MYMUSE_TOTAL_SHIPPING' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_shipping;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( 'COM_MYMUSE_COUPON_DISCOUNT' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_coupon_discount;?></td>
		</tr>
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( 'COM_MYMUSE_TOTAL_DISCOUNTS' ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->total_discount;?></td>
		</tr>
	<?php foreach($orders_summary->tax_array as $tax){ ?>
		<tr>
			<td width="200" class="paramlist_key"><?php echo Text::_( $tax ); ?>:</td>
			<td width="100" class="paramlist_value"><?php echo $orders_summary->$tax;?></td>
		</tr>
	<?php } ?>	
		</table>
<?php } ?>

<br />	
<br />	
<?php if(isset($rows) && is_countable( $rows )){ ?>
		<h2><?php echo Text::_('COM_MYMUSE_ITEMS_SUMMARY'); ?></h2>
		
		<table id="articleList" class="table table-striped">
		<thead>
			<tr>
				<th class="w1">
					<?php echo Text::_('COM_MYMUSE_PRODUCT_ID'); ?>
				</th>
				<th class="w1">
					<?php echo Text::_('COM_MYMUSE_PARENT_LABEL'); ?>
				</th>
				<th class="w1">
					<?php echo Text::_('COM_MYMUSE_TITLE_ITEMS'); ?>
				</th>
				<?php if($this->show_format){ ?>
					<th class="w5">
						<?php echo Text::_('COM_MYMUSE_FORMAT'); ?>
					</th>
				<?php } ?>
				<th class="title">
					<?php echo Text::_('COM_MYMUSE_ARTIST'); ?>
				</th>
				<th class="w1">
					<?php echo Text::_('COM_MYMUSE_CATEGORY'); ?>
				</th>
				<th class="w-1 text-right">
					<?php echo Text::_('COM_MYMUSE_NUMBER_SOLD'); ?>
				</th>
				<th class="w-1 text-right">
					<?php echo Text::_('COM_MYMUSE_PRICE'); ?>
				</th>
				<th class="w-1 text-right">
					<?php echo Text::_('COM_MYMUSE_TOTAL_SALES'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php
		$k = 0;
		$total = 0.00;
		for ( $i=0, $n=count( $rows ); $i < $n; $i++ ) {
			$row =& $rows[$i];
			$row->file_format = '';
			$digital = '';
			if(isset($row->digital) && $row->digital){
				$digital = json_decode($row->digital);
				$row->file_format = $digital->file_format;

			}
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td> <?php echo $row->product_id; ?> </td>
				<td>
					<?php if($row->parent_id){ echo $row->parent_id; }?>
				</td>
				<td>
					<?php 
					if($row->parent_title){
							echo $row->parent_title." : ";
					}else{
							echo '';
					}
						?> <?php echo $row->product_name; ?>
				</td>

				<?php if($this->show_format){  ?>
					<td>
						<?php echo $row->file_format ?>
					</td>

				<?php } ?>
				<td>
					<?php echo $row->artist_name; ?>
				</td>
				<td>
					<?php echo $row->cat_name; ?>
				</td>
				<td id="quantity<?php echo $k; ?>" class="text-right">
					<?php echo $row->quantity; ?>
				</td>
				<td id="price<?php echo $k; ?>" class="text-right">
					<?php echo $row->price; ?>
				</td>
				<td id="total<?php echo $k; ?>" class="text-right">
					<?php echo MyMuseHelper::printMoney($row->total); ?>
				</td>

				<?php
				$k = 1 - $k;
				$total += $row->total;
				?>
			</tr>
			<?php
		}
		?>
			<tr>
				<td colspan="5" align="right"><?php echo Text::_('COM_MYMUSE_TOTAL'); ?></td>
				<td align="right"><?php echo MymuseHelper::printMoney($total); ?></td>
			</tr>
		</tbody>
		</table>
<?php } ?>

</div>
</div>