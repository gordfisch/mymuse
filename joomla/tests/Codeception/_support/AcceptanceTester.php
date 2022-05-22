<?php
/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Codeception\Actor;
use Codeception\Lib\Friend;


/**
 * Acceptance Tester global class for entry point.
 *
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 * @method getConfig(string $string)
 *
 * @SuppressWarnings(PHPMD)
 *
 * @since  3.7.3
 */
class AcceptanceTester extends Actor
{
	use _generated\AcceptanceTesterActions;



	/**
	 * Function to check for PHP Notices or Warnings.
	 *
	 * @param   string  $page  Optional, if not given checks will be done in the current page
	 *
	 * @note    doAdminLogin() before
	 *
	 * @since   3.7.3
	 *
	 * @return  void
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		$I = $this;

		if ($page)
		{
			$I->amOnPage($page);
		}

		$I->dontSeeInPageSource('Notice:');
		$I->dontSeeInPageSource('<b>Notice</b>:');
		$I->dontSeeInPageSource('Warning:');
		$I->dontSeeInPageSource('<b>Warning</b>:');
		$I->dontSeeInPageSource('Strict standards:');
		$I->dontSeeInPageSource('<b>Strict standards</b>:');
		$I->dontSeeInPageSource('The requested page can\'t be found');
	}

	/**
	 * Function to wait for JS to be properly loaded on page change.
	 *
	 * @param   integer|float  $timeout  Time to wait for JS to be ready
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	public function waitForJsOnPageLoad($timeout = 1)
	{
		$I = $this;

		$I->waitForJS('return document.readyState == "complete"', $timeout);

		// Wait an additional 500ms to make sure that really all JS is loaded
		$I->wait(0.5);
	}



   /**
    * Define custom actions here
    */



   function seePageHasElement($element)
   {
       try {
           $this->seeElement($element);
       } catch (Exception $f) {
           return false;
       }
       return true;
   }

   function seePageHasText($text)
   {
    $this->comment('Looking for "'.$text.'"');
       try {
           $this->see($text);
       } catch (Exception $f) {
           return false;
       }
       return true;
   }

   function clearCart()
   {
        $this->amOnPage('index.php');
        $this->click("My Cart");
        if($this->seePageHasText("Clear Cart")){
          $this->click("Clear Cart");
        }
        return true;
   }

   public function selectFromDropdown($selector, $n)
   {
       $option = $this->grabTextFrom($selector . ' option:nth-child(' . $n . ')');
       $this->selectOption($selector, $option);
   }

  /**
   * Selects an option in a Joomla Radio Field based on its id
   *
   * @param   string  $id   The text in the <label> with for attribute that links to the radio element
   * @param   string  $option  The text in the <option> to be selected in the chosen radio button
   *
   * @return  void
   *
   * @since   3.0.0
   */
  public function selectOptionInRadioFieldById($radioId, $option)
  {

    $this->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
  }



  /**
   * Uninstall Extension based on a name
   *
   * @param   string  $extensionName  Is important to use a specific
   *
   * @return  void
   *
   * @since   3.0.0
   * @throws  \Exception
   */
  public function uninstallMymuse($extensionName)
  {
      $this->comment('Going to  uninstallExtension');
      $this->uninstallExtension($extensionName);

  }



