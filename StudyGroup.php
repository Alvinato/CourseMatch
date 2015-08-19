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

  <div>
    <!-- this is going to select the study group that the user is looking at!! -->
    <!-- we need this to dynamically to create the list based on the user.--> 
    <?php
    // this needs to grab the courses for the user.
    $db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

  $firstname = $_SESSION['firstname'];
  $lastname = $_SESSION['lastname'];
  $sql = "SELECT Courses FROM Users WHERE firstname= '$firstname' AND lastname= '$lastname'";

   $result = $conn->query($sql);
     
      if ($result->num_rows > 0) {  
        while($row = $result->fetch_assoc()) {
           $user_courses = $row['Courses'];  
           $course_object = json_decode($user_courses);
           $_SESSION['user_courses'] = $course_object; // this holds the user courses...
        }
      }

     //Chromephp::log($_SESSION['user_courses']); 

     // with this we need to create the options that can be picked here... 
     echo "<select name='courses'  onchange='changeFunc();'>";
     for($r = 0; $r < count($_SESSION['user_courses']); $r++){

      //Chromephp::log($_SESSION['user_courses'][$r]->course);
        $coursename = $_SESSION['user_courses'][$r]->course;
        // should maybe place more then just the coursename through this value here!!
       echo " <option value=$coursename>
                  $coursename
              </option>";
     }
      echo "</select>";
      // we need the javascript function to check if this select has been in fact pressed and then call the php funciton from there!



      // when this calendar is reloaded we need the calendar to relaod with the correct courses and the day.
      // thus we need to change the events.json.php file and reload.. 

      if(isset($_POST['functionname'])){
  
      if($_POST['functionname']='course_events'){
        $aResult['result'] = course_events($_POST['arguments']);
        
        echo json_encode($aResult);
        }
        } 


       function course_events($value){

        // we need to find the rest of the info related to this course... 
          // i dont need this i just need the other course info
         if(isset($_GET['courses'])){
        //Chromephp::log('this is running right now');
         $_SESSION['courses'] = $_GET['courses'];
        }

        //--->> 

        //Chromephp::log($_SESSION['user_courses']);
        // we need to go through this in order to gather the other info about this course... 

        for($r = 0; $r < count($_SESSION['user_courses']); $r++){
          // we need to check every single one of these user courses
          $current_course = $_SESSION['user_courses'][$r];
          $course_name = $current_course->course;
          $course_type = $current_course->type;
          if ($course_name == $value && $course_type == 'Lecture'){
            

            //Chromephp::log($current_course);  
           
           // we have all the info right now from this current course content... 
           // ---->>    
           // we should grab the start time and the end time and the day
           $course_start = $current_course->currentstart;
           $course_end = $current_course->currentend;
           $course_day = $current_course->currentday; 

           $day_array = explode(',',$course_day);  
           //Chromephp::log($day_array);
           $times_a_week = count($day_array);

           $number_of_classes = $times_a_week * 14;  // this will be the number of times the loop is going to run!!

           //$date = date_create_from_format('d/m/y', '07/09/2015');
           $d = new DateTime( '2015-09-07' );
            $theArray = array();

            // need to do one week at a time
           for ($x = 0; $x < 14; $x++){
              $days_adding;
              // we actaully do need the rest of that incase for other classes during that week...
               $day_one;
               $day_two;
              $day_three;

            for ($f = 0; $f < count($day_array);$f++){
              $curday = $day_array[$f]; 
              if($curday == "Mon"){
                if($f == 0){
                  $day_one = 1;
                  $days_adding = 1;
                }
                if($f == 1){
                  $day_two = 1;
                  $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 1;
                  $days_adding = $day_three - $day_two;
                }
              }
              if($curday == "Tue"){
                if($f == 0){
                  $day_one = 2;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 2;
                  $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 2;
                  $days_adding = $day_three - $day_two;
                }
              }
              if($curday == "Wed"){

                if($f == 0){
                  $day_one = 3;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 3;
                  $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 3;
                   $days_adding = $day_three - $day_two;
                }
              }
              if($curday == "Thu"){
                if($f == 0){
                  $day_one = 4;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 4;
                    $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 4;
               $days_adding = $day_three - $day_two;
                }
              }
              if($curday == "Fri"){
                if($f == 0){
                  $day_one = 5;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 5;
                  $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 5;
                  $days_adding = $day_three - $day_two;
                }
              }

              if($curday == "Sat"){
                if($f == 0){
                  $day_one = 6;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 6;
                  $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 6;
                 $days_adding = $day_three - $day_two;
                }
              }
              // sunday is going to be zero...
              if($curday == "Sun"){
                if($f == 0){
                  $day_one = 0;
                  $days_adding = $day_one;
                }
                if($f == 1){
                  $day_two = 0;
               $days_adding = $day_two - $day_one;
                }
                if($f == 2){
                  $day_three = 0;
                 $days_adding = $day_three - $day_two;
                }
              }

              date_modify($d, '+'.$days_adding.' day');

               Chromephp::log($d);

              //Chromephp::log($d->date);

              $date_array = explode('-', $d->date);
              //--->> 
              $year = $date_array[0];
              // take away the first two in the string
              //$year = substr($year, 2, 4);
              //Chromephp::log($year);
              $month = $date_array[1];

              $monthName = date('F', mktime(0, 0, 0, $month, 10)); // March
             // Chromephp::log($monthName);
             // Chromephp::log($month);
              $day = $date_array[2];
              
              $day = explode(' ', $day);
              $day = $day[0];
              //Chromephp::log($year.'.'.$month.'.'.$day);
              //Chromephp::log($day);
              //$date_string = $year.'.'.$month.'.'.$day ;
              
              $date_string = $day.' '.$monthName.' '.$year;

              //Chromephp::log($date_string);

             $myvar = (strtotime($date_string) * 1000);
             $myvar = (string)$myvar;
            // Chromephp::log($myvar); 
              $obj = array(
                'class'=>'event-warning',
                'end' => $myvar,
                'id' => '1',
                'start' => $myvar,
                'title' => $course_name.' Lecture',  // right here we need to say the class for this day! 
                'url'=>'http://www.example.com/'
                );
             
              //Chromephp::log($obj);
              array_push($theArray, $obj);
              
              
            }
            Chromephp::log("inside the weekly loop");
            Chromephp::log($day_one);
            Chromephp::log($day_two);
            Chromephp::log($day_three);
            // we need to see what the days_adding has been made up to be must add up to 7
            if(!is_null($day_one) && is_null($day_two) && is_null($day_three)){
              $days_adding_week = 7 - $day_one;
            }
            // TODO!! this code is only working for CPSC 304...
            if(!is_null($day_one) && !is_null($day_two) && is_null($day_three)){
              $days_adding_week = 7 - $day_two;
            }
            if(!is_null($day_one) && !is_null($day_two) && !is_null($day_three)){
              $days_adding_week = 7 - $day_three;
            }
             date_modify($d, '+'.$days_adding_week.' day');
           }

          $jsonString = file_get_contents('./bootstrap-calendar/events.json.php');
          
           $data = json_decode($jsonString);

              $data->result = $theArray;
             
              $encoded_data = json_encode($data);
              
              file_put_contents('./bootstrap-calendar/events.json.php', $encoded_data);
          }
        }
       } 
    ?>


     <script>
   
     function changeFunc(){ 
     var courses = document.getElementsByName("courses")[0];
     var selectedValue = courses.options[courses.selectedIndex].value;
   
      var object =  $.ajax({
                type: "POST",
                url: "StudyGroup.php",   // maybe have to make another url here...
                
                data: {functionname: 'course_events', arguments: selectedValue},  

                success: function (obj, textstatus) {
                
                 // console.log("the function ran successfully!!");
                 // here lets have the page refresh!... 
                 location.reload(); 
                 // we need this page to reload the page where the person was looking at on the calendar

                  }
            });


   }
     </script> 

   </div> 

   <div class="page-header">

    <div class="pull-right form-inline">
       
        <!--this button is going to create a new event!!-->
        <!-- this needs to prompt some sort of popup that will allow the user to specify which dates this event is.-->
        <div class="btn-group">
          <button class="btn btn-default" create-event="event">
          Create Event
        </button>
        </div>
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
<div>
  <div class="span3">
      <div class="row-fluid">
        <select id="first_day" class="span12">
          <option value="" selected="selected">First day of week language-dependant</option>
          <option value="2">First day of week is Sunday</option>
          <option value="1">First day of week is Monday</option>
        </select>
       
        <label class="checkbox">
          <input type="checkbox" value="#events-modal" id="events-in-modal"> Open events in modal window
        </label>
        <label class="checkbox">
          <input type="checkbox" id="format-12-hours"> 12 Hour format
        </label>
        <label class="checkbox">
          <input type="checkbox" id="show_wb" checked=""> Show week box
        </label>
        <label class="checkbox">
          <input type="checkbox" id="show_wbn" checked=""> Show week box number
        </label>
      </div>

      <h4>Events</h4>
      <small>This list is populated with events dynamically</small>
      <ul id="eventlist" class="nav nav-list"><li><a href="http://www.example.com/">This is warning class event with very long title to check how it fits to evet in day view</a></li><li><a href="http://www.example.com/">This is information class </a></li><li><a href="http://www.example.com/">Event that ends on timeline</a></li><li><a href="http://www.example.com/">This is special event</a></li><li><a href="http://www.example.com/">This is success event</a></li><li><a href="http://www.example.com/">Short day event</a></li><li><a href="http://www.example.com/">This is simple event</a></li><li><a href="http://www.example.com/">Event 3</a></li><li><a href="http://www.example.com/">This is inverse event</a></li></ul>
    </div>
  </div>
  
   <script type="text/javascript" src="bootstrap-calendar/js/calendar.js"></script>
    <script type="text/javascript" src="bootstrap-calendar/js/app.js"></script>


</body> 

</html>