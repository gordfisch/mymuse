<?php 

class MyMuseOtherViewsCest
{
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

        $cats = $I->createMymuseCategories();
        $I->comment(print_r($cats, true));


        //basic product with both CD and tracks
        $id1 = $I->createMymuseProduct($this->mock_cd);

        $I->comment("Making tracks for id $id1");
        $this->mock_track->id = $id1;
        $I->createMymuseTrack($this->mock_track, $this->myConfigStd);

        $this->mock_track1->id = $id1;
        $I->createMymuseTrack($this->mock_track1, $this->myConfigStd);

        $this->mock_all_track->id = $id1;
        $I->createMymuseAllTrack($this->mock_all_track,  $this->myConfigStd);

        $I->comment("Making menu for Single Product");
        $this->mock_single_menu->jform_request_id_id = $id1;
        $I->makeMenus($this->mock_single_menu);



        //second product
        $id1 = $I->createMymuseProduct($this->mock_cd2);

        $I->comment("Making tracks for id $id1");
        $this->mock_track2_1->id = $id1;
        $I->createMymuseTrack($this->mock_track2_1, $this->myConfigStd);

        $this->mock_track2_2->id = $id1;
        $I->createMymuseTrack($this->mock_track2_2, $this->myConfigStd);

        $this->mock_all_track2->id = $id1;
        $I->createMymuseAllTrack($this->mock_all_track2,  $this->myConfigStd);

        $I->comment("Making menu for CatBlog");
        $I->makeMenus($this->mock_catblog_menu);

        $I->comment("Making menu for CatList");
        $I->makeMenus($this->mock_catlist_menu);

        $I->comment("Making menu for Cat Tracks");
        $I->makeMenus($this->mock_cattrack_menu);

        //make a cart menu
        $I->comment("Making menu for Cart");
        $I->makeMenus($this->mock_cart_menu);

        //make a list my orders menu
        $I->comment("Making menu for List My Orders");
        $I->createMenuItem2('List My Orders', 'MyMuse', 'List My Orders');


    }

    // tests

    public function MyMuseOtherViews(AcceptanceTester $I)
    {
        $I->amOnPage("index.php");
        $I->click("Cat Blog");
        $I->see('Are You My Sister');
        $I->see('Second Life');

        $I->click("Cat List");
        $I->see('Subcategories');
        $I->see('Iron Brew');
        $I->see('Cats in the Kitchen');
        $I->see('Image');
        $I->see('Title');
        $I->see('Hits');
        $I->see('Price');
        $I->see('Are You My Sister');
        $I->see('Second Life');

        $I->click("Cat Tracks");
        $I->see('Artist');
        $I->see('Album');
        $I->see('Name');
        $I->see('Price');
        $I->see('Play');
        $I->see('Add');
        $I->see('Are You My Sister');
        $I->see('Second Life');
        $I->see('Are You My Sister Song');
        $I->see('Coonla');
        $I->see('The Foggy Dew');
        $I->see('The Headlands');

        $I->click("C");
        $I->see('Cats in the Kitchen');
        $I->see('Coonla');
        $I->see('The Headlands');
        $I->dontsee('Are You My Sister Song');
        $I->dontsee('The Foggy Dew');

        $I->click("I");
        $I->see('Iron Brew');
        $I->see('Are You My Sister Song');
        $I->see('The Foggy Dew');
        $I->dontsee('Coonla');
        $I->dontsee('The Headlands');


    //orderOne

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

        //see if it's in admin
        $I->comment("see if it's in admin");
        $I->doAdministratorLogin();
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=orders');
        $I->click($id);
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        $I->wait(1);

        //See if it's in the front end
        $I->amOnPage('index.php');
        if($I->seePageHasText('Log in')){
            $I->doFrontEndLogin();
            $I->wait(2);
        }
        $I->click("List My Orders");
        $I->waitForText('Your Order History', 30);

        
        $I->see($id);

  
    }
}
