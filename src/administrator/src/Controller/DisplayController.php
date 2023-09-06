<?php
/**

 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseUpdate;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * MyMuse master display controller.
 *
 * @since  5.0.0
 */
class DisplayController extends BaseController
{
	/**
		 * The default view.
		 *
		 * @var    string
		 * @since  1.0.0
		 */
		protected $default_view = 'mymuse';
		
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  BaseController|bool  This object to support chaining.
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception
	 */
	public function display($cachable = false, $urlparams = array())
	{

        $app  = Factory::getApplication();
		if($app->getUserState('com_mymuse.convertTo4', false)){
			$input = $app->input;
			$input->set('view','products');
			$input->set('task','update');
		}



		return parent::display();
	}

	function addSampleData()
	{

		$helper = new MyMuseUpdate;
		if(!$helper->addSampleData()){
			$this->msg = "ERROR: ". $helper->error;
		
		}else{	
			$this->msg = "Sample Products Added";
		}
		//redirect to product
		$this->setRedirect( 'index.php?option=com_mymuse&view=products', $this->msg);
	}



	
	/**
	 * Method to add genres
	 *
	 * @access    public
	 */
	function addGenres()
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'update.php');
		$db = JFactory::getDBO();
	
		$query = "SELECT id from #__categories WHERE title='Genres'";
		$db->setQuery($query);
		$parent_id = $db->loadResult();
	
		if(!$parent_id){
			$this->msg = Text::_("MYMUSE_CREATE_GENRES_CATEGORY");
			$this->setRedirect( 'index.php?option=com_mymuse', $this->msg);
			return false;
		}
	
		$genres = array(
				'Avant Garde',
				'Blues',
				'Classical',
				'Country',
				'Easy Listening',
				'Electronic',
				'Folk',
				'Hip-Hop/Rap',
				'Holiday',
				'Jazz',
				'Kids/Family',
				'Latin',
				'Metal/Punk',
				'Moods',
				'New Age',
				'Pop',
				'Raggae',
				'Rock',
				'Spiritual',
				'Spoken Word',
				'Urban/R&B',
				'World'
	
		)
		?>
<table width="700">
	<tr>
		<td valign="top" width="200"><img
			src="components/com_mymuse/assets/images/logo325.jpg"></td>
		<td>

			<ul>
				<?php
	
				$i = 0;
				foreach($genres as $genre){
					$i++;
					echo "<li><strong>".Text::_("Creating genre: ")."$genre</strong><br />";
					$res = MyMuseUpdateHelper::makeCategory($genre, $parent_id);
					if(!$res){
						echo Text::_("Problem with creating category: $genre ");
					}else{
						echo Text::_("Created Catalog Category '$genre'");
						$catalog_cat_id = $db->insertid();
					}
					echo "<br />";
					echo "</li>
					";
				}
			
		
				echo "
				</ul>
				</td>
				</tr>
				</table>
				";
		    }
		
	function addEuroTax()
	{
		$msg = '';
		$db = Factory::getDBO();
		$tax_names = array('VAT__AT_','VAT__BE_','VAT__BG_','VAT__CY_','VAT__CZ_','VAT__HR_',
		'VAT__DK_','VAT__EE_','VAT__FI_','VAT__FR_','VAT__DE_','VAT__GR_','VAT__HU_','VAT__IE_',
		'VAT__IT_','VAT__LV_','VAT__LT_','VAT__LU_','VAT__MT_','VAT__NL_','VAT__PL_','VAT__PT_','VAT__RO_',
		'VAT__SK_','VAT__SI_','VAT__ES_','VAT__SE_','VAT_Exempt');
		
		foreach($tax_names as $name){
			$sub_query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00';";
	
			$db->setQuery($sub_query);
			if($db->execute()){
				$msg = "Added Euro Zone Taxes to Order Table. <br />";
			}else{
				$msg = "Error Adding Euro Zone Taxes to Order Table";
				$this->setRedirect( 'index.php?option=com_mymuse&view=taxrates', $msg);
				return false;
			}
		}


		$query = "
INSERT INTO `#__mymuse_tax_rate` ( `province`, `country`, `tax_rate`, `tax_applies_to`, `tax_name`, `tax_format`, `compounded`, `ordering`, `checked_out`, `checked_out_time`) VALUES

('', 'AUT', 0.2000, 'C', 'VAT (AT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'BEL', 0.2100, 'C', 'VAT (BE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'BGR', 0.2000, 'C', 'VAT (BG)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'CYP', 0.1900, 'C', 'VAT (CY)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'CZE', 0.2100, 'C', 'VAT (CZ)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'HRV', 0.2500, 'C', 'VAT (HR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'DNK', 0.2500, 'C', 'VAT (DK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'EST', 0.2000, 'C', 'VAT (EE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'FIN', 0.2400, 'C', 'VAT (FI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'FRA', 0.2000, 'C', 'VAT (FR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'DEU', 0.1900, 'C', 'VAT (DE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'GRC', 0.2400, 'C', 'VAT (GR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'HUN', 0.2700, 'C', 'VAT (HU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'IRL', 0.2300, 'C', 'VAT (IE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'ITA', 0.2200, 'C', 'VAT (IT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'LVA', 0.2100, 'C', 'VAT (LT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'LTU', 0.2100, 'C', 'VAT (LT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'LUX', 0.1600, 'C', 'VAT (LU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'MLT', 0.1800, 'C', 'VAT (MT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'NLD', 0.2100, 'C', 'VAT (NL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'NOR', 0.2500, 'C', 'VAT (NL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'POL', 0.2300, 'C', 'VAT (PL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'PRT', 0.2300, 'C', 'VAT (PT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'ROM', 0.1900, 'C', 'VAT (RO)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'SVK', 0.2000, 'C', 'VAT (SK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'SVN', 0.2200, 'C', 'VAT (SI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'ESP', 0.2100, 'C', 'VAT (ES)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'SWE', 0.2500, 'C', 'VAT (SE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
('', 'EUU', 0.0000, 'C', 'VAT Exempt', 'RATE', '0', 1, 0, '0000-00-00 00:00:00');
	 		";

		
		$db->setQuery($query);
		if($db->execute()){
			$msg .= "Added Euro Zone Taxes to Tax Rate Table";
		}else{
			$msg .= "Error Adding Euro Zone Taxes to Tax Rate Table";
		}
		$this->setRedirect( 'index.php?option=com_mymuse&view=taxrates', $msg);
		return true;
	}
}