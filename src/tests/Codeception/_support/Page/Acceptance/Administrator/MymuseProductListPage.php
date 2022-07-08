<?php
/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class for product list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class MymuseProductListPage extends AdminListPage
{
	/**
	 * Link to the product listing page.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $url = "/administrator/index.php?option=com_mymuse&view=products";

	/**
	 * Drop Down Toggle Element.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $dropDownToggle = ['xpath' => "//button[contains(@class, 'dropdown-toggle')]"];

	/**
	 * Page object for content body editor field.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $content = ['id' => 'jform_producttext'];

	/**
	 * Page object for the toggle button.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $toggleEditor = "Toggle editor";

	/**
	 * Locator for product's name field.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $seeName = ['xpath' => "//table[@id='productList']//tr[1]//td[4]"];

	/**
	 * Locator for product's featured icon.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $seeFeatured = ['xpath' => "//table[@id='productList']//*//span[@class='icon-star']"];

	/**
	 * Locator for product's name field.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $seeAccessLevel = ['xpath' => "//table[@id='productList']//tr[1]//td[5]"];

	/**
	 * Locator for product's unpublish icon.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $seeUnpublished = ['xpath' => "//table[@id='productList']//*//span[@class='icon-unpublish']"];

	public static $productTitleField = [ 'id' => "jform_title" ];

	public static $productAliasField = [ 'id' => "jform_alias" ];

/*
        $this->mock_cd->jform_product_in_stock                  = "5";
        $this->mock_cd->jform_price                             = "20.00";
        $this->mock_cd->jform_artist                            = "- - Iron Brew";
        $this->mock_cd->jform_cat                               = "- - World Beat";
        $this->mock_cd->jform_product_sku                       = "IronBrew01-CD";
        $this->mock_cd->jform_product_physical                  = "Yes";
        $this->mock_cd->jform_list_image                        = "images/mymuse/sister.jpg";
        $this->mock_cd->jform_detail_image                      = "images/mymuse/sister.jpg";
        $this->mock_cd->jform_product_made_date                 = "2018-11-28";
        $this->mock_cd->jform_product_full_time                 = "45:10";
        $this->mock_cd->jform_product_publisher                 = "Iron Filings";
        $this->mock_cd->jform_product_producer                  = "Gord Fisch";
        $this->mock_cd->jform_product_country                   = "38"; 
        $this->mock_cd->jform_product_studio                    = "Tanglewood";
        $this->mock_cd->jform_product_weight                    = ".2";
        $this->mock_cd->jform_product_length                    = ".6";
        $this->mock_cd->jform_product_width                     = "6";
        $this->mock_cd->jform_product_height                    = ".5";
        $this->mock_cd->jform_attribs['media_rls']              = "";
        $this->mock_cd->jform_attribs['media_link']             = "";
        $this->mock_cd->jform_producttext                       = '<p>The great first album</p>';
*/


	public static $productSearchField = [ 'id' => "filter_search" ];

	public static $searchButton = [ 'xpath' => "//button[@aria-label='Search']" ];

	public static $systemMessageAlertClose = ['class' => "joomla-alert--close"];
}
