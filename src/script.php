<?php
/**

 * @package     Joomla.Administrator
 * @subpackage  com_fmymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;


defined('_JEXEC') or die('Restricted access');
/**
 * Script file of MyMuse component
 *
 * @since  5.0.0
 */
class Com_MymuseInstallerScript
{
    /**
     * Minimum Joomla version to check
     *
     * @var    string
     * @since  5.0.0
     */
    private $minimumJoomlaVersion = '3.10.11';
    /**
     * Target Joomla version
     *
     * @var    string
     * @since  5.0.0
     */
    private $targetJoomlaVersion = '4.2.3';
    /**
     * Minimum PHP version to check
     *
     * @var    string
     * @since  5.0.q0
     */
    private $minimumPHPVersion = JOOMLA_MINIMUM_PHP;

    /**
     * db
     *
     * @var    int
     * @since  5.0.0
     */
    var $db = null;

    /**
     * Already Installed
     *
     * @var    int
     * @since  5.0.0
     */
    var $already_installed = 0;

    /**
     * Old Mymuse Version
     *
     * @var    int
     * @since  5.0.0
     */
    var $old_version = 0;

    /**
     * css file
     *
     * @var    string
     * @since  5.0.0
     */
    var $css = '';

    /**
     * Mymuse Params
     *
     * @var    string
     * @since  5.0.0
     */
    var $mymuse_params = '';

    /**
     * convertTo4
     *
     * @var    int
     * @since  5.0.0
     */
    var $convertTo4 = 0;

    /**
     * actions
     *
     * @var    int
     * @since  5.0.0
     */
    var $actions = array();

    /**
     * convert_actions
     *
     * @var    int
     * @since  5.0.0
     */
    var $convert_actions = array();

    public function __construct() {
        if(version_compare(JVERSION, '4.0.0', '<')){
            $this->db = JFactory::getDBO();
        }else{
            $this->db = Factory::getContainer()->get('DatabaseDriver');
        }
    }


