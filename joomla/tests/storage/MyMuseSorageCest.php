<?php 

class MyMuseStorageCest
{
    public function _before(AcceptanceTester $I)
    {

        $this->myConfigStd = array(
            'my_download_dir' => 'mmawsdownloads',
            'my_preview_dir' => 'mmawspreviews',
            'my_download_dir_format' => "0",
            'my_price_by_product' => 0,
            'my_formats' => array('mp3'),
            'my_copy_tracks' => 0
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


        $I->changeStoreConfig($this->mock_delay_config );
        $I->changeStoreConfig($this->mock_amazon_config );
        

        //add amazon values
        $I->enablePlugin("MyMuse Storage S3");
        $I->amOnPage('administrator/index.php?option=com_plugins&view=plugins');
        $I->searchForItem("MyMuse Storage S3");
        $I->waitForElement("//form[@id='adminForm']/div/table/tbody/tr[1]/td[4]/a[contains(text(), 'MyMuse Storage S3')]", 30);
        $I->checkExistenceOf("MyMuse Storage S3");
        $I->click("MyMuse Storage S3");
        include(dirname(dirname(__FILE__)).'/_data/amazon.php');

        $I->comment("Update Amazon plugin");
        $I->fillField(array('id' => "jform_params_my_s3web"), $my_s3web);
        $I->fillField(array('id' => "jform_params_my_s3access"), $my_s3access);
        $I->fillField(array('id' => "jform_params_my_s3secret"), $my_s3secret);
        $I->fillField(array('id' => "jform_params_my_s3region"), $my_s3region);
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'plugin.save\');"]']);
        $I->waitForText('Plugin saved.', 30, array('id' => 'system-message-container'));



        $I->createMymuseCategories();

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
/*
        //a vinyl only product
        $id2 = $I->createMymuseProduct($this->mock_vinyl);

        $I->comment("Making menu for Vinyl Product");
        $this->mock_vinyl_menu->jform_request_id_id = $id2;
        $I->makeMenus($this->mock_vinyl_menu);


        //a product with items
        $id3 = $I->createMymuseProduct($this->mock_hoodies);

        $I->comment("Making Items for ProductItems");
        $this->mock_hoodies->id = $id3;
        $I->createMymuseItems($this->mock_hoodies, $this->myConfigStd);

        $I->comment("Making menu for Hoodie Product");
        $this->mock_hoodies_menu->jform_request_id_id = $id3;
        $I->makeMenus($this->mock_hoodies_menu);
*/
        //make a cart menu
        $I->comment("Making menu for Cart");
        $I->makeMenus($this->mock_cart_menu);
        //$I->createMenuItem('My Cart', 'MyMuse', 'Shopping Cart');

       //make a list my orders menu
        $I->comment("Making menu for List My Orders");
        $I->createMenuItem2('List My Orders', 'MyMuse', 'List My Orders');

        //make an edit profile menu
        $I->comment("Making menu for Edit Profile");
        $I->createMenuItem2('Edit Profile', 'Users', 'Edit User Profile');


    }

    // tests

    public function MyMuseBasic(AcceptanceTester $I)
    {


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

   // orderMultiple

        $I->placeIteminCart($this->mock_order_track);
        $I->placeIteminCart($this->mock_order_track1);
        //$I->placeIteminCart($this->mock_order_vinyl);
        //$I->placeIteminCart($this->mock_order_hoodie);

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
        //administrator/index.php?option=com_mymuse&view=order&layout=edit&id=1007
        $I->see('Order Summary');
        $I->selectOptionInChosenById('jform_order_status', 'Confirmed');
        $I->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'order.save\');"]']);
        
        //See if it's in the front end
        $I->amOnPage('index.php');
        if($I->seePageHasText('Log in')){
            $I->doFrontEndLogin();
        }
        $I->click("List My Orders");
        $I->waitForText('Your Order History', 30);
        $I->see($id);

        $I->doFrontendLogout();

    }


}
