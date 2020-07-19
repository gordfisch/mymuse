<?php

class MyMuseReportsCest
{
	var $id_cd = 1;
	var $id_track1 = 2;
	var $id_track2 = 3;
    var $id_vinyl = 4;
    var $id_hoodie = 5;


    public function _before(AcceptanceTester $I)
    {

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

        $path = "com_mymuse-latest.zip";
        $I->installExtensionFromFileUpload($path, 'Extension');
    }

    // tests

    public function MyMuseReports(AcceptanceTester $I)
    {
        $I->amOnPage('administrator/index.php?option=com_mymuse&view=reports');
        $I->click('Create Report');
        

        //$I->changeReportOptions($mock);

        

    }
}