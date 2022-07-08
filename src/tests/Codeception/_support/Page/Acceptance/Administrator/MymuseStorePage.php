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
 * Acceptance Page object class for store manager file page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class MymuseStorePage extends AdminFormPage
{
	/**
	 * Url to store manager file page.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $url = "administrator/index.php?option=com_mymuse&view=store&layout=edit&id=1";

    /**
     * Locator for the Store Description Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $generalTab = ['xpath' => '//*[@id="myTab"]/div/button[1]'];

    /**
     * Locator for the Store Css Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $cssTab = ['xpath' => '//*[@id="myTab"]/div/button[2]'];

    /**
     * Locator for the Store contact Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $contactTab = ['xpath' => '//*[@id="myTab"]/div/button[3]'];

    /**
     * Locator for the Store Downloads Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $downloadsTab = ['xpath' => '//*[@id="myTab"]/div/button[4]'];

    /**
     * Locator for the Store physical Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $physicalTab = ['xpath' => '//*[@id="myTab"]/div/button[5]'];

    /**
     * Locator for the Store Store Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $storeTab = ['xpath' => '//*[@id="myTab"]/div/button[6]'];

    /**
     * Locator for the Store Reports Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $reportsTab = ['xpath' => '//*[@id="myTab"]/div/button[7]'];

    /**
     * Locator for the Store testing Button
     *
     * @var    string
     * @since  4.0.0
     */
    public static $testingTab = ['xpath' => '//*[@id="myTab"]/div/button[8]'];
    
}