   /**
    * Create mymuse categories
    *
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseCategories()
   {
   
    $cats = array();

   	//MyMuse
   	$this->comment('Category creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'MyMuse');

   	$this->comment('Click new category apply button');

    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
   	$this->comment('see a success message after saving the category');
   	$this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['MyMuse'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);


   	//Artists
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Artists');
   	//choose the parent
	  $this->click(array('css' => 'a.chzn-single > span'));
	  $this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[2]"));
   
   	$this->comment('Click new category apply button');
   	
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
    $this->comment('see a success message after saving the category');
    $this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['Artists'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);


   	//Genres
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Genres');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[2]"));

   	$this->comment('Click new category apply button');
   	
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
    $this->comment('see a success message after saving the category');
    $this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['Genres'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);



   	//Iron Brew
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Iron Brew');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[3]"));
   	$this->comment('Click new category apply button');
   	
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
    $this->comment('see a success message after saving the category');
    $this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['Iron Brew'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);


   	//World Beat
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'World Beat');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[5]"));
   	$this->comment('Click new category apply button');
   	
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
    $this->comment('see a success message after saving the category');
    $this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['World Beat'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);


    //Cats in the Kitchen
    $this->comment('Category MyMuse creation in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

    $this->waitForElement(array('class' => 'page-title'));

    $this->comment('Click new category button');
    $this->click($this->locator->adminToolbarButtonNew);

    $this->waitForElement(array('class' => 'page-title'));

    $this->fillField(array('id' => 'jform_title'), 'Cats in the Kitchen');
    //choose the parent
    $this->click(array('css' => 'a.chzn-single > span'));
    $this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[3]"));
    $this->comment('Click new category apply button');
    
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.apply\');"]']);
    $this->comment('see a success message after saving the category');
    $this->see('Category saved', '#system-message-container');

    $this->executeJS("document.getElementById('jform_id').removeAttribute('readonly');");
    $id = $this->executeJS("return document.getElementById('jform_id').value;");
    $this->comment('Created category with id '.$id);
    $cats['Cats in the Kitchen'] = $id;
    $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'category.save\');"]']);


    return $cats;
   }


 

   /**
    * Create mymuse product
    * @param object mock : object of a product 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseProduct($mock)
   {


    $this->comment('Product creation in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=products');
    $this->waitForElement(array('class' => 'page-title'));

    $this->comment('Click new product button');
    $this->click($this->locator->adminToolbarButtonNew);
    $this->waitForElement(array('class' => 'page-title'));

    $this->comment('Fill in image fields');
    $this->waitForElement(array('css' => '#myTabTabs > li:nth-child(2)'));
    $this->click('Images');
    $this->waitForElement(array('id' => 'jform_list_image'));
    $this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

    $this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

    if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){
      $this->selectOptionInChosenById('jform_product_images', $mock->jform_product_images);
    }

    $this->comment('Fill in recording details fields');
    $this->click('Recording Details');
    $this->fillField(array('id' => 'jform_product_made_date'), $mock->jform_product_made_date);
    $this->fillField(array('id' => 'jform_product_full_time'), $mock->jform_product_full_time);

    if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){
      $this->click(["css" => "#jform_product_country_chzn > a.chzn-single > span"]);
      $this->click(["xpath" => "//div[@id='jform_product_country_chzn']/div/ul/li[$mock->jform_product_country]"]);
    }
    

    $this->fillField(array('id' => 'jform_product_publisher'), $mock->jform_product_publisher);
    $this->fillField(array('id' => 'jform_product_producer'), $mock->jform_product_producer);
    $this->fillField(array('id' => 'jform_product_studio'), $mock->jform_product_studio);

    $this->comment('Fill in Dimensions fields');
    $this->click('Dimensions');
    $this->fillField(array('id' => 'jform_product_weight'), $mock->jform_product_weight);
    $this->fillField(array('id' => 'jform_product_length'), $mock->jform_product_length);
    $this->fillField(array('id' => 'jform_product_width'), $mock->jform_product_width);
    $this->fillField(array('id' => 'jform_product_height'), $mock->jform_product_height);


    $this->comment('Fill in Details fields');
    $this->click('Details');
    $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

    $this->selectOptionInChosenById('jform_artistid', $mock->jform_artist);
    $this->selectOptionInChosenById('jform_catid', $mock->jform_cat);


    $this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
    

    if(isset($mock->jform_product_physical)){
      $this->selectOptionInChosenById('jform_product_physical', $mock->jform_product_physical);
    }

    if(isset($mock->jform_attribs_product_price_physical)){
      $this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
    }else{
      $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
    }

    if(isset($mock->jform_attribs_product_price_mp3)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
    }
    if(isset($mock->jform_attribs_product_price_mp3_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
    }
    if(isset($mock->jform_attribs_product_price_wav)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
    }
    if(isset($mock->jform_attribs_product_price_wav_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
    }
    

    
    $this->waitForElement(array('id' => 'jform_articletext_ifr'));
    $editor_frame_name = 'articletext-frame';
    $this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
    $this->switchToIFrame($editor_frame_name);
    $this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


    $this->switchToIFrame();
    
    $this->comment('Click Apply button to save');
    $this->click($this->locator->adminToolbarButtonApply);

    $this->see('saved');
    $id = $this->grabValueFrom('input[id=jform_id]');
    $this->comment('Created product with id '.$id);
    return $id;
  }


   /**
    * Edit MyMuse Product
    * @param object mock : object of a product
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function editMymuseProduct($mock)
   {
   

    $this->comment('Product editing in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
    $this->waitForElement(array('class' => 'page-title'));


    $this->comment('Fill in image fields');
    $this->waitForElement(array('css' => '#myTabTabs > li:nth-child(2)'));
    $this->click('Images');
    $this->waitForElement(array('id' => 'jform_list_image'));
    $this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

    $this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

    if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){
      $this->selectOptionInChosenById('jform_product_images', $mock->jform_product_images);
    }

    $this->comment('Fill in recording details fields');
    $this->click('Recording Details');
    $this->fillField(array('id' => 'jform_product_made_date'), $mock->jform_product_made_date);
    $this->fillField(array('id' => 'jform_product_full_time'), $mock->jform_product_full_time);

    if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){
      $this->click(["css" => "#jform_product_country_chzn > a.chzn-single > span"]);
      $this->click(["xpath" => "//div[@id='jform_product_country_chzn']/div/ul/li[$mock->jform_product_country]"]);
    }
    

    $this->fillField(array('id' => 'jform_product_publisher'), $mock->jform_product_publisher);
    $this->fillField(array('id' => 'jform_product_producer'), $mock->jform_product_producer);
    $this->fillField(array('id' => 'jform_product_studio'), $mock->jform_product_studio);

    $this->comment('Fill in Dimensions fields');
    $this->click('Dimensions');
    $this->fillField(array('id' => 'jform_product_weight'), $mock->jform_product_weight);
    $this->fillField(array('id' => 'jform_product_length'), $mock->jform_product_length);
    $this->fillField(array('id' => 'jform_product_width'), $mock->jform_product_width);
    $this->fillField(array('id' => 'jform_product_height'), $mock->jform_product_height);


    $this->comment('Fill in Details fields');
    $this->click('Details');
    $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

    $this->selectOptionInChosenById('jform_artistid', $mock->jform_artist);
    $this->selectOptionInChosenById('jform_catid', $mock->jform_cat);


    $this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
    

    if(isset($mock->jform_product_physical)){
      $this->selectOptionInChosenById('jform_product_physical', $mock->jform_product_physical);
    }

    if(isset($mock->jform_attribs_product_price_physical)){
      $this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
    }else{
      $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
    }

    if(isset($mock->jform_attribs_product_price_mp3)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
    }
    if(isset($mock->jform_attribs_product_price_mp3_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
    }
    if(isset($mock->jform_attribs_product_price_wav)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
    }
    if(isset($mock->jform_attribs_product_price_wav_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
    }

    if(isset($mock->jform_attribs_special_status)){
      $this->selectOptionInChosenById('jform_attribs_special_status', $mock->jform_attribs_special_status);
    }
    

    
    $this->waitForElement(array('id' => 'jform_articletext_ifr'));
    $editor_frame_name = 'articletext-frame';
    $this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
    $this->switchToIFrame($editor_frame_name);
    $this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


    $this->switchToIFrame();
    $this->comment('I leave time to the iframe to close');
    $this->wait(2);
    
    $this->comment('Click Apply button to save');
    $this->click($this->locator->adminToolbarButtonApply);

    $this->see('saved');
    $this->comment('Edited product with id '.$mock->id);



  }


  /**
   * Edit MyMuse Product Field
   * @param object mock : object of a product field
   * @return  void
   *
   * @since   3.7.5
   * @throws  \Exception
   */
  public function editMymuseProductField($mock)
  {
  

   $this->comment('Product edit Field in /administrator/ ');
   $this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
   $this->waitForElement(array('class' => 'page-title'));

   $this->click($mock->tab);
   foreach($mock->select as $select){
     if($select['type'] == "select"){
       $this->selectOptionInChosenById($select['option'], $select['value']);
     }elseif($select['type'] == "radio"){
       $this->selectOptionInRadioField($select['option'], $select['value']);

     }elseif($select['type'] == "multiSelect"){
       $this->selectMultipleOptionsInChosen($select['option'], $select['value']);
       $this->wait(1); 
       $this->click($mock->tab);
     }elseif($select['type'] == "text"){
       $this->fillField(array('id' => $select['option']), $select['value']);

     }   
   }
   $this->comment('Click Apply button to save');
   $this->click($this->locator->adminToolbarButtonApply);

   $this->see('saved');

   $this->comment('Edited product with id '.$mock->id);

 }



