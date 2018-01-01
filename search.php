<?php
session_start();
include('connection.php');
//define error messages
$missingDeparture = "<p><strong>Please enter your departure!</strong></p>";
$invalidDeparture = "<p><strong>Please enter a valid departure!</strong></p>";
$missingDestination = "<p><strong>Please enter your destination!</strong></p>";
$invalidDestination = "<p><strong>Please enter a valid destination!</strong></p>";

//get all the inputs
$departure = $_POST['departure'];
$destination = $_POST['destination'];

//check departure
if(!$departure){
    $errors .= $missingDeparture;
}else{
    //check coordinate
    if(!isset($_POST['departureLatitude']) or !isset($_POST['departureLongitude'])){
        $errors .= $invalidDeparture;
    }else{
        $departureLatitude = $_POST['departureLatitude'];
        $departureLongitude = $_POST['departureLongitude'];
        $departure = filter_var($departure, FILTER_SANITIZE_STRING);
    }
}
//check destination
if(!$destination){
    $errors .= $missingDestination;
}else{
    //check coordinate
    if(!isset($_POST['destinationLatitude']) or !isset($_POST['destinationLongitude'])){
        $errors .= $invalidDestination;
    }else{
        $destinationLatitude = $_POST['destinationLatitude'];
        $destinationLongitude = $_POST['destinationLongitude'];
        $destination = filter_var($destination, FILTER_SANITIZE_STRING);
    }
}
//if there is an error, print the error message
if($errors){
    $resultMessage = "<div class='alert alert-danger'>$errors</div>";
    echo $resultMessage;
    exit;
}

//set search radius 
$searchRadius = 10;

//longitude out of range
$departureLngOutOfRange = false;
$destinationLngOutOfRange = false;

//minimum and maximum departure longitude
$deltaLongitudeDeparture = $searchRadius*360/(24901*cos(deg2rad($departureLatitude)));
$minLongitudeDeparture = $departureLongitude - $deltaLongitudeDeparture;
if($minLongitudeDeparture<-180){
    $departureLngOutOfRange = true;
    $minLongitudeDeparture += 360;
}
$maxLongitudeDeparture = $departureLongitude + $deltaLongitudeDeparture;
if($maxLongitudeDeparture>180){
    $departureLngOutOfRange = true;
    $maxLongitudeDeparture -= 360;
}

//minimum and maximum destination longitude
$deltaLongitudeDestination = $searchRadius*360/(24901*cos(deg2rad($destinationLatitude)));
$minLongitudeDestination = $destinationLongitude - $deltaLongitudeDestination;
if($minLongitudeDestination<-180){
    $destinationLngOutOfRange = true;
    $minLongitudeDestination += 360;
}
$maxLongitudeDestination = $destinationLongitude + $deltaLongitudeDestination;
if($maxLongitudeDestination>180){
    $destinationLngOutOfRange = true;
    $maxLongitudeDestination -= 360;
}

//min and max of departure latitude
$deltaLatitudeDeparture = $searchRadius*180/12430;
$minLatitudeDeparture = $departureLatitude - $deltaLatitudeDeparture;
if($minLatitudeDeparture<-90){
    $minLatitudeDeparture = -90;
}
$maxLatitudeDeparture = $departureLatitude + $deltaLatitudeDeparture;
if($maxLatitudeDeparture>90){
    $maxLatitudeDeparture = 90;
}

//min and max of destination latitude
$deltaLatitudeDestination = $searchRadius*180/12430;
$minLatitudeDestination = $destinationLatitude - $deltaLatitudeDestination;
if($minLatitudeDestination<-90){
    $minLatitudeDestination = -90;
}
$maxLatitudeDestination = $destinationLatitude + $deltaLatitudeDestination;
if($maxLatitudeDestination>90){
    $maxLatitudeDestination = 90;
}

//build query
$sql = "SELECT * FROM carsharetrips WHERE ";
//departure longitude
if($departureLngOutOfRange){
    $sql .= "((departureLongitude > '$minLongitudeDeparture') OR (departureLongitude > $maxLongitudeDeparture))";
}else{
    $sql .= "(departureLongitude BETWEEN $minLongitudeDeparture AND $maxLongitudeDeparture)";
}