   /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  1.0.0
     *
     * @throws Exception
     */
    public function preflight($type, $parent): bool
    {
        $session  = Factory::getSession();
        $session->set('com_mymuse.convertTo4', false);


        if ($type !== 'uninstall')
        {
            try
            {
                $minJoomla = '3.10.0';
                $minPHP = '7.4.0';

                $jversion = new JVersion();
                if (!$jversion->isCompatible($this->minimumJoomlaVersion))
                {
                    throw new Exception(sprintf('Please upgrade to at least Joomla! %s before continuing!', $this->minimumJoomlaVersion));
                }

                if (version_compare(PHP_VERSION, $this->minimumPHPVersion, '<'))
                {
                    throw new Exception(sprintf('You have a very old PHP version and RSFirewall! requires at least %s; please ask your hosting provider to upgrade to a newer version of PHP.', $this->minimumPHPVersion));
                }
            }
            catch (Exception $e)
            {
                JFactory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }
        }



        $query = "SELECT * from #__extensions WHERE element = 'com_mymuse' ";

        $this->db->setQuery($query);
        if($res = $this->db->loadObject()){
            $this->already_installed = 1;
            $manifest = json_decode($res->manifest_cache);
            $this->old_version = $manifest ->version;

            if ( version_compare( $this->old_version, '5.0', '<' )) {  
                $this->convertTo4 = 1;
                $session  = Factory::getSession();
                $session->set('com_mymuse.convertTo4', true);
            }
            // get the current css file
            if(file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mymuse.css')){
                $this->css = file_get_contents(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mymuse.css');
            }
            $myFile = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mymuse_old.css';
            
            $name = Text::_('COM_MYMUSE_SAVE_CSS');
            if($this->css != ""){
                if(!FILE::write($myFile, $this->css)){
                    $alt = Text::_( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message =  Text::_("COM_MYMUSE_SAVE_CSS_FAILED");
                }else{
                    $alt = Text::_( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message =  Text::_("COM_MYMUSE_SAVE_CSS_SUCCESS");
                }
                $this->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
            }
            $parent->already_installed = $this->already_installed;
            $parent->old_version = $this->old_version;

        }
        

        if($this->convertTo4 == 1){

            echo "<h3>Convert to Mymuse 5 for Joomla 4</h3>";
            echo "<p>Old MyMuse version = ". $this->old_version  ."</p>";
            $plugins = array();
            $modules = array();
            $manifest = $parent->getManifest();
            $super = $parent->getParent();

            /* remove plugins */
            
/*
            $query = "SELECT * FROM `#__extensions` WHERE 
            (
                `name` LIKE '%installer_mymuse%' 
                OR `name` LIKE '%mymuse_socialshare%' 
                OR `name` LIKE '%PLG_MYMUSE_MYMUSE_VOTE%' 
                OR `name` LIKE '%plg_mymuse_shoppergroup_view%' 
                OR `name` LIKE '%PLG_MYMUSE_AUDIO_JPLAYER%' 
                OR `name` LIKE '%Installer - MyMuse%'
                OR `name` LIKE '%plg_mymuse_shipping_usps%'
                OR `name` LIKE '%plg_mymuse_vote%'
                OR `name` LIKE '%plg_user_mymuse%'
            )
            AND `type` = 'plugin'"; 
*/

            $query = "SELECT * FROM `#__extensions` WHERE 
            (
                `name` LIKE '%mymuse%' OR `name` LIKE 'plg_user_redirectonlogin'
            )
            AND `type` = 'plugin'";



            $this->db->setQuery($query);
            if($res = $this->db->loadObjectList())
            {
                foreach ($res as $plugin) {
                    $plugins[] = array(
                        'id' => $plugin->extension_id,
                        'name' => (string) $plugin->name,
                        'type' => (string) 'plugin',
                        'folder' => $super->getPath('source').'/'.(string) $plugin->folder,
                        'installer' => new JInstaller,
                        'status' => '<span style="color: red;">false</span>');

                }
            }
            if(count($plugins)){
                for ($i = 0; $i < count($plugins); $i++) {
                    $plugin =& $plugins[$i];
                    //echo $plugin['folder'].'<br />';
                    if ($plugins[$i]['installer']->uninstall('plugin', $plugins[$i]['id'])) {
                        $plugins[$i]['status'] = true;
                    }
                    $this->convert_actions [] = array (
                        'name' => "Removing old Plugin: ",
                        'message' => $plugins[$i]['id']." ".$plugins[$i]['name'],
                        'status' => $plugins[$i]['status']
                    );
   

                }
            }

            /* remove modules */
            $query = "SELECT * FROM `#__extensions` WHERE `name` LIKE '%mymuse%' AND `type` = 'module' ORDER BY `extension_id` ASC";
            $this->db->setQuery($query);
            if($res = $this->db->loadObjectList())
            {
                foreach ($res as $module) {
                    $modules[] = array(
                        'id' => $module->extension_id,
                        'name' => (string) $module->name,
                        'type' => (string) 'module',
                        'installer' => new JInstaller,
                        'status' => false);

                }
            }
            if(count($modules)){
                for ($i = 0; $i < count($modules); $i++) {
                    $module =& $modules[$i];
                    if ($modules[$i]['installer']->uninstall('module', $modules[$i]['id'])) {
                        $modules[$i]['status'] = true;
                    }
                  
                    $this->convert_actions [] = array (
                        'name' => "Removing old Module: ",
                        'message' => $modules[$i]['id']." ".$modules[$i]['name'],
                        'status' => $modules[$i]['status']
                    );
                }
            }
            //remove update sites
            $query = 'DELETE FROM #__update_sites WHERE name LIKE "%mymuse%" ';
            $this->db->setQuery($query);
            try
            {
                $this->db->execute();
                $status = true;
            }
            catch (\Exception $e)
            {
                $query = $e->getMessage(). ' '.$query;
                $status = false;
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }




            //add this in preflight
            $query = "SHOW COLUMNS FROM #__mymuse_format LIKE 'id'";
            $this->db->setQuery($query);
            try 
            {
                $this->db->loadObject();
            }
            catch (\Exception $e)
            {
                $query = "
                    CREATE TABLE `#__mymuse_format` (
                      `id` int NOT NULL,
                      `format_key` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `format_value` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `ordering` int NOT NULL DEFAULT '0'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                    return false;
                }

                $query = "ALTER TABLE `#__mymuse_format`
                    ADD PRIMARY KEY (`id`);";
                    $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                    return false;
                }

                $query = "ALTER TABLE `#__mymuse_format`
                    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;";
                    $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                    return false;
                }


                $query = "INSERT INTO `#__mymuse_format` (`id`, `format_key`, `format_value`, `ordering`) VALUES
                    (1, 'MP3', 'mp3', 1),
                    (2, 'WAV', 'wav', 2);
                    ";
                $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                    return false;
                }
            }





            $this->convert_actions [] = array (
                        'name' => "Removing old Update Sites: ",
                        'message' => "",
                        'status' => $status
                    );
            echo "<h3>CONVERT TO JOOMLA 4</h3>";
            //update DB
            $queries = array(
                "ALTER TABLE `#__mymuse_product` DROP `urls`;",
                "ALTER TABLE `#__mymuse_product` MODIFY `parentid` int UNSIGNED NOT NULL DEFAULT '0' AFTER `asset_id`;",
                "ALTER TABLE `#__mymuse_product` ADD `track_parentid` int UNSIGNED NOT NULL DEFAULT '0' AFTER `parentid`;",
                "ALTER TABLE `#__mymuse_product` MODIFY `catid` int NOT NULL AFTER `track_parentid`;",

                "ALTER TABLE `#__mymuse_product` MODIFY `artistid` int NOT NULL AFTER `catid`;",
                "ALTER TABLE `#__mymuse_product` ADD `physical` varchar(1024) COLLATE utf8mb4_unicode_ci default NULL  COMMENT 'Registry' AFTER `artistid`;",
                "ALTER TABLE `#__mymuse_product` ADD `digital` varchar(1024) COLLATE utf8mb4_unicode_ci default NULL  COMMENT 'Registry' AFTER `physical`;",
                "ALTER TABLE `#__mymuse_product` ADD`recording` varchar(1024) COLLATE utf8mb4_unicode_ci AFTER `digital`;",

                "ALTER TABLE `#__mymuse_product` CHANGE `product_made_date` `product_release_date` date DEFAULT NULL;",

                "UPDATE `#__mymuse_product` SET `product_release_date` = NULL WHERE `product_release_date`='0000-00-00'",

                "ALTER TABLE `#__mymuse_product` ADD`updated` char(1) NOT NULL default '0';",
                "ALTER TABLE `#__mymuse_product` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY `checked_out_time` datetime DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY `attribs` varchar(1024) COLLATE utf8mb4_unicode_ci default NULL  COMMENT 'Registry';",
                "ALTER TABLE `#__mymuse_product` MODIFY `metakey` text COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `#__mymuse_product` MODIFY `metadesc` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY `metadata` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Registry.';",
                "ALTER TABLE `#__mymuse_product` MODIFY  `created` datetime NOT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY  `modified` datetime NOT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY  `publish_up` datetime DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY  `publish_down` datetime DEFAULT NULL; ", 
                "ALTER TABLE `#__mymuse_product` MODIFY `introtext` mediumtext COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `#__mymuse_product` MODIFY `fulltext` mediumtext COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `#__mymuse_product` MODIFY `product_discount` decimal(10,2) DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY `list_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_product` MODIFY `detail_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",

                
                "ALTER TABLE `#__mymuse_product` ADD `special_status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `alias`;",

                "CREATE INDEX `idx_catid` ON `#__mymuse_product` (`catid`);",
                "CREATE INDEX `idx_artistid` ON `#__mymuse_product` (`artistid`);",

                "UPDATE `#__mymuse_product` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00'",
                "UPDATE `#__mymuse_product` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_product` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",
                "ALTER TABLE `#__mymuse_product` MODIFY `product_sku` VARCHAR(254) NOT NULL;",

                "ALTER TABLE `#__mymuse_shopper_group` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_shopper_group` MODIFY `checked_out_time` datetime DEFAULT NULL;",

                

                "ALTER TABLE `#__mymuse_shopper_group`  ADD `usergroups_id` int NOT NULL DEFAULT '2';",
                "ALTER TABLE `#__mymuse_shopper_group` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_shopper_group` MODIFY `checked_out_time` datetime DEFAULT NULL;",

                "ALTER TABLE `#__mymuse_coupon` CHANGE `params` `params` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_coupon` ALTER `coupon_uses` DROP DEFAULT;",
                "ALTER TABLE `#__mymuse_coupon` ALTER `coupon_uses` SET DEFAULT '0';",
                "ALTER TABLE `#__mymuse_coupon` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_coupon` MODIFY `checked_out_time` datetime DEFAULT NULL;",

                "ALTER TABLE `#__mymuse_order` ADD `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order` CHANGE `extra` `extra` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order` CHANGE `licence` `licence` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order` MODIFY `checked_out_time` datetime DEFAULT NULL;",
                            
                "ALTER TABLE `#__mymuse_store` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_store` MODIFY `checked_out_time` datetime DEFAULT NULL;",

                "ALTER TABLE `#__mymuse_tax_rate` CHANGE `state` `published` tinyint(1) DEFAULT '0';",
                "ALTER TABLE `#__mymuse_tax_rate` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_tax_rate` MODIFY `checked_out_time` datetime DEFAULT NULL;",

                "ALTER TABLE `#__mymuse_order_item` ADD `variation_id` int default NULL;",
                "ALTER TABLE `#__mymuse_order_item`  MODIFY  `created` datetime NOT NULL;",
                "ALTER TABLE `#__mymuse_order_item`  MODIFY  `modified` datetime NOT NULL;",
                "ALTER TABLE `#__mymuse_order_item` MODIFY `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order_item` MODIFY `end_date` int DEFAULT NULL;",

                "ALTER TABLE `#__mymuse_order_shipping` MODIFY `checked_out` int UNSIGNED DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `checked_out_time` datetime DEFAULT NULL;",


                "ALTER TABLE `#__mymuse_order_shipping` DROP  `ordering`;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `created` datetime NOT NULL;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `ship_carrier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `ship_carrier_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `ship_method_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;",
                "ALTER TABLE `#__mymuse_order_shipping` MODIFY  `ship_method_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';",
                "UPDATE `#__mymuse_order_shipping` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_order_shipping` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",

                "UPDATE `#__mymuse_shopper_group` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_shopper_group` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",

                "UPDATE `#__mymuse_coupon` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_coupon` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",

                "UPDATE `#__mymuse_order` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_order` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",

                "UPDATE `#__mymuse_store` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_store` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",

                "UPDATE `#__mymuse_tax_rate` SET `checked_out` = NULL WHERE `checked_out` = '0'",
                "UPDATE `#__mymuse_tax_rate` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00'",
                "ALTER TABLE `#__mymuse_format` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;"


            );

            foreach($queries as $query){
                $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                    $status = 1;
                }
                catch (\Exception $e)
                {
                    $query = $e->getMessage(). ' '.$query;
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                    $status = 0;
                    //return false;
                }
                $this->convert_actions [] = array (
                    'name' => Text::_ ( "COM_MYMUSE_UPDATE_DB_J4" ),
                    'message' => $query,
                    'status' => $status
                );
            }

        }

 
        return true;
    }
    /**
     * Method to install the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  1.0.0
     */
    public function install($parent): bool
    {
        return true;
    }

    /**
     * Method to uninstall the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  1.0.0
     */
    public function uninstall($parent): bool
    {
        $plugins = array();
        $modules = array();
        $manifest = $parent->getManifest();
        $super = $parent->getParent();
        /* remove plugins */
        if(isset($manifest->plugins->plugin) && count($manifest->plugins->plugin)){

            foreach ($manifest->plugins->plugin as $plugin) {
                $plugins[] = array(
                    'name' => (string) $plugin,
                    'type' => (string) $plugin['name'],
                    'folder' => $super->getPath('source').'/'.(string) $plugin['folder'],
                    'installer' => new JInstaller,
                    'status' => false);

            }
        }

        for ($i = 0; $i < count($plugins); $i++) {
            $plugin =& $plugins[$i];
            $query = "SELECT extension_id FROM #__extensions
            WHERE element ='".$plugins[$i]['type']."'";
            $this->db->setQuery($query);
            $res = $this->db->loadResult();
            echo $res." ".$plugins[$i]['type']."<br />";
            if ($plugins[$i]['installer']->uninstall('plugin', $res)) {
                $plugins[$i]['status'] = true;
            }
        }

        /* remove modules */
        if(isset($manifest->modules->module) && count($manifest->modules->module)){

            foreach ($manifest->modules->module as $module) {
                $modules[] = array(
                    'name' => (string) $module,
                    'type' => (string) $module['name'],
                    'installer' => new JInstaller,
                    'status' => false);
            }
        }

        for ($i = 0; $i < count($modules); $i++) {
            $module =& $modules[$i];
            $query = "SELECT extension_id FROM #__extensions
            WHERE element ='".$modules[$i]['type']."'";
            $this->db->setQuery($query);
            $res = $this->db->loadResult();
            if($res){
                echo $res." ".$modules[$i]['type']."<br />";
                if ($modules[$i]['installer']->uninstall('module', $res)) {
                    $modules[$i]['status'] = true;
                }
            }
        }


        return true;
    }
    /**
     * Method to update the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  1.0.0
     *
     */
    public function update($parent): bool
    {
        /*echo Text::_('COM_MYMUSE_INSTALLERSCRIPT_UPDATE');*/

        $query = "SHOW COLUMNS FROM #__mymuse_format LIKE 'id'";
        $this->db->setQuery($query);
        try 
        {
            $this->db->loadObject();
        }
        catch (\Exception $e)
        {
            $query = "
                CREATE TABLE `#__mymuse_format` (
                  `id` int NOT NULL,
                  `format_key` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `format_value` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `ordering` int NOT NULL DEFAULT '0'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }

            $query = "ALTER TABLE `#__mymuse_format`
                ADD PRIMARY KEY (`id`);";
                $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }

            $query = "ALTER TABLE `#__mymuse_format`
                MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;";
                $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }

            $query = "INSERT INTO `#__mymuse_format` (`id`, `format_key`, `format_value`, `ordering`) VALUES
                (1, 'MP3', 'mp3', 1),
                (2, 'WAV', 'wav', 2);
                ";
            $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }



            $this->convert_actions [] = array (
                'name' => Text::_ ( "COM_MYMUSE_UPDATE_DB_J4" ),
                'message' => Text::_ ( "COM_MYMUSE_ADD_FORMAT_TABLE"),
                'status' => 1
            );
        }
        return true;
    }

 


    /**
     * Function called after extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  1.0.0
     *
     */
    public function postflight($type, $parent)
    {

        if ($type == 'uninstall') {
            return true;
        }

        
        //echo Text::_('COM_MYMUSE_INSTALLERSCRIPT_POSTFLIGHT');
        $app = Factory::getApplication();
        $manifest = $parent->getManifest();


        if($this->convertTo4){

            /* update store params */
            $query = "SELECT * from `#__mymuse_store` WHERE id='1'";
            $this->db->setQuery($query);
            $store = $this->db->loadObject();
            $params = new Registry($store->params);
            $old_formats = $params->get('my_formats');
            $ordering = 0;
            foreach($old_formats as $f){
                if(strtolower($f) == 'mp3'){
                    $new_formats[0] = 1;
                    $ordering++;
                }
                elseif(strtolower($f) == 'wav'){
                    $new_formats[1] = 2;
                    $ordering++;
                }
                else{
                    //add it to the table
                    $ordering++;
                    $query = 'INSERT INTO `#__mymuse_format` (`format_key`, `format_value`, ) VALUES
                    (' . strtoupper($f) . ', ' . $f . ', ' . $ordering .')';
                    $this->db->setQuery($query);
                    $this->db->execute();
                    $new_formats[] = $ordering;
                }
            }
            $params->set('my_formats',$new_formats);
            $new_store_params = $this->db->quote((string)$params);
            $query = 'UPDATE `#__mymuse_store` SET params = '.$new_store_params . ' WHERE id=1';
            $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');
                return false;
            }
            $this->convert_actions [] = array (
                'name' => Text::_ ( "COM_MYMUSE_UPDATE_DB_J4" ),
                'message' => Text::_ ( "COM_MYMUSE_UPDATE_STORE_FORMAT"),
                'status' => 1
            );

            //UPDATE PLUGINS
            $name = Text::_("COM_MYMUSE_ENABLE_PLUGINS");
            $query = "UPDATE #__extensions SET enabled=1 WHERE
                element='payment_paypal' OR
                element='payment_offline' OR
                element='shipping_standard' OR
                element='audio_amplitude' OR
                element='searchmymuse' OR
                element='mymuseuser' OR
                element='products'
                ";
            $this->db->setQuery($query);
            if(!$this->db->execute()){
                $alt = Text::_( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message =  Text::_("COM_MYMUSE_ENABLE_PLUGINS_FAILED");
            }else{
                $alt = Text::_( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message =  Text::_("COM_MYMUSE_ENABLE_PLUGINS_SUCCESS");
            }
            $this->convert_actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );

            
        } //end of convertTo4


        if($this->convertTo4 || $type == 'install'){

            //Content Types for custom fields
            $query = "SELECT type_id FROM #__content_types WHERE type_title='MyMuse Category'";
            $this->db->setQuery($query);
            if(!$res = $this->db->loadResult()) {

$query = <<<END
INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('MyMuse Category', 'com_mymuse.category', 
'{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},
"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', 
'', 
'{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 
'mymuseHelperRoute::getCategoryRoute', 
'{"formFile":"administrator/components/com_categories/models/forms/category.xml", 
"hideFielDIRECTORY_SEPARATOR":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], 
"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],
"convertToInt":["publish_up", "publish_down"], 
"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}');
END;

                $status = true;
                $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');

                    $status = false;
                }
                $this->convert_actions [] = array (
                    'name' => Text::_ ( "COM_MYMUSE_CUSTOM_FIELDS" ),
                    'message' => Text::_ ( "COM_MYMUSE_CUSTOM_FIELDS_CATEGORY"),
                    'status' => $status
                );

            }

            $query = "SELECT type_id FROM #__content_types WHERE type_title='MyMuse Product'";
            $this->db->setQuery($query);
            if(!$res = $this->db->loadResult()) {
    $query = <<<END
    INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
    ('MyMuse Product', 'com_mymuse.product', 
    '{"special":{"dbtable":"#__mymuse_product","key":"id","type":"ProductTable","prefix":"Joomla\Component\Mymuse\Administrator\Table","config":"array()"},
    "common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"\Joomla\CMS\Table","config":"array()"}}', '', 
    '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified",
    "core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", 
    "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", 
    "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "asset_id":"asset_id", "note":"note"}, 
    "special":{"fulltext":"fulltext"}}', 
    'mymuseHelperRoute::getProductRoute', 
    '{"formFile":"administrator/components/com_mymuse/forms/product.xml", 
    "hideFielDIRECTORY_SEPARATOR":["asset_id","checked_out","checked_out_time","version"],
    "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits", "ordering"],
    "convertToInt":["publish_up", "publish_down", "featured", "ordering"],"
    displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},
    {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
    {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
    {"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}');
    END;
                $status = true;
                $this->db->setQuery($query);
                try
                {
                    $this->db->execute();
                }
                catch (\Exception $e)
                {
                    Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');

                    $status = false;
                }
                $this->convert_actions [] = array (
                    'name' => Text::_ ( "COM_MYMUSE_CUSTOM_FIELDS" ),
                    'message' => Text::_ ( "COM_MYMUSE_CUSTOM_FIELDS_PRODUCT"),
                    'status' => $status
                );
            }
        }
        
        // add params for mymuse extensions **
        if($this->convertTo4 ){

            
            $defaults = '{"product_layout":"_:product","show_minicart":"1","show_title":"1","link_titles":"","product_show_product_image":"1","info_block_show":"1","info_block_show_title":"1","split_text":"1","show_readmore":"1","show_readmore_title":"1","show_artist":"1","link_artist":"0","show_category":"1","link_category":"1","show_associations":"0","show_author":"0","link_author":"0","show_date":"1","show_which_date":"product_release_date","show_hits":"0","show_tags":"0","show_product_sku":"1","show_special_status":"0","show_news_release_link":"0","show_media_link":"0","show_product_full_time":"0","show_product_studio":"0","show_product_publisher":"0","show_product_producer":"0","show_product_country":"0","product_show_quantity":"0","product_item_selectbox":"0","show_player":"1","product_show_tracks":"1","orderby_track":"order","order_track_date":"published","product_show_select_column":"1","product_show_filesize":"1","product_show_filetime":"0","product_show_downloads":"0","product_show_cost_column":"1","product_show_preview_column":"1","product_show_cartadd":"1","show_recommends":"1","show_category_recommends":"1","show_base_description":"0","categories_description":"","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"0","show_cat_num_articles_cat":"0","show_cat_subcat_image":"0","category_layout":"_:blog","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"0","show_empty_categories":"0","show_no_articles":"0","show_category_heading_title_text":"1","show_subcat_image":"0","show_subcat_desc":"0","subcat_desc_truncate":"","show_cat_num_articles":"0","show_cat_tags":"0","num_leading_products":"0","blog_class_leading":"","num_intro_products":"10","blog_class":"","num_columns":"4","multi_column_order":"1","blog_product_introtext":"1","num_links":"100","show_subcategory_content":"-1","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","category_show_product_image":"1","category_product_show_title":"1","category_link_titles":"1","category_show_intro_text":"1","category_show_readmore":"0","category_show_readmore_title":"1","readmore_limit":100,"list_show_artist":"1","list_show_album":"1","list_show_date":"0","date_format":"Y-m-d","list_show_hits":"1","list_show_price":"0","list_show_author":"1","list_show_sales":"0","list_show_discount":"0","product_artist_alternate_itemid":"4","show_featured":"show","link_intro_image":"1","orderby_pri":"order","orderby_sec":"order","order_date":"published","display_num":"10","show_pagination":"2","show_pagination_results":"1","group_by":"","custom_fields_enable":0,"sef_ids":0,"show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0","downloadid":""}'; // JSON format for the parameters
            
            /*
            $query = $this->db->getQuery(true);
            $query->update($this->db->quoteName('#__extensions'));
            $query->set($this->db->quoteName('params') . ' = ' . $defaults);
            $query->where($this->db->quoteName('name') . ' = ' . $this->db->quote('mymuse'));
            */
            $query = "UPDATE #__extensions SET params = ".$this->db->quote($defaults). "
            WHERE name = 'mymuse'";


            $this->db->setQuery($query);
            try
            {
                $this->db->execute();
            }
            catch (\Exception $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(). ' '.$query, 'error');

                return false;
            }
            $this->actions [] = array (
                'name' => Text::_ ( "COM_MYMUSE_UPDATE_DB_J4" ),
                'message' => Text::_ ( "COM_MYMUSE_UPDATE_EXTENSIONS_PARAMS"),
                'status' => 1
            );

        }


        if($type == "install" || $type == "update"){
            // init vars
            $error = false;
            $extensions = array();

            // reseting post installation session variables
            $session  = Factory::getSession();
            $session->set('mymuse.postinstall', false);
            $session->set('mymuse.allplgpublish', false);

            // additional extensions
            //first PLUG-INS PLUG-INS

            $super = $parent->getParent();

            if(count($manifest->plugins->plugin)){

                foreach ($manifest->plugins->plugin as $plugin) {

                    $extensions[] = array(
                        'name' => (string) $plugin,
                        'type' => (string) $plugin['name'],
                        'folder' => $super->getPath('source').'/'.(string) $plugin['folder'],
                        'installer' => new Installer,
                        'status' => false);

                }
            }

            //now add MODULES
            if(count($manifest->modules->module)){

                $super = $parent->getParent();
                foreach ($manifest->modules->module as $module) {

                    $extensions[] = array(
                        'name' => (string) $module,
                        'type' => (string) $module['name'],
                        'folder' => $super->getPath('source').'/'.(string) $module['folder'],
                        'installer' => new Installer,
                        'status' => false);
                }
            }



            // install additional extensions
            for ($i = 0; $i < count($extensions); $i++) {
                $error = false;
                $extension =& $extensions[$i];

                $extension['installer']->setOverwrite(true);
                if ($extension['installer']->install($extension['folder'])) {
                    $extension['status'] = true;
                }else{
                    $error =  $extension['name']. " threw an error ";
                    Factory::getApplication()->enqueueMessage($error, 'error');
                    break;
                }
            }
/*
            // rollback on installation errors
            if ($error) {
                $this->parent->abort(Text::_('Component').' '.Text::_('Install').': '.Text::_('Error'), 'component');
                for ($i = 0; $i < count($extensions); $i++) {
                    if ($extensions[$i]['status']) {
                        $extensions[$i]['installer']->abort(Text::_($extensions[$i]['type']).' '.Text::_('Install').': '.Text::_('Error'), $extensions[$i]['type']);
                        $extensions[$i]['status'] = false;
                    }
                }
            }
*/
            if(count($this->convert_actions)){
                ?>
                <table class="adminlist">
                    <thead>
                    <tr>
                        <th class="title"><?php echo Text::_('COM_MYMUSE_CONVERT_ACTIONS'); ?></th>
                        <th class="title"><?php echo Text::_('COM_MYMUSE_ACTIONS'); ?></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    $i = 0;
                    foreach ($this->convert_actions as $ext) : ?>
                        <tr class="row<?php echo $i % 2; $i++; ?>">
                            <td class="key"><?php echo $ext['name']; ?> (<?php echo Text::_($ext['message']); ?>)</td>
                            <td align="center"><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
                                <span style="<?php echo $style; ?>"><?php echo $ext['status'] ? Text::_('Success') : Text::_('NOT Successful'); ?>
                            </span></td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            <?php } ?>

            <table cellpadding="4" cellspacing="0" border="0" width="800">
                <tr>
                    <td valign="top" width="40%"><img
                            src="<?php echo 'components/com_mymuse/assets/images/logo325.jpg'; ?>"
                            height="325" width="190" alt="COM_MYMUSE Logo" align="left" /></td>
                    <td valign="top" width="60%"><strong>MyMuse</strong><br /> <span>MyMuse
                                        for Joomla! 4</span><br /> <font class="small">by <a
                                href="http://www.arboreta.ca" target="_blank">Arboreta.ca</a>
                        </font><br /> To get started
                        <ol>
                            <li><?php echo Text::_('COM_MYMUSE_INSTALL_CONFIGURE');?> <a
                                    href="index.php?option=com_mymuse&view=store&layout=edit&id=1"><?php echo Text::_('STORE'); ?></a></li>
                            <li><?php echo Text::_('COM_MYMUSE_INSTALL_CONFIGURE');?> <a
                                    href="index.php?option=com_plugins&view=plugins&filter_folder=mymuse"><?php echo Text::_('COM_MYMUSE_PLUGINS'); ?></a>
                            </li>
                            <li><?php echo Text::_('COM_MYMUSE_INSTALL_CONFIGURE_CREATE_CATEGORY');?>
                            </li>
                            <li><?php echo Text::_('COM_MYMUSE_INSTALL_CONFIGURE_USER_PROFILE');?>
                            </li>
                        </ol></td>
                </tr>
            </table>
            <h2>TYPE: <?php echo $type ?></h2>
            <h3><?php echo Text::_('Additional Extensions'); ?></h3>
            <table class="adminlist">
                <thead>
                <tr>
                    <th class="title"><?php echo Text::_('Extension'); ?></th>
                    <th width="60%"><?php echo Text::_('Status'); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($extensions as $i => $ext) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="key"><?php echo $ext['name']; ?> (<?php echo Text::_($ext['type']); ?>)</td>
                        <td align="center"><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
                            <span style="<?php echo $style; ?>"><?php echo $ext['status'] ? Text::_('Installed successfully') : Text::_('NOT Installed'); ?>
                                    </span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h3><?php echo Text::_('Actions'); ?></h3>
            <?php
            /* DEFAULT DOWNLOAD DIRECTORY */
            $name = Text::_ ( "COM_MYMUSE_MAKE_DOWNLOAD_DIR" );
            $download_dir = JPATH_ROOT . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "A_MyMuseDownloads";
            if (! file_exists ( $download_dir )) {
                if (! JFolder::create ( $download_dir )) {
                    $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message = Text::_ ( "COM_MYMUSE_COULD_NOT_MAKE_DIR" ) . "<br />$download_dir";
                } else {
                    $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message = Text::_ ( "COM_MYMUSE_DIR_CREATED" ) . " " . $download_dir;
                }
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_DIR_EXISTS" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // DEFAULT PREVIEW DIRECTORY
            $name = Text::_ ( "COM_MYMUSE_MAKE_PREVIEW_DIR" );
            $preview_dir = JPATH_ROOT . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "A_MyMusePreviews";
            if (! file_exists ( $preview_dir )) {
                if (! JFolder::create ( $preview_dir )) {
                    $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message = Text::_ ( "COM_MYMUSE_COULD_NOT_MAKE_DIR" ) . "<br />$preview_dir";
                } else {
                    $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message = Text::_ ( "COM_MYMUSE_DIR_CREATED" ) . " " . $preview_dir;
                }
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_DIR_EXISTS" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // DIRECTORY FOR GRAPHICS
            $name = Text::_ ( "COM_MYMUSE_MAKE_ALBUM_DIR" );
            $album_dir = JPATH_ROOT . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "A_MyMuseImages";
            if (! file_exists ( $album_dir )) {
                if (! JFolder::create ( $album_dir )) {
                    $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message = Text::_ ( "COM_MYMUSE_COULD_NOT_MAKE_DIR" ) . "<br />$album_dir";
                } else {
                    $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message = Text::_ ( "COM_MYMUSE_DIR_CREATED" ) . " " . $album_dir;
                }
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_DIR_EXISTS" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // copy index.html to Download Dir
            $name = Text::_ ( "index.html to Download Dir" );
            if (! JFile::copy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "index.html", $download_dir . DIRECTORY_SEPARATOR . "index.html" )) {
                $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message = Text::_ ( "COM_MYMUSE_COULD_NOT_COPY_FILE" );
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_FILE_COPIED" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            /* copy htaccess to Download Dir */
            if (stristr ( PHP_OS, 'win' )) {
                // skip the htaccess
            } else {
                $name = Text::_ ( "htaccess to Download Dir" );
                if (! JFile::copy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "htaccess.txt", $download_dir . DIRECTORY_SEPARATOR . ".htaccess" )) {
                    $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message = Text::_ ( "COM_MYMUSE_COULD_NOT_COPY_FILE" );
                } else {
                    $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message = Text::_ ( "COM_MYMUSE_FILE_COPIED" );
                }

            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // copy index.html to Preview Dir
            $name = Text::_ ( "index.html to Preview Dir" );
            if (! JFile::copy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "index.html", $preview_dir . DIRECTORY_SEPARATOR . "index.html" )) {
                $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message = Text::_ ( "COM_MYMUSE_COULD_NOT_COPY_FILE" );
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_FILE_COPIED" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // copy index.html to Album Dir
            $name = Text::_ ( "COM_MYMUSE_COPY_INDEX_TO_ALBUM_DIR" );
            if (! JFile::copy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "index.html", $album_dir . DIRECTORY_SEPARATOR . "index.html" )) {
                $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message = Text::_ ( "COM_MYMUSE_COULD_NOT_COPY_FILE" );
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_FILE_COPIED" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );

            // MOVE LOGO
            $name = Text::_ ( "COM_MYMUSE_COPY_LOGO" ) . " /images/logo150sq.jpg";
            $logo = JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "logo150sq.jpg";
            if (! file_exists ( $logo )) {
                $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message = Text::_ ( "COM_MYMUSE_COPY_LOGO_FAILED" ) . " File does not exist: " . $logo;
            } elseif (! JFile::copy ( $logo, JPATH_ROOT . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "logo150sq.jpg" )) {
                $alt = Text::_ ( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message = Text::_ ( "COM_MYMUSE_COPY_LOGO_FAILED" ) . $logo . " " . JPATH_ROOT . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "logo150sq.jpg";
            } else {
                $alt = Text::_ ( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message = Text::_ ( "COM_MYMUSE_COPY_LOGO_SUCCESS" );
            }
            $this->actions [] = array (
                'name' => $name,
                'message' => $message,
                'status' => $astatus
            );
        }

        if(!$this->already_installed && $type == "install"){

            // update store download dir
            $download_dir =  JPATH_ROOT.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."A_MyMuseDownloads";
            $name = Text::_("COM_MYMUSE_UPDATING_STORE");
            $query = "SELECT params FROM #__mymuse_store WHERE id='1'";
            $this->db->setQuery($query);
            $store_params = json_decode($this->db->loadResult(), TRUE);
            if($store_params){
                $store_params['my_download_dir'] = $download_dir;
                $registry = new JRegistry;
                $registry->loadArray($store_params);
                $new_params = (string)$registry;

                $query = "UPDATE #__mymuse_store set params='$new_params' WHERE id=1";

                $this->db->setQuery($query);
                if(!$this->db->execute()){
                    $alt = Text::_( "COM_MYMUSE_FAILED" );
                    $astatus = 0;
                    $message =  Text::_("COM_MYMUSE_PROBLEM_UPDATING_STORE").$this->db->_errorMsg;
                }else{
                    $alt = Text::_( "COM_MYMUSE_INSTALLED" );
                    $astatus = 1;
                    $message =  Text::_("COM_MYMUSE_STORE_UPDATED");
                }

            }else{
                if(function_exists(json_last_error)){
                    switch (json_last_error()) {
                        case JSON_ERROR_NONE:
                            $message = 'JSON - No errors';
                            $astatus = 1;
                            break;
                        case JSON_ERROR_DEPTH:
                            $message = 'JSON - Maximum stack depth exceeded';
                            $astatus = 0;
                            break;
                        case JSON_ERROR_STATE_MISMATCH:
                            $message = 'JSON - Underflow or the modes mismatch';
                            $astatus = 0;
                            break;
                        case JSON_ERROR_CTRL_CHAR:
                            $message = 'JSON - Unexpected control character found';
                            $astatus = 0;
                            break;
                        case JSON_ERROR_SYNTAX:
                            $message = 'JSON - Syntax error, malformed JSON';
                            $astatus = 0;
                            break;
                        case JSON_ERROR_UTF8:
                            $message = 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded';
                            $astatus = 0;
                            break;
                        default:
                            $message = 'JSON - Unknown error';
                            $astatus = 0;
                            break;
                    }
                }
            }

            $this->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );

            //UPDATE PLUGINS **
            $name = Text::_("COM_MYMUSE_ENABLE_PLUGINS");
            $query = "UPDATE #__extensions SET enabled=1 WHERE
                element='payment_paypal' OR
                element='payment_offline' OR
                element='shipping_standard' OR
                element='audio_amplitude' OR
                element='searchmymuse' OR
                element='mymuse_installer' 
                ";
            $this->db->setQuery($query);
            if(!$this->db->execute()){
                $alt = Text::_( "COM_MYMUSE_FAILED" );
                $astatus = 0;
                $message =  Text::_("COM_MYMUSE_ENABLE_PLUGINS_FAILED");
            }else{
                $alt = Text::_( "COM_MYMUSE_INSTALLED" );
                $astatus = 1;
                $message =  Text::_("COM_MYMUSE_ENABLE_PLUGINS_SUCCESS");
            }
            $this->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );

        

            //UPDATE MEDIA MANAGER TO ALLOW MP3's
            $name = Text::_("COM_MYMUSE_UPDATING_MEDIA_MANAGER");
            $query = "SELECT params FROM #__extensions WHERE element='com_media'";
            $this->db->setQuery($query);
            if($this->db->loadResult()){
                $media_params = json_decode($this->db->loadResult(), TRUE);
                if($media_params){
                    if (!stristr ( $media_params['upload_extensions'], 'mp3' )) {
                        $media_params['upload_extensions'] .= ",mp3,MP3";
                    }
                    if (!stristr ( $media_params['upload_mime'], 'audio/mpeg' )) {
                        $media_params['upload_mime'] .= ",audio/mpeg";
                    }
                    if (!stristr ( $media_params['ignore_extensions'], 'mp3' )) {
                        $media_params['ignore_extensions'] = $media_params['ignore_extensions'] != ''? $media_params['ignore_extensions'].",mp3" : "mp3";
                    }

                    if (!stristr ( $media_params['upload_extensions'], 'wav' )) {
                        $media_params['upload_extensions'] .= ",wav,WAV";
                    }
                    if (!stristr ( $media_params['upload_mime'], 'audio/wav' )) {
                        $media_params['upload_mime'] .= ",audio/wav";
                    }
                    if (!stristr ( $media_params['ignore_extensions'], 'wav' )) {
                        $media_params['ignore_extensions'] = $media_params['ignore_extensions'] != ''? $media_params['ignore_extensions'].",wav" : "wav";
                    }

                    $registry = new JRegistry;
                    $registry->loadArray($media_params);
                    $new_params = (string)$registry;

                    $query = "UPDATE #__extensions set params='$new_params' WHERE element='com_media'";

                    $this->db->setQuery($query);
                    if(!$this->db->execute()){
                        $alt = Text::_( "COM_MYMUSE_FAILED" );
                        $astatus = 0;
                        $message =  Text::_("COM_MYMUSE_PROBLEM_UPDATING_MEDIA_MANAGER").$this->db->_errorMsg;
                    }else{
                        $alt = Text::_( "COM_MYMUSE_INSTALLED" );
                        $astatus = 1;
                        $message =  Text::_("COM_MYMUSE_MEDIA_MANAGER_UPDATED");
                    }
                }else{
                    if(function_exists(json_last_error)){
                        switch (json_last_error()) {
                            case JSON_ERROR_NONE:
                                $message = 'JSON - No errors';
                                $astatus = 1;
                                break;
                            case JSON_ERROR_DEPTH:
                                $message = 'JSON - Maximum stack depth exceeded';
                                $astatus = 0;
                                break;
                            case JSON_ERROR_STATE_MISMATCH:
                                $message = 'JSON - Underflow or the modes mismatch';
                                $astatus = 0;
                                break;
                            case JSON_ERROR_CTRL_CHAR:
                                $message = 'JSON - Unexpected control character found';
                                $astatus = 0;
                                break;
                            case JSON_ERROR_SYNTAX:
                                $message = 'JSON - Syntax error, malformed JSON';
                                $astatus = 0;
                                break;
                            case JSON_ERROR_UTF8:
                                $message = 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded';
                                $astatus = 0;
                                break;
                            default:
                                $message = 'JSON - Unknown error';
                                $astatus = 0;
                                break;
                        }
                    }
                }
            }
            $this->actions[] = array('name'=>$name,'message'=>$message, 'status'=>$astatus );
        }



        if(count($this->actions)){
            ?>
            <table class="adminlist">
                <thead>
                <tr>
                    <th class="title"><?php echo Text::_('COM_MYMUSE_POST_INSTALL_ACTIONS'); ?></th>
                    <th class="title"><?php echo Text::_('COM_MYMUSE_ACTIONS'); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </tfoot>
                <tbody>
                <?php
                $i = 0;
                foreach ($this->actions as $ext) : ?>
                    <tr class="row<?php echo $i % 2; $i++; ?>">
                        <td class="key"><?php echo $ext['name']; ?> (<?php echo Text::_($ext['message']); ?>)</td>
                        <td align="center"><?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
                            <span style="<?php echo $style; ?>"><?php echo $ext['status'] ? Text::_('Success') : Text::_('NOT Successful'); ?>
                        </span></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
            <?php

            if (!empty($this->targetJoomlaVersion) && version_compare(JVERSION, $this->targetJoomlaVersion, '<'))
            {
               ?>
               <h3><?php echo Text::_('COM_MYMUSE_YOU_MUST_UPDATE_JOOMLA'); ?></h3>

               <?php
               $myFile = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'mymuse.php';
               $tmp = "

<h3><?php echo Text::_('COM_MYMUSE_YOU_MUST_UPDATE_JOOMLA'); ?></h3>
<?php
return true;

               ";
            
                if(!FILE::write($myFile, $tmp)){
                    echo "<h2>Could not make $myFile. <br>Joomal 3 needs this.</h2>";
                }
                $myFile2 = JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'mymuse.php';
                if(!FILE::write($myFile2, $tmp)){
                    echo "<h2>Could not make $myFile. <br>Joomal 3 needs this.</h2>";
                }
            }
            return true;
        }
    }
}