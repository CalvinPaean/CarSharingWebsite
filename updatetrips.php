<?php
session_start();
include("connection.php");
//define error messages
$missingDeparture = "<p><strong>Please enter your departure!</strong></p>";
$invalidDeparture = "<p><strong>Please enter a valid departure!</strong></p>";
$missingDestination = "<p><strong>Please enter your destination!</strong></p>";
$invalidDestination = "<p><strong>Please enter a valid destination!</strong></p>";
$missingPrice = "<p><strong>Please choose a price per seat!</strong></p>";
$invalidPrice = "<p><strong>Please choose a valid price per seat using numbers only!</strong></p>";
$missingSeatsavailable = "<p><strong>Please select the number of available seats!</strong></p>";
$invalidSeatsavailable = "<p><strong>The number of available seats should contain digits only!</strong></p>";
$missingFrequency = "<p><strong>Please select a frequency!</strong></p>";
$missingDays = "<p><strong>Please select at least one weekday!</strong></p>";
$missingDate = "<p><strong>Please choose a date for your trip!</strong></p>";
$missingTime = "<p><strong>Please choose a time for your trip!</strong></p>";


//get all the inputs
$trip_id = $_POST['trip_id'];
$departure = $_POST['departure2'];
$destination = $_POST['destination2'];
$price = $_POST['price2'];
$seatsavailable = $_POST['seatsavailable2'];
$regular = $_POST['regular2'];
$date = $_POST['date2'];
$time = $_POST['time2'];
$monday = $_POST['monday2'];
$tuesday = $_POST['tuesday2'];
$wednesday = $_POST['wednesday2'];
$thursday = $_POST['thursday2'];
$friday = $_POST['friday2'];
$saturday = $_POST['saturday2'];
$sunday = $_POST['sunday2'];


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

//check price
if(!($price)){
    $errors .= $missingPrice;
}elseif(preg_match("/\D/", $price)){
    $errors .= $invalidPrice;
}else{
    $price = filter_var($price, FILTER_SANITIZE_STRING);
}

//check seatsavailable
if(!($seatsavailable)){
    $errors .= $missingSeatsavailable;
}elseif(preg_match("/\D/", $seatsavailable)){
    $errors .= $invalidSeatsavailable;
}else{
    $seatsavailable = filter_var($seatsavailable, FILTER_SANITIZE_STRING);
}

//check frequency
if(!($regular)){
    $errors .= $missingFrequency;
}elseif($regular == "Y"){
    if(!($monday) && !($tuesday) &&!($wednesday) &&!($thursday) &&!($friday) &&!($saturday) &&!($sunday)){
        $errors .= $missingDays;
    }
    if(!($time)){
        $errors .= $missingTime;
    }
}else{
    if(!($date)){
        $errors .= $missingDate;
    }
    if(!($time)){
        $errors .= $missingTime;
    }
}
//if there is an error, print the error message
if($errors){
    $resultMessage = "<div class='alert alert-danger'>$errors</div>";
    echo $resultMessage;
}else{
    //no errors, prepare variables to the query
    $departure = mysqli_real_escape_string($link, $departure);
    $destination = mysqli_real_escape_string($link, $destination);
    $tblName = 'carsharetrips';
    $user_id = $_SESSION['user_id'];
    if($regular == "Y"){
        //query for a regular trip
        $sql = "UPDATE $tblName SET `departure`='$departure', `departureLongitude`='$departureLongitude', `departureLatitude`='$departureLatitude',`destination`='$destination',`destinationLongitude`='$destinationLongitude',`destinationLatitude`='$destinationLatitude',`price`='$price',`seatsavailable`='$seatsavailable',`regular`='$regular', `monday`='$monday', `tuesday`='$tuesday', `wednesday`='$wednesday', `thursday`='$thursday', `friday`='$friday', `saturday`='$saturday', `sunday`='$sunday', `time`='$time' WHERE `trip_id`='$trip_id' LIMIT 1";
    }else{
        //query for a one-off trip
        $sql = "UPDATE $tblName SET `departure`='$departure', `departureLongitude`='$departureLongitude', `departureLatitude`='$departureLatitude',`destination`='$destination',`destinationLongitude`='$destinationLongitude',`destinationLatitude`='$destinationLatitude',`price`='$price',`seatsavailable`='$seatsavailable',`regular`='$regular', `date`='$date', `time`='$time' WHERE `trip_id`='$trip_id' LIMIT 1";
    }
    $result = mysqli_query($link, $sql);
    //check if query is successful
    if(!$result){
        echo "<div class='alert alert-danger'>There was an error! The trip could not be updated!</div>";
    }
}



?>