   /**
    * Create mymuse product tracks
    * @param object mock : object of a track 
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseTrack($mock, $config)
   {
     
      $joomla_folder = dirname(dirname(dirname(__FILE__)));
      
      if($config['my_copy_tracks']){
        //move some tracks in there
        $from_dir     = $joomla_folder.'/mymuse-downloads';
        $download_dir = $joomla_folder.$config['my_download_dir'];
        $preview_dir  = $joomla_folder.$config['my_preview_dir'];

        if($config['my_download_dir_format'] == "0"){
            $download_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
            $preview_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
        }

        $this->comment('joomla_folder = '.$joomla_folder);
        $this->comment('download dir = '.$download_dir);
        $this->comment('preview dir = '.$preview_dir);



        //preview
        if(!file_exists($preview_dir)){
          if(!mkdir($preview_dir, 0755)){
            $this->comment('Could not make dir '.$preview_dir.'/wav');
            return false;
          }
        }

        if(!file_exists($preview_dir.'/'.$mock->preview )){
          if(!file_exists($from_dir.'/'.$mock->preview )){
              $this->comment('Could not find preview '.$from_dir.'/'.$mock->preview);
              return false;
          }
          copy($from_dir.'/'.$mock->preview, $preview_dir.'/'.$mock->preview);
        }
        

        // downloads
        if(!file_exists($download_dir)){
          if(!mkdir($download_dir, 0755)){
            $this->comment('Could not make dir '.$download_dir.'/wav');
            return false;
          }
        }

        //main file(s)
        if($config['my_download_dir_format'] == "1"){
          if(in_array('wav',$config['my_formats'])){
            if(!file_exists($download_dir.'/wav')){
              if(!mkdir($download_dir.'/wav', 0755)){
                $this->comment('Could not make dir '.$download_dir.'/wav');
                return false;
              }
            }
          }
          if(in_array('mp3',$config['my_formats'])){
            if(!file_exists($download_dir.'/mp3')){
              if(!mkdir($download_dir.'/mp3', 0755)){
                $this->comment('Could not make dir '.$download_dir.'/mp3');
                return false;
              }
            }
          }
          if(!file_exists($download_dir.'/wav/'.$mock->wav)){
            if(!file_exists($from_dir.'/'.$mock->wav )){
                $this->comment('Could not find jform_attribs_product_price_wav_all $from_dir / '.$mock->wav);
                return false;
            }
            copy($from_dir.'/'.$mock->wav, $download_dir.'/wav/'.$mock->wav);
          }

          if(!file_exists($download_dir.'/mp3/'.$mock->mp3)){
            if(!file_exists($from_dir.'/'.$mock->mp3 )){
                $this->comment('Could not find mp3 '.$from_dir.'/'.$mock->mp3);
                return false;
            }
            copy($from_dir.'/'.$mock->mp3, $download_dir.'/mp3/'.$mock->mp3);
          }
        }else{
          if(!file_exists($download_dir.'/'.$mock->mp3)){
            if(!file_exists($from_dir.'/'.$mock->mp3 )){
                $this->comment('Could not find mp3 '.$from_dir.'/'.$mock->mp3);
                return false;
            }
            copy($from_dir.'/are-you-my-sister.mp3', $download_dir.'/'.$mock->mp3);
          }

        }
      }

      $this->comment('Track creation in /administrator/ ');
      $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

      $this->comment('List Tracks');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listracks\');"]']);

      $this->comment('New Track');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.addfile\');"]']);

      $this->comment('Fill in Fields');
      $this->fillField(array('id' => 'jform_title'), $mock->jform_title);
      $this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

      if($config['my_price_by_product'] != "1"){
        $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
      }

      $this->comment('Choose Track');
      $this->click('TRACKS');
      for($i = 0; $i < count($config['my_formats']); $i++){
        $this->selectOptionInChosenById('select_file'.$i, $mock->{$config['my_formats'][$i]});
      }

      $this->comment('Choose Preview');
      $this->click('PREVIEWS');
      $this->selectOptionInChosenById('file_preview', $mock->preview);

      $this->comment('Save');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.applyfile\');"]']);

      $this->see('File saved');
      $id = $this->grabValueFrom('input[id=jform_id]');
      $this->comment('Created track with id '.$id);
      return $id;

    }

   /**
    * Create mymuse all track
    * @param object mock : object of a all track 
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function createMymuseAllTrack($mock, $config)
    {
        $this->comment('ALL Track creation in /administrator/ ');
        $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

        $this->comment('List Tracks');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listracks\');"]']);

        $this->comment('New Track');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.new_allfiles\');"]']);

        $this->comment('Fill in Fields');
        $this->fillField(array('id' => 'jform_title'), $mock->jform_title);
        $this->fillField(array('id' => 'jform_alias'), $mock->jform_alias);
        $this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

        if($config['my_download_dir_format'] != "1"){
          $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
        }
        if(isset($mock->jform_product_discount)){
          $this->fillField(array('id' => 'jform_product_discount'), $mock->jform_product_discount);
        }
        $this->comment('Save');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.save_allfiles\');"]']);

        $this->see('All File saved');

    }

    /**
    * Create mymuse product items
    * @param object mock : object of an item 
    * @param object config : the current config 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseItems($mock, $config)
   {
     


      $this->comment('Item creation in /administrator/ for itemid '.$mock->id);
      $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

      $this->comment('List Items');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listitems\');"]']);


      //$this->comment('Check attributes '.print_r($mock->jform_attribute, true));
      if(isset($mock->jform_attribute) && is_array($mock->jform_attribute)){
        $att_count = count($mock->jform_attribute);
        foreach($mock->jform_attribute as &$attr){
          $this->comment('New Attribute '.$attr['name']);
          $this->click(["css" => '#toolbar-new > .btn']);
          $this->wait(1);
          $this->fillField(array('id' => 'jform_name'), $attr['name']);
          $this->fillField(array('id' => 'jform_extra_base'), $attr['extra_base']);
          $this->fillField(array('id' => 'jform_extra_css'), $attr['extra_css']);
          $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'productattributesku.save\');"]']);
          $this->wait(1);
          $this->see('Item successfully saved');
        }
        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'productattributesku.myreturn\');"]']);
      }
      $this->click('Create ITEMS');
      $this->wait(2);
      $this->see('Changes to Item saved');
    }

   /**
    * Create mymuse taxes
    * @param object mock : object of a taxrate 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseTaxrate($mock)
   {
     

      $this->comment('Taxrate creation in /administrator/ ');
      $this->amOnPage('administrator/index.php?option=com_mymuse&view=taxrates');
      $this->waitForElement(array('class' => 'page-title'));

      $this->comment('Click new Taxrate button');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.add\');"]']);
      $this->waitForElement(array('class' => 'page-title'));

      $this->fillField(array('id' => 'jform_tax_name'), $mock->tax_name);
      $this->fillField(array('id' => 'jform_tax_rate'), $mock->tax_rate);
      $this->selectOption(array('id' => 'jform_tax_applies_to'), $mock->tax_applies_to);
      $this->selectOption(array('id' => 'jform_country'), $mock->country);
      $this->selectOption(array('id' => 'jform_province'), $mock->province);
      $this->selectOption(array('id' => 'jform_compounded'), $mock->compounded);


      $this->comment('Save');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.apply\');"]']);
      $this->see('Item successfully saved');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'taxrate.save\');"]']);
  }


   /**
    * Create mymuse coupon
    * @param object mock : object of a coupon 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseCoupon($mock)
   {
     

      $this->comment('Coupon creation in /administrator/ ');
      $this->amOnPage('administrator/index.php?option=com_mymuse&view=coupons');
      $this->waitForElement(array('class' => 'page-title'));

      $this->comment('Click new Coupon button');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'coupon.add\');"]']);
      $this->waitForElement(array('class' => 'page-title'));

      $this->fillField(array('id' => 'jform_title'), $mock->title);
      $this->fillField(array('id' => 'jform_code'), $mock->code);
      $this->selectOption(array('id' => 'jform_coupon_type'), $mock->coupon_type);
      if($mock->coupon_type == "Per Product" && isset($mock->product_id) && $mock->product_id > 0){
        $this->selectOption(array('id' => 'jform_product_id'), $mock->product_id);

      }
      $this->fillField(array('id' => 'jform_coupon_value'), $mock->coupon_value);
      $this->selectOptionInChosenById('jform_currency_id', $mock->currency_id);
      $this->selectOptionInChosenById('jform_coupon_value_type', $mock->coupon_value_type);
      $this->fillField(array('id' => 'jform_coupon_max_uses'), $mock->coupon_max_uses);
      $this->fillField(array('id' => 'jform_coupon_max_uses_per_user'), $mock->coupon_max_uses_per_user);
      $this->fillField(array('id' => 'jform_start_date'), $mock->start_date);
      $this->fillField(array('id' => 'jform_expiration_date'), $mock->expiration_date);


      $this->comment('Save');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'coupon.apply\');"]']);
      $this->see('Item successfully saved');

  }


    /**
    * MakeMenus
    * @param object mock : object of a menu 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function makeMenus($mock)
    {
     
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      
      //$this->comment('mock = '.print_r($mock, true));
      $this->comment('Clear Search');
      $this->click(['css' => '.js-stools-btn-clear']);
      $this->comment('Click new menu button');
      $this->click($this->locator->adminToolbarButtonNew);
      $this->waitForElement(array('class' => 'page-title'));
      $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

      $this->click(['class' =>'btn-primary']);
      $this->switchToIFrame('Menu Item Type');
      $this->wait(1);
      $this->comment('Choose type MyMuse::'.$mock->menu_type);
      $this->click($mock->menu_item_type);
      $this->wait(1);
      $this->click($mock->menu_type);
      $this->switchToIFrame();

      if(strpos($mock->menu_type, 'Category') !== false) {
        //category menu
          $this->comment('Select Category '.$mock->jform_request_id_name);
          $this->selectOptionInChosenById('jform_request_id', $mock->jform_request_id_name);

      }elseif($mock->jform_request_id_id && $mock->jform_request_id_name){
        // product menu
          $this->comment('Select Product '.$mock->jform_request_id_name);
          $this->executeJS("document.getElementById('jform_request_id_name').removeAttribute('disabled');");
          $this->fillField(array('id' => 'jform_request_id_name'), $mock->jform_request_id_name);
          $this->executeJS("document.getElementById('jform_request_id_id').value='".$mock->jform_request_id_id."';");
      }
       


      $this->click($this->locator->adminToolbarButtonApply);
      $this->see('Menu item saved');

      $this->comment('Select Home Page');
      $this->amOnPage('index.php');
      $this->see($mock->jform_title);
      $this->click($mock->jform_title);
      $name = str_replace("- ","",$mock->jform_request_id_name);
      $this->comment('look for name '.$name);
      $this->see($name);
 
   }

   public function createMenuItem2($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
   {
    $this->comment("I open the menus page");
    $this->amOnPage('administrator/index.php?option=com_menus&view=menus');
    $this->waitForText('Menus', 30, array('css' => 'H1'));
    $this->checkForPhpNoticesOrWarnings();

    $this->comment("I click in the menu: $menu");
    $this->click(array('link' => $menu));
    $this->waitForText('Menus: Items', 30, array('css' => 'H1'));
    $this->checkForPhpNoticesOrWarnings();

    $this->comment("I click new");
    $this->click("New");
    $this->waitForText('Menus: New Item', 30, array('css' => 'h1'));
    $this->checkForPhpNoticesOrWarnings();
    $this->fillField(array('id' => 'jform_title'), $menuTitle);

    $this->comment("Open the menu types iframe");
    $this->click("Select");
    $this->waitForElement(array('id' => 'menuTypeModal'), 30);
    $this->wait(1);
    $this->switchToIFrame("Menu Item Type");

    $this->comment("Open the menu category: $menuCategory");

    // Open the category
    $this->wait(1);
    $this->waitForElement(array('link' => $menuCategory), 30);
    $this->click(array('link' => $menuCategory));

    $this->comment("Choose the menu item type: $menuItem");
    $this->wait(1);
    $this->waitForElement(array('xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"), 30);
    $this->click(array('xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"));
    $this->comment('I switch back to the main window');
    $this->switchToIFrame();
    $this->comment('I leave time to the iframe to close');
    $this->wait(2);
    $this->selectOptionInChosen('Language', $language);
    $this->waitForText('Menus: New Item', '30', array('css' => 'h1'));
    $this->comment('I save the menu');
    $this->click("Save");

    $this->waitForText('Menu item saved', 30, array('id' => 'system-message-container'));
   }
    /**
    * placeIteminCart
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function placeIteminCart($mock)
    {
        $this->comment('Select Page '.$mock->menu_link);
        $this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('index.php');
        $this->click($mock->menu_link);
        foreach($mock->select as $select){
          $this->click(['id' => $select]);
          $this->wait(7);       
        }

    }

    /**
    * changeStoreConfig
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function changeStoreConfig($mock)
    {
        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('administrator/index.php?option=com_mymuse&view=store&layout=edit&id=1');
        $this->waitForElement(array('class' => 'page-title'));
        $this->click($mock->tab);

        foreach($mock->select as $select){
          //$this->scrollTo(array('css' => '#'.$select['option']));
          if($select['type'] == "select"){
            $this->selectOptionInChosenById($select['option'], $select['value']);
          }elseif($select['type'] == "radio"){
            $this->selectOptionInRadioField($select['option'], $select['value']);

          }elseif($select['type'] == "multiSelect"){
            $this->selectMultipleOptionsInChosen($select['option'], $select['value']);
            $this->wait(1); 
            $this->click($mock->tab);
          }elseif($select['type'] == "text"){
            $this->fillField(array('id' => $select['option']), $select['value']);

          }   
        }

        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'store.save\');"]']);
        $this->see('Item successfully saved');
    }

    /**
    * changeGlobalOptions
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function changeGlobalOptions($mock)
    {

        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('administrator/index.php?option=com_config&view=component&component='.$mock->component);
        $this->waitForElement(array('class' => 'page-title'));
        $this->click($mock->tab);
        foreach($mock->select as $select){
          if($select['type'] == "select"){
            $this->selectOptionInChosenById($select['option'], $select['value']);
            $this->wait(1);   
          }elseif($select['type'] == "radio"){
            $this->selectOptionInRadioField($select['option'], $select['value']);
            $this->wait(1); 
          }elseif($select['type'] == "text"){
            $this->fillField(array('id' => $select['option']), $select['value']);

          }

        }
        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'config.save.component.save\');"]']);
        //$this->see('Configuration saved');
    }

    /**
    * changeReportOptions
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function changeReportOptions($mock)
    {

        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('administrator/index.php?option=com_mymuse&view=reports');
        $this->waitForElement(array('class' => 'page-title'));
        foreach($mock->select as $select){
          if($select['type'] == "select"){
            $this->selectOptionInChosenById($select['option'], $select['value']);
            $this->wait(1);   
          }elseif($select['type'] == "radio"){
            $this->selectOptionInRadioField($select['option'], $select['value']);
            $this->wait(1); 
          }elseif($select['type'] == "text"){
            $this->fillField(array('id' => $select['option']), $select['value']);

          }

        }
        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'config.save.component.save\');"]']);
        //$this->see('Configuration saved');
    }


    /**
     * Changes the module options
     *
     * @param   string  $module  The full name of the module
     *
     * @return  void
     *
     * @since   3.0.0
     * @throws  \Exception
     */
    public function editModule($mock)
    {
      $this->amOnPage('administrator/index.php?option=com_modules');
      $this->waitForElement(array('class' => 'page-title'));
      $this->searchForItem($mock->module);
      $this->click(array('link' => $mock->module));
      $this->wait(1);
      if($mock->tab){
        $this->click($mock->tab);
      }
      
      foreach($mock->select as $select){
        if($select['type'] == "select"){
          $this->selectOptionInChosenById($select['option'], $select['value']);
        }elseif($select['type'] == "radio"){
          $this->selectOptionInRadioField($select['option'], $select['value']);

        }elseif($select['type'] == "multiSelect"){
          $this->selectMultipleOptionsInChosen($select['option'], $select['value']);
          $this->wait(1); 
          $this->click($mock->tab);
        }elseif($select['type'] == "text"){
          $this->fillField(array('id' => $select['option']), $select['value']);

        }   
      }

      $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'module.apply\');"]']);
      $this->see('Module saved');

    }
    /**
    * fillFullRegForm
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function fillFullRegForm($mock_user)
    {
      $this->amOnPage('index.php/edit-profile?view=registration');
      $this->fillField(array('id' => 'jform_name'), $mock_user->jform_user);
      $this->fillField(array('id' => 'jform_username'), $mock_user->jform_username);
      $this->fillField(array('id' => 'jform_password1'), $mock_user->jform_password1);
      $this->fillField(array('id' => 'jform_password2'), $mock_user->jform_password2);
      $this->fillField(array('id' => 'jform_email1'), $mock_user->jform_email1);
      $this->fillField(array('id' => 'jform_email2'), $mock_user->jform_email2);
      $this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
      $this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
      $this->fillField(array('id' => 'jform_profile_city'), $mock_user->jform_profile_city);
      $this->fillField(array('id' => 'jform_profile_postal_code'), $mock_user->jform_profile_postal_code);
      $this->fillField(array('id' => 'jform_profile_phone'), $mock_user->jform_profile_phone);
      $this->fillField(array('id' => 'jform_profile_mobile'), $mock_user->jform_profile_mobile);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_country', $mock_user->jform_profile_country); 
      $this->wait(1);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_region', $mock_user->jform_profile_region);
      $this->wait(1);
      $this->click('Register');
      

    }


    /**
    * fillNoRegForm
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function fillNoRegForm($mock_user)
    {
      $this->fillField(array('id' => 'jform_profile_first_name'), $mock_user->jform_profile_first_name);
      $this->fillField(array('id' => 'jform_profile_last_name'), $mock_user->jform_profile_last_name);
      $this->fillField(array('id' => 'jform_profile_email'), $mock_user->jform_profile_email);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_country', $mock_user->jform_profile_country); 
      $this->wait(1);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_region', $mock_user->jform_profile_region);
      $this->wait(1);
      //$this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
      //$this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
      $this->click('Save');

    }










   /* ########################## clear functions ############################### */

    /**
     * Clear menus
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearMenus()
    {
     
      $this->comment('Clear MyMuse Menus ');
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      $this->waitForElement(array('class' => 'page-title'));

      if($this->seePageHasElement('#cb1')){

        $this->comment('Check All');
        $this->click(["xpath" => "//input[@name='checkall-toggle']"]);
        //unclick home
        $this->click(["css" => "#cb0"]);


        $this->comment('Click on Trash button ');
        $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);


        $this->comment('Open Search Tools ');
        $this->click(["css" => ".js-stools-btn-filter"]);
        $this->wait(2);
        
        $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

        $this->executeJS("document.getElementById('filter_published').style.display = 'block';");


        $this->comment('Select Status Trashed ');
        $this->selectOptionInChosenById('filter_published', 'Trashed');

        $this->waitForElement(array("name" => "checkall-toggle"));

        $this->comment('Check All Trashed Items');
        $this->click(["name" => "checkall-toggle"]);

        $this->comment('Empty Trash ');
        $this->click(["css" => "#toolbar-delete > button"]);

        $this->acceptPopup();
        $this->waitForText('deleted', '30', array('id' => 'system-message-container'));
      }else{
        $this->comment('Menus are clear ');

      }

    }
    /**
     * Clear trashed menus
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearTrashedMenus()
    {
     
      $this->comment('Clear MyMuse Trashed Menus ');
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      $this->waitForElement(array('class' => 'page-title'));


      $this->comment('Open Search Tools ');
      $this->click(["css" => ".js-stools-btn-filter"]);
      $this->wait(2);
      
      $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

      $this->executeJS("document.getElementById('filter_published').style.display = 'block';");


      $this->comment('Select Status Trashed ');
      $this->selectOptionInChosenById('filter_published', 'Trashed');

      if($this->seePageHasText("Single")){
        $this->waitForElement(array("name" => "checkall-toggle"));

        $this->comment('Check All Trashed Items');
        $this->click(["name" => "checkall-toggle"]);

        $this->comment('Empty Trash ');
        $this->click(["css" => "#toolbar-delete > button"]);

        $this->acceptPopup();
        $this->waitForText('deleted', '30', array('id' => 'system-message-container'));
      }


    }
    /**
     * Clear users
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearUsers()
    {
     
      $this->comment('Clear MyMuse Users ');
      $this->amOnPage('administrator/index.php?option=com_users&view=users');
      $this->waitForElement(array('class' => 'page-title'));

      $this->searchForItem("Test User");
      if(!$this->seePageHasText("No Matching Results")){
        $this->click(['id' => 'cb0']);
        $this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

        $this->acceptPopup();
        $this->waitForText('deleted', '30', array('id' => 'system-message-container'));
      }
      $this->searchForItem("Buyer");
      if(!$this->seePageHasText("No Matching Results")){
        $this->click(['id' => 'cb0']);
        $this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

        $this->acceptPopup();
        $this->waitForText('deleted', '30', array('id' => 'system-message-container'));
      }

      
    }

    /**
     * Clear mymuse categories
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearMymuseCategories()
    {
     

      $this->comment('Clear Categories  in /administrator/ ');
      $this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');
      $this->waitForElement(array('class' => 'page-title'));

      $this->comment('Check All');
      $this->click(["xpath" => "//input[@name='checkall-toggle']"]);

      $this->comment('Click on Trash button ');
      $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);


      $this->comment('Open Search Tools ');
      $this->click(["css" => ".js-stools-btn-filter"]);
      $this->wait(2);
      
      $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

      $this->executeJS("document.getElementById('filter_published').style.display = 'block';");
      $this->selectOptionInChosenById('filter_published', 'Trashed');


      $this->waitForElement(array("name" => "checkall-toggle"));

      $this->comment('Check All Trashed Items');
      $this->click(["name" => "checkall-toggle"]);

      $this->comment('Empty Trash ');
      $this->click(["css" => "#toolbar-delete > button"]);

      $this->acceptPopup();
      $this->waitForText('deleted', '30', array('id' => 'system-message-container'));

    }


      /**
    * Clear mymuse products
    *
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function clearMymuseProducts()
   {
   

    $this->comment('Clear products  in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=products');
    $this->waitForElement(["xpath" => "//input[@name='checkall-toggle']"]);

    $this->comment('Check All');
    $this->click(["xpath" => "//input[@name='checkall-toggle']"]);

    $this->comment('Click on Trash button ');
    $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);
    $this->see('successfully trashed');

    $this->comment('Open Search Tools ');
    $this->click(["css" => ".js-stools-btn-filter"]);
    $this->wait(2);
    
    $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

    $this->executeJS("document.getElementById('filter_published').style.display = 'block';");
    $this->selectOptionInChosenById('filter_published', 'Trashed');


    $this->click(["css" => 'input[name="checkall-toggle"]']);
    $this->click(["css" => '.button-delete']);

  }


  /**
    * Clear mymuse Coupons
    *
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function clearMymuseCoupons()
   {
     

      $this->comment('Clear Coupons  in /administrator/ ');
      $this->amOnPage('administrator/index.php?option=com_mymuse&view=coupons');
      if($this->seePageHasText('Trash')){
        $this->selectOptionInChosenById('filter_published', '- Select Status -');
        $this->waitForElement(["xpath" => "//input[@name='checkall-toggle']"]);

        $this->comment('Check All');
        $this->click(["xpath" => "//input[@name='checkall-toggle']"]);


      
        $this->comment('Click on Trash button ');
        $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);
        $this->see('successfully trashed');

       // $this->selectOption(array('id' => 'filter_published'), 'Trashed');
        $this->selectOptionInChosenById('filter_published', 'Trashed');

        $this->click(["css" => 'input[name="checkall-toggle"]']);
        $this->click(["css" => '.button-delete']);
      }
   }




}
