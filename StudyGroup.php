<!DOCTYPE html>
<html lang="en">
	<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Course-Match Homepage</title>
  
  
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="bootstrap-social/bootstrap-social.less" rel="stylesheet">
    <link href="bootstrap-social/bootstrap-social.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    
     <link href="bootstrap-calendar/components/bootstrap3/css/bootstrap.min.css" rel="stylesheet">
     <link href="bootstrap-calendar/components/bootstrap3/css/bootstrap-theme.min.css" rel="stylesheet">
     <link href="bootstrap-calendar/components/bootstrap3/css/bootstrap-theme.css" rel="stylesheet">
     <link href="bootstrap-calendar/css/calendar.min.css" rel="stylesheet">

  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script type="text/javascript" src="bootstrap-calendar/components/jquery/jquery.min.js"></script> 
  <script type="text/javascript" src="bootstrap-calendar/components/bootstrap3/js/bootstrap.js"></script> 
  <script type="text/javascript" src="bootstrap-calendar/components/bootstrap3/js/bootstrap.min.js"></script> 
  
  

	<script src="underscore/underscore.js"></script>
  <script type="text/javascript" src="bootstrap-calendar/js/language/ar-SA.js"></script>
  <script type="text/javascript" src="bootstrap-calendar/components/jstimezonedetect/jstz.js"></script>
  <script type="text/javascript" src="bootstrap-calendar/components/jstimezonedetect/jstz.js"></script>
  
   <script type="text/javascript" src="bootstrap-calendar/js/calendar.min.js"></script>    
   
     <link href="bootstrap-calendar/css/calendar.css" rel="stylesheet">
     <link href="bootstrap-calendar/components/bootstrap3/css/bootstrap.css" rel="stylesheet">
      

    </head>
	<div>
		 <?php
		 include 'ChromePhp.php';

            require __DIR__ . '/facebook-php-sdk/autoload.php';
            use Facebook\FacebookSession;
            use Facebook\FacebookRedirectLoginHelper;
            use Facebook\FacebookRequest;
            use Facebook\FacebookResponse;
            use Facebook\FacebookSDKException; 
            use Facebook\FacebookRequestException;
            use Facebook\FacebookAuthorizationException;
            use Facebook\GraphObject;
            use Facebook\Entities\AccessToken;
            use Facebook\HttpClients\FacebookCurlHttpClient;
            use Facebook\HttpClients\FacebookHttpable;

            session_start();  // start the session so we can save stuff...

            if (!isset($_SESSION['PICTURE'])){

            FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 
            $helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/CourseFinder.php');
            // use this facebook login and just display the users name and hopefully profile picture...

                // here we need to create the session... 
                    try {
                  $session = $helper->getSessionFromRedirect();
                } catch( FacebookRequestException $ex ) {
                  Chromephp::log("FacebookRequestException");
                  Chromephp::log($ex);
                  // When Facebook returns an error
                } catch( Exception $ex ) {
                  // When validation fails or other local issues
                  Chromephp::log("Exception");
                  Chromephp::log($ex);
            }

            if (isset($session)){
                // graph api request for user data...
                  $request = new FacebookRequest($session, 'GET', '/me');

                  $response = $request ->execute();

                  // get the response... 

                   $graphObject = $response->getGraphObject();
                   $fbid = $graphObject->getProperty('id');              // To Get Facebook ID
                   $fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
                   $femail = $graphObject->getProperty('email');    // To Get Facebook email ID

                     /* ---- Session Variables -----*/
                     $_SESSION['FBID'] = $fbid;           
                        $_SESSION['FULLNAME'] = $fbfullname;
                     $_SESSION['EMAIL'] =  $femail;

                    
                    //create the url... 
				$_SESSION['PICTURE']= $profile_pic = "http://graph.facebook.com/".$fbid."/picture";
                        echo "<div>";               
                        echo "<img src=\"" . $profile_pic . "\" />"; 

                        echo $fbfullname;

                        echo "</div>";

            }else{
                // there is no session and we redirect to the login page... 
                  Chromephp::log("there is no session here");
                   $loginUrl = $helper->getLoginUrl(array("user_friends", "user_status","email","public_profile", "user_photos"));
                   Chromephp::log($loginUrl);
                 header("Location: ".$loginUrl);
            }
}else{

echo "<div>";               
echo "<img src=\"" . $_SESSION['PICTURE'] . "\" />"; 
echo $_SESSION['FULLNAME'];
echo "</div>";
}

		 ?>
	</div>

<ul class="nav nav-tabs">
  <li role="presentation"><a href="CourseFinder.php">Course Browser</a></li>
  <li role="presentation"><a href="Profile.php">Profile</a></li>
  <li role="presentation" class = "active"><a href="#">Study Groups</a></li>
  <li role="presentation"><a href="WhosFree.php">Who's Free</a></li>
</ul>

 


<body>
   <div class="page-header">

    <div class="pull-right form-inline">
      <div class="btn-group">
        
        <button class="btn btn-primary" data-calendar-nav="prev">&lt;&lt; Prev</button>
        <button class="btn btn-default" data-calendar-nav="today">Today</button>
        <button class="btn btn-primary" data-calendar-nav="next">Next &gt;&gt;</button>
      </div>
      <div class="btn-group">
        <button class="btn btn-warning" data-calendar-view="year">Year</button>
        <button class="btn btn-warning active" data-calendar-view="month">Month</button>
        <button class="btn btn-warning" data-calendar-view="week">Week</button>
        <button class="btn btn-warning" data-calendar-view="day">Day</button>
      </div>
    </div>

    <h3>March 2013</h3>
    
  </div>
  <!-- create the calendar here!!-->
 <div id="container"> 



  <div id="calendar" class="row-fluid"></div>
   
    
    <!-- we need a container here to make this calendar here smaller. --> 
    <script type="text/javascript">
        
        
        var calendar = $("#calendar").calendar(
          
            {
                tmpl_path: "bootstrap-calendar/tmpls/",
                events_source: 'bootstrap-calendar/events.json.php',
            });         

    </script>


</div>

   <script type="text/javascript" src="bootstrap-calendar/js/calendar.js"></script>
    <script type="text/javascript" src="bootstrap-calendar/js/app.js"></script>






<?php


?>

</body> 

</html>