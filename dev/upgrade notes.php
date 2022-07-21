		// Check for a database error.
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
