MANIFEST
Removing old Plugin: audio_amplitude
Removing old Plugin: 10104 mymuse_discount
Removing old Plugin: 10061 payment_offline
Removing old Plugin: 10110 payment_paypal
Removing old Plugin: 10020 searchmymuse
Removing old Plugin: 10015 shipping_standard
Removing old Plugin: 10014 shipping_price
Removing old Plugin: 10180 shipping_usps
Removing old Plugin: 10019 mymuse
Removing old Plugin: 10044 mymusenoreg
Removing old Plugin: 10018 redirectonlogin
Removing old Module: 10022 audio_amplitude


DATABASE
10044 plg_user_mymusenoreg mymusenoreg
10064 plg_mymuse_vote mymuse_vote
10104 PLG_MYMUSE_DISCOUNT mymuse_discount
10019 plg_user_mymuse mymuse
10020 plg_search_mymuse searchmymuse
10110 plg_mymuse_payment_paypal payment_paypal
10061 plg_mymuse_payoffline payment_offline
10023 MyMuse com_mymuse

10105 mod_mymuse_jplayer mod_mymuse_jplayer
10021 mod_mymuse_latest mod_mymuse_latest
10022 mod_mymuse_minicart mod_mymuse_minicart


Check for a database error.
		try
		{
			$db->setQuery( $query);
			$results = $db->loadRowList();
		}
		catch (RuntimeException $e)
		{
		    $this->_subject->setError($e->getMessage());
		    return false;
		}



		
Factory::getApplication()->enqueueMessage();

#__mymuse_product.product_made_date 
=> 
#__mymuse_product.product_release_date

#__mymuse_product.file_name
=> 
#__mymuse_product.digital

file_name array => sub products with track_parentid


#__mymuse_shopper_group
	`shopper_group_name` varchar(32) DEFAULT NULL,

=>
#__mymuse_shoppergroup
	`usergroups_id` int(11) NOT NULL DEFAULT '2',

data
metadata
digital
physical
attribs
