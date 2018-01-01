<?php
$link = mysqli_connect("localhost", "laniakea_caruser", "!=_-4iZmDZzF", "laniakea_carshare_database");
if(mysqli_connect_error()){
    die('ERROR: Unable to connect:' . mysqli_connect_error()); 
    echo "<script>window.alert('Hi!')</script>";
}
    ?>