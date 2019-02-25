<?php

    session_start();

    if(isset($_GET['logout'])){
        session_destroy();
        unset($_SESSION['username']);
        header('location: Login.php');
    }
    
    //make sure that the user is logged in
    if(!isset($_SESSION['username']))
    {
        $_SESSION['msg']="You must log in first";
        header('location: Login.php');
    }
    else{
    // confirm that the user is a client
    if( $_SESSION['type'] != 'admin' )
    {
        // if it's an admin
        if( $_SESSION['type'] == 'client' )
            header("Location: clientLanding.php");
        else if( $_SESSION['type'] == 'hotel' )
            header("Location: hotelLanding.php");
         else
            // redirect to the login page if there is no active session
            header("Location: Login.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
    <script src="main.js"></script>
    <style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>

<?php

$username="";
$email="";
$_SESSION['success']="";
$error_flag=false;

echo "<h1>Administrator Home page</h1>";
//connect to databse
$dataBaseConnection=mysqli_connect('localhost', 'root', '', 'hotelreservation');

//if a hotel is selected to be suspended
 if(isset($_GET['suspendFlag'])){
    
        if($_GET['suspendFlag'] == 1){
        
               $varHotelId = $_GET['hotelId'];
               $varHotelEmail = $_GET['hotelEmail'];
               $varHotelName = $_GET['hotelName'];
               $varHotelLocation = $_GET['hotelLocation'];
               $varHotelStars = $_GET['hotelStars'];
               $varHotelRating = $_GET['hotelRating'];
               $varHotelMoney = $_GET['hotelMoney'];
           //    $insertionIntoSuspendedHotelsQuery ="INSERT INTO suspendedhotel(id,name,email,location,stars,rating,moneyOwed)
          //                                          VALUES ('$varHotelId','$varHotelName','$varHotelEmail','$varHotelLocation','$varHotelStars','$varHotelRating','$varHotelMoney')";
   
         //      $deletionFromActiveHotelsQuery ="DELETE FROM hotel WHERE email ='$varHotelEmail'";        
   
                $modifysuspendedHotel = "UPDATE hotel
                                         SET suspended = '1'
                                         WHERE id= '$varHotelId'";
                mysqli_query($dataBaseConnection,$modifysuspendedHotel);
               //insert the selected hotel to the suspended hotels table
           //    mysqli_query($dataBaseConnection,$insertionIntoSuspendedHotelsQuery);
              //delete the selected hotel from hotel table after inserting in the suspension table
          //     mysqli_query($dataBaseConnection,$deletionFromActiveHotelsQuery);
    }
   }

//if a hotel is selected  from suspension table
if(isset($_GET['unSuspendFlag'])){
    //if the Admin Clicks Unsuspend , he change the value of unSuspendFlag to 1
        if($_GET['unSuspendFlag'] == 1){

               $varHotelId = $_GET['hotelId'];
               $varHotelEmail = $_GET['hotelEmail'];
               $varHotelName = $_GET['hotelName'];
               $varHotelLocation = $_GET['hotelLocation'];
               $varHotelStars = $_GET['hotelStars'];
               $varHotelRating = $_GET['hotelRating'];

               //we set Money owed by hotel by Zero as Hotel is unsuspeded only when paying all dues.
        //       $insertionIntoActiveHotelsQuery ="INSERT INTO hotel(id,name,email,location,stars,rating,moneyOwed)
        //                                        VALUES (' $varHotelId','$varHotelName','$varHotelEmail','$varHotelLocation','$varHotelStars','$varHotelRating',0) ";
        //       $deletionFromSuspendedHotelsQuery ="DELETE FROM suspendedhotel WHERE email ='$varHotelEmail'";        
                $modifyUnsuspendedHotel = "UPDATE hotel
                                    SET suspended = '0'
                                    WHERE id= '$varHotelId'";
                mysqli_query($dataBaseConnection,$modifyUnsuspendedHotel);
   
   
        //       mysqli_query($dataBaseConnection,$insertionIntoActiveHotelsQuery);
        //       mysqli_query($dataBaseConnection,$deletionFromSuspendedHotelsQuery);
    }
   }

//if the request of hotel is rejected
if(isset($_GET['rejectFlag'])){
        //if the Admin Clicks Unsuspend , he change the value of unSuspendFlag to 1
        if($_GET['rejectFlag'] == 1){
       
               $varHotelEmail = $_GET['hotelEmail'];
       
               //if the pending request of hotel is rejected then hotel is deleted from waitinghotel
               $deletionFromWaitingHotelsQuery ="DELETE FROM waitinghotel WHERE email ='$varHotelEmail'";
               mysqli_query($dataBaseConnection,$deletionFromWaitingHotelsQuery);
       
      
        }
}

//if the request of the hotel is accepted 
if(isset($_GET['approveFlag'])){
 
        if($_GET['approveFlag'] == 1){
   
               $varWaitingHotelId = $_GET['hotelId'];
               $varHotelEmail = $_GET['hotelEmail'];
               $varHotelName = $_GET['hotelName'];
               $varHotelLocation = $_GET['hotelLocation'];
               $varHotelStars = $_GET['hotelStars'];
               $varHotelRating = $_GET['hotelRating'];
               $varHotelPassword = $_GET['hotelPassword'];
   
               $insertionIntoActiveHotelsQuery ="INSERT INTO hotel(name,email,location,stars,rating,moneyOwed,password)
                                                 VALUES ('$varHotelName','$varHotelEmail','$varHotelLocation','$varHotelStars','$varHotelRating',0,'$varHotelPassword')";
                mysqli_query($dataBaseConnection,$insertionIntoActiveHotelsQuery);
            
            //Get the id of the recently added hotel for the insertion of it's rooms and facilities
            $recentlyAddedHotelId = mysqli_query($dataBaseConnection,"SELECT * 
                                                                      FROM hotel
                                                                      WHERE email='$varHotelEmail'");
            $rowRecentlyAddedHotelId = mysqli_fetch_assoc($recentlyAddedHotelId);
            $varHotelId = $rowRecentlyAddedHotelId['id'];

            //Get the attributes of waiting rooms
            $attibutesOfWaitingRoom = mysqli_query($dataBaseConnection, "SELECT *
                                                                        FROM waitingroom
                                                                        WHERE hotelId='$varWaitingHotelId'");
            while($rowOfRoomAttributes = mysqli_fetch_assoc($attibutesOfWaitingRoom))
            {
                $tempRoomType = $rowOfRoomAttributes['type'];
                $tempRoomPrice = $rowOfRoomAttributes['price'];
            //    $tempRoomCount = $rowOfRoomAttributes['count'];
                //$tempRoomImage = $rowOfRoomAttributes ['imageUrl'];

               mysqli_query($dataBaseConnection,"INSERT INTO room(hotelId,type,price)
                                                  VALUES ('$varHotelId','$tempRoomType','$tempRoomPrice')");
                
            }

            //GET the attributes of waiting facilities
            $attibutesOfWaitingFacilities = mysqli_query($dataBaseConnection, "SELECT *
                                                                        FROM waitingfacilities
                                                                        WHERE hotelId='$varWaitingHotelId'");
            $rowOfFacilities = mysqli_fetch_assoc($attibutesOfWaitingFacilities);
            
            $tempSpa = $rowOfFacilities['spa'];
            $tempBeach = $rowOfFacilities['beach'];
            $tempSwimmingPool = $rowOfFacilities['swimmingPool'];
            $tempGYM = $rowOfFacilities['GYM'];

           mysqli_query($dataBaseConnection,"INSERT INTO facilities(hotelId,spa,beach,swimmingPool,GYM)
                                             VALUES('$varHotelId','$tempSpa','$tempBeach','$tempSwimmingPool','$tempGYM')");
            
            // remove it from waitinghotel table
            $deletionFromWaitingHotelsQuery ="DELETE FROM waitinghotel WHERE email ='$varHotelEmail'";
           mysqli_query($dataBaseConnection,$deletionFromWaitingHotelsQuery);
   
    }
   }
   








//if the button of show hotels is pressed will enter if
 if(isset($_POST['showHotels'])){

         $ActivehotelsDataSelection = "SELECT * FROM hotel 
                                       WHERE suspended = '0'";
         $resultActiveHotels = mysqli_query($dataBaseConnection, $ActivehotelsDataSelection);

        echo "<h2>Active Hotels</h2>";
        echo "<table>
        <tr>
        <th>Hotel ID</th>
        <th>Name</th>
        <th>E-mail</th>
        <th>Location</th>
        <th>Stars</th>
        <th>Rating</th>
        <th>Money Due</th>
        <th>Premium</th>
        <th>Action</th>
        </tr>
        <tr>";

        while($row = mysqli_fetch_assoc($resultActiveHotels)){

                 echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['location'] . "</td>";
                echo "<td>" . $row['stars'] . "</td>";
                echo "<td>" . $row['rating'] . "</td>";
                echo "<td>" . $row['moneyOwed'] . "</td>";
                echo "<td>" . $row['premium'] . "</td>";
                echo "<td><a href='adminLanding.php?suspendFlag=1&hotelEmail=".$row['email']."&hotelLocation=".$row['location']."&hotelStars=".$row['stars']."&hotelRating=".$row['rating']."&hotelName=".$row['name']."&hotelId=".$row['id']."&hotelMoney=".$row['moneyOwed']."'>Suspend</a>";
                echo "</tr>";
        }
        echo "</table>";

  }
//if the unsuspend button is pressed enter if
  else if(isset($_POST['showSuspendHotels'])){

        $SuspendedhotelsDataSelection = "SELECT * FROM hotel
                                         WHERE suspended ='1'";
        $resultSuspendedHotels = mysqli_query($dataBaseConnection, $SuspendedhotelsDataSelection);

        echo "<h2>Suspended Hotels</h2>";
        echo "<table>
        <tr>
        <th>Hotel ID</th>
        <th>Name</th>
        <th>E-mail</th>
        <th>Location</th>
        <th>Stars</th>
        <th>Rating</th>
        <th>Money Due</th>
        <th>Premium</th>
        <th>Action</th>
        </tr>
        <tr>";

        while($row = mysqli_fetch_assoc($resultSuspendedHotels)){

            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['location'] . "</td>";
            echo "<td>" . $row['stars'] . "</td>";
            echo "<td>" . $row['rating'] . "</td>";
            echo "<td>" . $row['moneyOwed'] . "</td>";
            echo "<td>" . $row['premium'] . "</td>";
            echo "<td><a href='adminLanding.php?unSuspendFlag=1&hotelEmail=".$row['email']."&hotelLocation=".$row['location']."&hotelStars=".$row['stars']."&hotelRating=".$row['rating']."&hotelName=".$row['name']."&hotelId=".$row['id']."'>Un-Suspend</a>";
            echo "</tr>";
        }
        echo "</table>";

  }

//if the show waiting hotels is pressed enter if
  else if(isset($_POST['showWaitingHotels'])){


        $waitingHotelsDataSelection = "SELECT * FROM waitinghotel";
        $resultWaitingHotels = mysqli_query($dataBaseConnection, $waitingHotelsDataSelection);

        echo "<h2>Waiting Hotels</h2>";
        echo "<table>
        <tr>
        <th>Hotel ID</th>
        <th>Name</th>
        <th>E-mail</th>
        <th>Location</th>
        <th>Stars</th>
        <th>Rating</th>
        <th>Money Due</th>
        <th>Premium</th>
        <th>Action</th>
        </tr>
        <tr>";

        while($row = mysqli_fetch_assoc($resultWaitingHotels)){

            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['location'] . "</td>";
            echo "<td>" . $row['stars'] . "</td>";
            echo "<td>" . $row['rating'] . "</td>";
            echo "<td>" . $row['moneyOwed'] . "</td>";
            echo "<td>" . $row['premium'] . "</td>";
            echo "<td><a href='adminLanding.php?approveFlag=1&hotelEmail=".$row['email']."&hotelLocation=".$row['location']."&hotelStars=".$row['stars']."&hotelRating=".$row['rating']."&hotelName=".$row['name']."&hotelId=".$row['id']."&hotelPassword=".$row['password']."'>Approve</a></br>
                <a href='adminLanding.php?rejectFlag=1&hotelEmail=".$row['email']."&hotelLocation=".$row['location']."&hotelStars=".$row['stars']."&hotelRating=".$row['rating']."&hotelName=".$row['name']."'>Reject</a>";
            echo "</tr>";
            }
        echo "</table>";


  }


 

 

?>



<body>
    

    <form method="post" action="adminLanding.php">

    <button type="submit" name="showHotels">Show hotels</button>
    <button type="submit" name="showSuspendHotels">Show suspended Hotels</button>
    <button type="submit" name="showWaitingHotels">Show waiting Hotels</button>
    <p> <a href="adminLanding.php?logout='1'" style="color: red;">logout</a> </p>
    </form>
</body>
</html>

