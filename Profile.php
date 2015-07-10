<?php

// this is going to take in the info about the courses and its going to save it to the users profile and display it properly... 
include 'ChromePhp.php';


	if(isset($_GET['SaveCourses'])){
		$cart = $_GET['cart'];
		$cart = json_decode($cart);
		Chromephp::log($cart);


		// lets save this to the database...but we need to somehow know who is logged in right now...
		
		


	}






?>