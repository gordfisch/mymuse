<?php

class MyMuseModuleCest
{
	var $id_cd = 1;
	var $id_track1 = 2;
	var $id_track2 = 3;
    var $id_vinyl = 4;
    var $id_hoodie = 5;


    public function _before(AcceptanceTester $I)
    {

        $this->myConfigStd = array(
            'my_download_dir' => '/images/A_MyMuseDownloads',
            'my_preview_dir' => '/images/A_MyMusePreviews',
            'my_download_dir_format' => "0",
            'my_price_by_product' => 0,
            'my_formats' => array('mp3'),
            'my_copy_tracks' => 1
        );


        include(dirname(dirname(__FILE__)).'/_data/mock_objects.php');

        $I->doAdministratorLogin();

        $I->wait(3);
        if($I->seePageHasText('would like your permission')){
            $I->click('Never');
            $I->wait(3);
        }else{
            $I->comment("No Stats");
        }
        if($I->seePageHasText('Read Messages')){
            $I->click('Read Messages');
            $I->wait(3);
            $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'hideAll\');"]']);
        }


        $I->comment("Check if MyMuse needs uninstall");
        $I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
        $I->waitForText('Extensions: Manage', '30', array('css' => 'H1'));
        $I->searchForItem('MyMuse');
        if($I->seePageHasText('There are no extensions installed matching your query')){
            //we are good to go. MyMuse is not installed.
        }else{
            $I->uninstallExtension('mymuse');
        }
        
        $I->clearMenus();

        $I->amOnPage('/administrator/index.php?option=com_users&view=users');
        $I->waitForText('Users', '30', array('css' => 'H1'));
        if($I->seePageHasText('Buyer') || $I->seePageHasText('Test-User') ){
            $I->clearUsers();
        }


        $path = "com_mymuse-latest.zip";
        $I->installExtensionFromFileUpload($path, 'Extension');

        $I->createMymuseCategories();

        //basic product with both CD and tracks
        $this->id_cd = $I->createMymuseProduct($this->mock_cd);

        $I->comment("Making tracks for id ".$this->id_cd);
        $this->mock_track->id = $this->id_cd;
        $this->mock_track1->id = $this->id_cd;
        $this->id_track1 = $I->createMymuseTrack($this->mock_track, $this->myConfigStd);
        $this->id_track2 = $I->createMymuseTrack($this->mock_track1, $this->myConfigStd);

        $I->comment("Making menu for Single Product");
        $this->mock_single_menu->jform_request_id_id = $this->id_cd;
        $I->makeMenus($this->mock_single_menu);

        //a vinyl only product
        $this->id_vinyl = $I->createMymuseProduct($this->mock_vinyl);

        $I->comment("Making menu for Vinyl Product");
        $this->mock_vinyl_menu->jform_request_id_id = $this->id_vinyl;
        $I->makeMenus($this->mock_vinyl_menu);


        //a product with items
        $this->id_hoodie = $I->createMymuseProduct($this->mock_hoodies);

        $I->comment("Making Items for ProductItems");
        $this->mock_hoodies->id = $this->id_hoodie;
        $I->createMymuseItems($this->mock_hoodies, $this->myConfigStd);

        $I->comment("Making menu for Hoodie Product");
        $this->mock_hoodies_menu->jform_request_id_id = $this->id_hoodie;
        $I->makeMenus($this->mock_hoodies_menu);

        //make a cart menu
        $I->comment("Making menu for Cart");
        $I->makeMenus($this->mock_cart_menu);
        

        $I->publishModule('MyMuse Latest Module');
        $I->setModulePosition('MyMuse Latest Module', 'position-7');
        $I->displayModuleOnAllPages('MyMuse Latest Module');
        $I->amOnPage('index.php');
        $I->see('MyMuse Latest Module');

    }

    // tests

    public function MyMuseModule(AcceptanceTester $I)
    {

    //orderOne
    	$this->mock_order_cd->select[0] = "box_".$this->id_cd;
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);

        if($I->seePageHasText('Please log in or register')){
            $I->doFrontEndLogin();
        }
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');      
        $I->comment("OrderID was ".$id);


   // orderMultiple
        $this->mock_order_track->select[0] = "box_".$this->id_track1;
        $I->placeIteminCart($this->mock_order_track);
        $this->mock_order_vinyl->select[0] = "box_".$this->id_vinyl;
        $I->placeIteminCart($this->mock_order_vinyl);
        $this->mock_order_hoodie->select[2] = "box_".$this->id_hoodie;
        $I->placeIteminCart($this->mock_order_hoodie);

        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
  
        if($I->seePageHasText('Please log in or register')){
            $I->doFrontEndLogin();
        }
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');      
        $I->comment("OrderID was ".$id);



        $I->editModule($this->mock_module_config);
        $I->amOnPage('index.php');
        $I->dontSee('Track : Are You My Sister Song');

        


        $this->mock_module_config->select[0]['value'] = "Tracks";
        $I->editModule($this->mock_module_config);
        $I->amOnPage('index.php');
        $I->see('Track : Are You My Sister Song');
        $I->seeElement('.cp-circle-control');

        

    }
}