<?php
	require_once('quickLinkedIn.php');


	$link = new quickLinkedIn('YOUR_KEY', 'YOUR_SECRET');
	$link->setRedirect('http://localhost/quickLinkedInPHP/');
	
	if(!isset($_GET['code']))
	{
		$link->setScope('r_basicprofile r_emailaddress rw_groups');
		$url = $link->getAccessUrl();
		print '<a href="'.$url.'">Login....</a>';
	}
	else
	{
		$result = $link->auth_code(); //Turns response code into token.
		
		
		if(!$result)
		{
			print('Something went wrong! D:');
			print '<br/><a href="/quickLinkedInPHP/">try again?</a>';
			die();
		}
		
		//LETS GET DIRTY!
		//https://developer.linkedin.com/documents/profile-fields
		var_dump($link->call('people/~:(first-name,last-name)'));
		var_dump($link->call('people/~/email-address'));
		var_dump($link->call('people/~/group-memberships'));
		
		

	}
	
	
	
	print '<br/><br/><a href="/quickLinkedInPHP/">try again?</a>';
	
	

?>