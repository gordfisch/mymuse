<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2022 Arboreta Internet Services <https://www.arboreta.ca>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Basic MyMuse
 *
 * @since  3.7.3
 */
class BasicMymuseCest {

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

        include(dirname(dirname(dirname(dirname(__FILE__)))).'/_data/mock_objects.php');

        $I->am('Administrator');
        $I->doAdministratorLogin(null, null, false);
        
         $I->changeStoreConfig($this->mock_delay_config );

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
        $I->placeIteminCart($this->mock_order_vinyl);
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

//FULL REGISTRATION

        $I->doAdministratorLogin();
        $I->changeGlobalOptions($this->mock_user_config);
        $I->changeStoreConfig($this->mock_regFull_config);
        $I->disablePlugin("User - MyMuse No Registration Profile");
        $I->enablePlugin("User - MyMuse Profile");


        $I->placeIteminCart($this->mock_order_track);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        if($I->seePageHasText('Please log in or register')){
            $I->fillFullRegForm($this->mock_user);
            $I->wait(2);
            $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);
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

//TEST COUPONS FULL REG
        $I->comment("TESTING COUPONS");
        $I->changeStoreConfig($this->mock_coupon_config );
        $I->clearMymuseCoupons();

        $I->comment("*** MAX USES PER USER ***");
        $I->createMymuseCoupon($this->mock_coupon1);
       
        $I->doFrontEndLogin($this->mock_user->jform_username, $this->mock_user->jform_password1);
       
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->fillField(array('id' => 'coupon'), '12345');
        $I->click("Submit");
        $I->see('Your coupon has been added');
        $I->see('$16.00', ['css' => '#mytotal']);
        $I->click(['id' => 'checkout']);
        
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');
        $I->comment("OrderID was ".$id);

        
        $I->clearCart();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->fillField(array('id' => 'coupon'), '12345');
        $I->click("Submit");
        $I->see('You have used the coupon the maximum number of times');


        $I->comment("*** EXPIRED COUPON ***");
        $I->createMymuseCoupon($this->mock_coupon2);
        $I->clearCart();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->fillField(array('id' => 'coupon'), '12346');
        $I->click("Submit");
        $I->see('That coupon has expired and is no longer valid');

        $I->comment('Clear Coupons');
        $I->clearMymuseCoupons();
        $I->doFrontendLogout();

//NO REGISTRATION
        //$I->doAdministratorLogin();
        $I->comment("NO REGISTRATION)");
        $I->changeGlobalOptions($this->mock_user_default_config);

        $I->changeStoreConfig($this->mock_noReg_config);

        $I->comment("Change Plugins configuration)");
        $I->enablePlugin("User - MyMuse No Registration Profile");
        $I->disablePlugin("User - MyMuse Profile");

        $I->placeIteminCart($this->mock_order_track);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->wait(2);

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
        $I->wait(4);


    
//TEST COUPONS NO REG
        $I->comment("TESTING COUPONS");
        $I->changeStoreConfig($this->mock_coupon_config );
        $I->createMymuseCoupon($this->mock_coupon1);

        $I->clearCart();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");

        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->click("My Cart");

        $I->fillField(array('id' => 'coupon'), '12345');
        $I->click("Submit");
        $I->see('Your coupon has been added');
        $I->see('$16.00', ['css' => '#mytotal']);
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');
        $I->comment("OrderID was ".$id);
        $I->doFrontendLogout();

        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->click("My Cart");
        $I->fillField(array('id' => 'coupon'), '12345');
        $I->click("Submit");
        $I->see('Your coupon has been added');
        $I->see('$16.00', ['css' => '#mytotal']);
        $I->click(['id' => 'checkout']);
        $I->click(['id' => 'confirm']);
        $I->click(['id' => 'offline']);
        $id = $I->grabTextFrom(['class' => 'myordernumber']);
        $id = ltrim($id, '0');
        $I->comment("OrderID was ".$id);

        
        $I->doFrontendLogout();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->click("My Cart");


        $I->fillField(array('id' => 'coupon'), '12345');
        $I->click("Submit");
        $I->see('Coupon has exceeded its maximum number of uses');

 //TEST TAXES
    
        $I->comment("TESTING TAXES");
        $I->amOnPage('/administrator/index.php?option=com_mymuse&view=taxrates');
        $I->waitForText('MyMuse : Taxrates', '30', array('css' => 'H1'));
        $I->createMymuseTaxrate($this->mock_tax1);
        $I->createMymuseTaxrate($this->mock_tax2);


        //order one
        $I->doFrontendLogout();
        $I->clearCart();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);

        $I->see('User Profile');
        $this->mock_noreg_user->jform_profile_country            = 'Canada';
        $this->mock_noreg_user->jform_profile_region             = 'Quebec';
        $this->mock_noreg_user->jform_profile_postal_code        = 'J4H 2K1';
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->wait(2);
        $I->click("My Cart");
        $I->see('$22.61', ['css' => '#mytotal']);
        $I->see('$1.00', ['css' => '#taxgst']);
        $I->see('$1.61', ['css' => '#taxpst']);

        $I->clearCart();
        $I->doFrontendLogout();
        $I->placeIteminCart($this->mock_order_cd);
        $I->click("My Cart");
        $I->click(['id' => 'checkout']);
        $I->see('User Profile');
        $this->mock_noreg_user->jform_profile_country            = 'Canada';
        $this->mock_noreg_user->jform_profile_region             = 'Ontario';
        $this->mock_noreg_user->jform_profile_postal_code        = 'M5W 1E6';
        $I->fillNoRegForm($this->mock_noreg_user);
        $I->wait(2);
        $I->click("My Cart");
        $I->see('$21.00', ['css' => '#mytotal']);
        $I->see('$1.00', ['css' => '#taxgst']);
        $I->dontsee('PST');

    }
}