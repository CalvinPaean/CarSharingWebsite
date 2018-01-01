<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("location: index.php");
}
include('connection.php');

$user_id = $_SESSION['user_id'];

//get username and email
$sql = "SELECT * FROM users WHERE user_id='$user_id'";
$result = mysqli_query($link, $sql);

$count = mysqli_num_rows($result);

if($count == 1){
    $row = mysqli_fetch_array($result, MYSQL_ASSOC); 
    $username = $row['username'];
    $email = $row['email']; 
    $picture = $row['profilepicture'];
}else{
    echo "There was an error retrieving the username and email from the database";   
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Trips</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="styling.css" rel="stylesheet">
      <link href='https://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD9z0rVn5qrtpGWIhd5h8KsU5Gpd0eqapo&libraries=places"></script>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/dot-luv/jquery-ui.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
      <style>
        #container{
            margin-top:120px;   
        }

        #notePad, #allNotes, #done, .delete{
            display: none;   
        }

        .buttons{
            margin-bottom: 20px;   
        }

        textarea{
            width: 100%;
            max-width: 100%;
            font-size: 16px;
            line-height: 1.5em;
            border-left-width: 20px;
            border-color: #CA3DD9;
            color: #CA3DD9;
            background-color: #FBEFFF;
            padding: 10px;
              
        }
        
        .noteheader{
            border: 1px solid grey;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            padding: 0 10px;
            background: linear-gradient(#FFFFFF,#ECEAE7);
        }
          
        .text{
            font-size: 20px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
          
        .timetext{
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .notes{
            margin-bottom: 100px;
        }
          /*show the dropdown menu of autocomplete in the modal of google map*/
          .modal{
              z-index: 20;
          }
          .modal-backdrop{
              z-index: 10;
          }
          #googleMap{
              width: 300px;
              height: 200px;
              margin: 30px auto;
          }
          .time{
              margin-top: 10px;
          }
          .trip{
              border: 1px solid grey;
              padding: 10px;
              border-radius: 10px;
              margin: 10px auto;
              color:#065279;
              background:linear-gradient(#E9E7EF, #F3F9F1);
          }
          .departure, .destination{
              font-size: 1.5em;
          }
          .price{
              font-size: 2em;
          }
          .seatsAvailable{
              font-size: 1.5em;
          }
          #myTrips{
              margin-top: 20px;
              margin-bottom: 100px;
          }
          .preview{
              height: 20px;
              border-radius: 50%;
          }
      </style>
  </head>
  <body>
    <!--Navigation Bar-->  
      <nav role="navigation" class="navbar navbar-custom navbar-fixed-top">
      
          <div class="container-fluid">
            
              <div class="navbar-header">
              
                  <a class="navbar-brand">Car Sharing</a>
                  <button type="button" class="navbar-toggle" data-target="#navbarCollapse" data-toggle="collapse">
                      <span class="sr-only">Toggle navigation</span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                  
                  </button>
              </div>
              <div class="navbar-collapse collapse" id="navbarCollapse">
                  <ul class="nav navbar-nav">
                    <li><a href="index.php">Search</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="#">Help</a></li>
                    <li><a href="#">Contact us</a></li>
                      <li class="active"><a href="#">My Trips</a></li>
                  </ul>
                  <ul class="nav navbar-nav navbar-right">
                      <li><a href="#">
                          <div data-toggle="modal" data-target="#updatepicture">
                            <?php
                                if(empty($picture)){
                                    echo "<img class='preview' src='profilepicture/car.jpg'/>";
                                }else{
                                    echo "<img class='preview' src='$picture'/>";
                                }
                            ?>
                          </div></a></li>
                      <li><a href="#"><?php echo $username; ?></a></li>
                    <li><a href="index.php?logout=1">Log out</a></li>
                  </ul>
              
              </div>
          </div>
      
      </nav>
    
<!--Container-->
      <div class="container" id="container">
          <div class="row">
              <div class="col-sm-8 col-sm-offset-2">
                  <div>
                      <button type="button" class="btn btn-lg green" data-toggle="modal" data-target="#addtripModal">Add trips</button>
                      
                  </div>
                  <div id="myTrips" class="trips">
                      <!--Ajax call to PHP file-->
                  </div>
              </div>
          </div>
      </div>

      <!--Add Trip Form--> 
      <form method="post" id="addtripform">
        <div class="modal" id="addtripModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal">
                    &times;
                  </button>
                  <h4 id="myModalLabel">
                    New Trip: 
                  </h4>
              </div>
              <div class="modal-body">
                  
                  <!--Add trip message from PHP file-->
                  <div id="addtripmessage"></div>
                  <!--Google Map-->
                  <div id="googleMap"></div>
                  
                  <div class="form-group">
                      <label for="departure" class="sr-only">Departure:</label>
                      <input class="form-control" type="text" name="departure" id="departure" placeholder="Departure">
                  </div>
                  <div class="form-group">
                      <label for="destination" class="sr-only">Destination:</label>
                      <input class="form-control" type="text" name="destination" id="destination" placeholder="Destination">
                  </div>
                  <div class="form-group">
                      <label for="price" class="sr-only">Price:</label>
                      <input class="form-control" type="number" name="price" id="price" placeholder="Price">
                  </div>
                  <div class="form-group">
                      <label for="seatsavailable2" class="sr-only">Price:</label>
                      <input class="form-control" type="number" name="seatsavailable" id="seatsavailable" placeholder="Seats Available">
                  </div>
                  <div class="form-group">
                      <label><input type="radio" name="regular" id="yes" value="Y">Regular</label>
                      <label><input type="radio" name="regular" id="no" value="N">One-off</label>
                  </div>
                  <div class="checkbox checkbox-inline regular">
                      <label><input type="checkbox" name="monday" id="monday" value="1">Monday</label>
                      <label><input type="checkbox" name="tuesday" id="tuesday" value="">Tuesday</label>
                      <label><input type="checkbox" name="wednesday" id="wednesday" value="1">Wednesday</label>
                      <label><input type="checkbox" name="thursday" id="thursday" value="1">Thursday</label>
                      <label><input type="checkbox" name="friday" id="friday" value="1">Friday</label>
                      <label><input type="checkbox" name="saturday" id="saturday" value="1">Saturday</label>
                      <label><input type="checkbox" name="sunday" id="sunday" value="1">Sunday</label>
                  </div>
                  <div class="form-group one-off">
                      <label for="date" class="sr-only">Date:</label>
                      <input class="form-control" name="date" id="date" readonly="readonly">
                  </div>
                  <div class="form-group one-off regular time">
                      <label for="time" class="sr-only">Time:</label>
                      <input class="form-control" type="time" name="time" id="time">
                  </div>
              </div>
              <div class="modal-footer">
                <input class="btn btn-primary" name="createTrip" type="submit" value="Create Trip">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
              </div>
          </div>
      </div>
      </div>
      </form>
      
      <!--Edit Trip Form--> 
      <form method="post" id="edittripform">
        <div class="modal" id="edittripModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal">
                    &times;
                  </button>
                  <h4 id="myModalLabel">
                    Edit Trip: 
                  </h4>
              </div>
              <div class="modal-body">
                  
                  <!--Edit trip message from PHP file-->
                  <div id="edittripmessage"></div>
                  
                  
                  <div class="form-group">
                      <label for="departure2" class="sr-only">Departure:</label>
                      <input class="form-control" type="text" name="departure2" id="departure2" placeholder="Departure">
                  </div>
                  <div class="form-group">
                      <label for="destination2" class="sr-only">Destination:</label>
                      <input class="form-control" type="text" name="destination2" id="destination2" placeholder="Destination">
                  </div>
                  <div class="form-group">
                      <label for="price2" class="sr-only">Price:</label>
                      <input class="form-control" type="number" name="price2" id="price2" placeholder="Price">
                  </div>
                  <div class="form-group">
                      <label for="seatsavailable2" class="sr-only">Price:</label>
                      <input class="form-control" type="number" name="seatsavailable2" id="seatsavailable2" placeholder="Seats Available">
                  </div>
                  <div class="form-group">
                      <label><input type="radio" name="regular2" id="yes2" value="Y">Regular</label>
                      <label><input type="radio" name="regular2" id="no2" value="N">One-off</label>
                  </div>
                  <div class="checkbox checkbox-inline regular2">
                      <label><input type="checkbox" name="monday2" id="monday2" value="1">Monday</label>
                      <label><input type="checkbox" name="tuesday2" id="tuesday2" value="">Tuesday</label>
                      <label><input type="checkbox" name="wednesday2" id="wednesday2" value="1">Wednesday</label>
                      <label><input type="checkbox" name="thursday2" id="thursday2" value="1">Thursday</label>
                      <label><input type="checkbox" name="friday2" id="friday2" value="1">Friday</label>
                      <label><input type="checkbox" name="saturday2" id="saturday2" value="1">Saturday</label>
                      <label><input type="checkbox" name="sunday2" id="sunday2" value="1">Sunday</label>
                  </div>
                  <div class="form-group one-off2">
                      <label for="date2" class="sr-only">Date:</label>
                      <input class="form-control" name="date2" id="date2" readonly="readonly">
                  </div>
                  <div class="form-group one-off2 regular2 time">
                      <label for="time2" class="sr-only">Time:</label>
                      <input class="form-control" type="time" name="time2" id="time2">
                  </div>
              </div>
              <div class="modal-footer">
                <input class="btn btn-primary" name="updateTrip" type="submit" value="Edit Trip">
                <input class="btn btn-danger" name="deleteTrip" id="deleteTrip" type="button" value="Delete">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
              </div>
          </div>
      </div>
      </div>
      </form>
        <!-- Footer-->
      <div class="footer">
          <div class="container">
              <p>Laniakea Copyright &copy; 2016-<?php $today = date("Y"); echo $today?>.</p>
          </div>
      </div>
      <!--Spinner-->
      <div id="spinner">
         <img src='ajax-loader.gif' width="64" height="64" />
         <br>Loading..
      </div>

    
      <script src="map.js"></script>
    <script src="mytrips.js"></script>  
  </body>
</html>