//departure latitude
$sql .= " AND (departureLatitude BETWEEN $minLatitudeDeparture AND $maxLatitudeDeparture)";

//destination longitude
if($destinationLngOutOfRange){
    $sql .= " AND ((destinationLongitude > '$minLongitudeDestination') OR (destinationLongitude > $maxLongitudeDestination))";
}else{
    $sql .= " AND (destinationLongitude BETWEEN $minLongitudeDestination AND $maxLongitudeDestination)";
}

//destination latitude
$sql .= " AND (destinationLatitude BETWEEN $minLatitudeDestination AND $maxLatitudeDestination)";

//run query
$result = mysqli_query($link, $sql);
if(!$result){
    echo "ERROR: Unable to execute: $sql. " . mysqli_error($link);
    exit;
}

if(mysqli_num_rows($result)==0){
    echo "<div class='alert alert-info noresults'>There are no journeys matching your search!</div>";
    exit;
}
echo "<div class='alert alert-info journeysummary'>From $departure to $destination. <br />Closest Journeys: </div>";

echo "<div id='tripResults'>";
//cycle through the trips
while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){
    //get trip details
    //check frequency
    if($row['regular']=='N'){
        //one off journeys
        $frequency = "One-off journey.";
        $time = $row['date'] .  " at " . $row['time'] . ".";
        //check the date 
        $source = $row['date'];
        $tripDate = DateTime::createFromFormat('D d M, Y', $source);
        $today = date('D d M, Y');
        $todayDate = DateTime::createFromFormat('D d M, Y', $today);
        if($tripDate < $todayDate){
            continue;
        }
    }else{
        $frequency = "Regular.";
        
        $array = [];
        $weekdays = ['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun'];
        foreach($weekdays as $key => $value){
            if($row[$key] == 1){
                array_push($array, $value);
            }
        }
        $time = implode("-", $array) .  " at " . $row['time'] . ".";//return the concatenated string of the array
    }
    $tripDeparture = $row['departure'];
    $tripDestination = $row['destination'];
    $price = $row['price'];
    $seatsAvailable = $row['seatsavailable'];
    
    //get user_id
    $person_id = $row['user_id'];
    //run query to get user details
    $sql2 = "SELECT * FROM users WHERE user_id = '$person_id' LIMIT 1";
    $result2 = mysqli_query($link, $sql2);
    
    if(!$result2){
        echo "ERROR: Unable to execute: $sql. " . mysqli_error($link);
        exit;
    }
    $row2 = mysqli_fetch_array($result2,MYSQLI_ASSOC);
    $firstname = $row2['first_name'];
    $gender = $row2['gender'];
    $moreInformation = $row2['moreinformation'];
    $picture = $row2['profilepicture'];
    if($_SESSION['user_id']){
        $phonenumber = $row2['phonenumber'];
    }else{
        $phonenumber = "Please sign up! Only members have access to contact information.";
    }
    //print trip
    echo 
        "
        <h4 class='row'>
            <div class='col-sm-2'>
                    <div class='driver'>$firstname</div>
                    <div><img class='profile' src='$picture'$ /></div>
            </div>
            <div class='col-sm-8 journey'>
                    <div><span class='departure'>Departure</span>: $tripDeparture.</div>
                    <div><span class='destination'>Destination</span>: $tripDestination.</div>
                    <div class='time'>$time</div>
                    <div>$frequency</div>
            </div>
            <div class='col-sm-2 journey2'>
                    <div class='price'>$$price</div>
                    <div class='perseat'>Per Seat</div>
                    <div class='seatsAvailable'>$seatsAvailable left</div>
            </div>
        </h4>
        <div class='moreinfo'>
            <div>
                <div>Gender: $gender</div>
                <div>&#9742: $phonenumber</div>
            </div>
            <div class='aboutme'>About me: $moreInformation</div>
        </div>
        ";
}


echo "</div>";













?>