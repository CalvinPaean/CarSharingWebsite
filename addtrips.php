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
$departure = $_POST['departure'];
$destination = $_POST['destination'];
$price = $_POST['price'];
$seatsavailable = $_POST['seatsavailable'];
$regular = $_POST['regular'];
$date = $_POST['date'];
$time = $_POST['time'];
$monday = $_POST['monday'];
$tuesday = $_POST['tuesday'];
$wednesday = $_POST['wednesday'];
$thursday = $_POST['thursday'];
$friday = $_POST['friday'];
$saturday = $_POST['saturday'];
$sunday = $_POST['sunday'];


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
        $sql = "INSERT INTO $tblName (`user_id`, `departure`, `departureLongitude`, `departureLatitude`, `destination`, `destinationLongitude`, `destinationLatitude`, `price`, `seatsavailable`, `regular`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `time`) VALUES ('$user_id', '$departure', '$departureLongitude', '$departureLatitude', '$destination', '$destinationLongitude', '$destinationLatitude', '$price', '$seatsavailable', '$regular', '$monday', '$tuesday', '$wednesday', '$thursday', '$friday', '$saturday', '$sunday','$time')";
    }else{
        //query for a one-off trip
        $sql = "INSERT INTO $tblName (`user_id`, `departure`, `departureLongitude`, `departureLatitude`, `destination`, `destinationLongitude`, `destinationLatitude`, `price`, `seatsavailable`, `regular`, `date`, `time`) VALUES ('$user_id', '$departure', '$departureLongitude', '$departureLatitude', '$destination', '$destinationLongitude', '$destinationLatitude', '$price', '$seatsavailable', '$regular', '$date', '$time')";
    }
    $result = mysqli_query($link, $sql);
    //check if query is successful
    if(!$result){
        echo "<div class='alert alert-danger'>There was an error! The trip could not be added to the database.</div>";
    }
}






















?>