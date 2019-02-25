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
    if( $_SESSION['type'] != 'client' )
    {
        // if it's an admin
        if( $_SESSION['type'] == 'hotel' )
            header("Location: hotelLanding.php");
        else if( $_SESSION['type'] == 'admin' )
            header("Location: adminLanding.php");
         else
            // redirect to the login page if there is no active session
            header("Location: Login.php");
    }
}
?>

<<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Client Home page</title>
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

<body>

<?php
$username="";
$email="";
$_SESSION['success']="";
$error_flag=false;

//connect to databse
$db=mysqli_connect('localhost', 'root', '', 'hotelreservation');

echo "<h1>Welcome ".$_SESSION['username']."</h1>";

if(isset($_GET['reserve'])){

        if($_GET['reserve'] == 1){

            //Make the reservation by inserting it into waitingreservation for hotel approval
            $varRoomId = $_GET['roomId'];
            $varClientId = $_GET['clientId'];
            $varCheckIn = $_GET['checkIn'];
            $varCheckOut = $_GET['checkOut'];
            $varTotalPrice = $_GET['totalPrice'];
            mysqli_query($db, "INSERT INTO waitingreservation(roomId,clientId,checkInDate,checkOutDate,price)
                               VALUES('$varRoomId','$varClientId','$varCheckIn','$varCheckOut','$varTotalPrice')");
           
           //get the value of count of the choosen room and decrement it by one "is incremented again if the hotel reject the reservation"
           if(isset($_GET['noDecrement'])){
               if($_GET['noDecrement'] == 0){
                       $resultCount = mysqli_query($db,"SELECT count
                                                        FROM room
                                                        Where id ='$varRoomId'");
                        $rowCount = mysqli_fetch_assoc($resultCount);
                        $varCount = $rowCount['count'];
                        $varCount = $varCount -1;
                        //Update the count value in the room table
                        mysqli_query($db,"UPDATE room
                                          SET count = '$varCount'
                                          WHERE id = '$varRoomId'");
               }
           }
        }
}


//--------------------------------------------------------------------cancel
if(isset($_GET['cancel'])){

    //cancel a reseravtion from waitingreservation table
    if($_GET['cancel'] == 1){

        $varClientId = $_GET['clientId'];
        $varRoomId = $_GET['roomId'];
        $varCheckInDate = $_GET['checkInDate'];

        mysqli_query($db,"DELETE FROM waitingreservation
                          WHERE roomId='$varRoomId' AND clientId='$varClientId' AND checkInDate='$varCheckInDate'");

    }
    else if($_GET['cancel'] == 2){
        $varClientId = $_GET['clientId'];
        $varRoomId = $_GET['roomId'];
        $varCheckInDate = $_GET['checkInDate'];

        mysqli_query($db,"DELETE FROM reservation
                          WHERE roomId='$varRoomId' AND clientId='$varClientId' AND checkInDate='$varCheckInDate'");
    }


}

//----------------------------------------------------------------RATE

    if(isset($_POST['rateNow'])){
        $valueRate = $_POST['rate'];
        $varClientId = $_SESSION['clientId'];
        $varHotelId = $_SESSION['hotelRateId'];

        mysqli_query($db,"INSERT INTO rating(clientId,hotelId,rating)
                          VALUES ('$varClientId','$varHotelId','$valueRate')");
        
        $temp = mysqli_query($db,"SELECT AVG(rating) as avg
                                  FROM rating
                                  WHERE hotelId='$varHotelId'
                                  GROUP BY hotelId");
        $tempRate = mysqli_fetch_assoc($temp);
        $varRate = $tempRate['avg'];
        
        mysqli_query($db,"UPDATE hotel
                         SET rating='$varRate'
                         WHERE id='$varHotelId'");
    }

  





if(isset($_POST['searchFor'])){

    //GET the check in date and out date
    $varCheckin = $_POST['checkInDate'];
    $varCheckout = $_POST['checkOutDate'];
    if(isset($_POST['location']) && $_POST['location'] != ""){

        $varLocation = $_POST['location'];
    //    $query.=" AND hotel.location='$varLocation'";
    }
//GET all available rooms that can be reserved whether it is already reserved or not
    $query = "SELECT roomId,hotel.name as hotelName,location,stars,rating,type,price
              FROM(
                    SELECT roomId,hotelId,type,price
                    FROM(
                            SELECT DISTINCT roomId
                            FROM (
                                    SELECT reservation.roomId
                                    FROM ( 
                                            room INNER JOIN reservation ON room.id = reservation.roomId
                                        )
                                    WHERE reservation.roomId NOT IN(
                                                                        SELECT roomId
                                                                         FROM reservation
                                                                        WHERE ( ( '$varCheckout' >= checkInDate AND '$varCheckout' <= checkOutDate ) OR( '$varCheckin' >= checkInDate AND '$varCheckin' <= checkOutDate ) OR ( '$varCheckin' < checkInDate AND '$varCheckout' > checkOutDate ) ) ) ) AS temp1
                                    UNION ALL
                                    SELECT id AS roomId
                                    FROM room
                                    WHERE id NOT IN( SELECT roomId
                                                    FROM reservation
                                                    )
                                )as final INNER JOIN room
                                ON final.roomId = room.id
                        )as final2 INNER JOIN hotel
            ON final2.hotelId = hotel.id
            WHERE location='$varLocation'
        ";

            
if(isset($_POST['roomType']) && $_POST['roomType'] != ""){
    $varRoomType = $_POST['roomType'];
    $query.=" AND room.type='$varRoomType'";

}



if(isset($_POST['stars']) && $_POST['stars'] != ""){

    $varStars = $_POST['stars'];
    $query.=" AND hotel.stars='$varStars'";

}

if(isset($_POST['minPrice']) && $_POST['minPrice'] != ""){

    $varMinPrice = $_POST['minPrice'];
    $query.=" AND room.price >= '$varMinPrice'";
}

if(isset($_POST['maxPrice']) && $_POST['maxPrice'] != ""){

    $varMaxPrice = $_POST['maxPrice'];
    $query.=" AND room.price <= '$varMaxPrice'";
}

//calculate the price for the whole duration
$varCheckinSec = strtotime($varCheckin);
$varCheckoutSec = strtotime($varCheckout);
$duration = round(($varCheckoutSec - $varCheckinSec)/ (60 * 60 * 24));


//GET all available rooms that matches the requirement and not reserved at all
$query.=" ORDER BY hotel.premium DESC";
$availableRooms = mysqli_query($db,$query);

        
        //if a room or more found
        if(mysqli_num_rows($availableRooms) > 0){

            //show the search results
            echo "<h2>Search Results</h2>";
   
            //table columns
            echo "<table>
            <tr>
            <th>Hotel name</th>
            <th>Location</th>
            <th>Stars</th>
            <th>Rating</th>
            <th>Room type</th>
            <th>Price</th>
            <th></th>
            </tr>";

            

            while($row = mysqli_fetch_assoc($availableRooms)){
   
                $totalPrice = $duration * $row['price']; 
                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['location'] . "</td>";
                echo "<td>" . $row['stars'] . "</td>";
                echo "<td>" . $row['rating'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $totalPrice . "</td>";

                echo "<td><a href='clientLanding.php?reserve=1&noDecrement=0&clientId=".$_SESSION['clientId']."
                        &roomId=".$row['roomId']."&checkIn=".$varCheckin."&checkOut=".$varCheckout."
                        &totalPrice=".$totalPrice."'>Reserve</a>";
                        echo "</tr>";

            }
            echo "</table>";


        }
        else{
            echo "<h2>No Results</h2>";
        }


}
?>


<?php

    //if the rate button is pressed--------------------------------------------------------------------------
   // if(isset($_POST['rate']))


    


    //if the show reservation button is pressed
    if(isset($_GET['showReservation'])){

        if($_GET['showReservation'] == 1){

            $clientId = $_SESSION['clientId'];
            //select all waiting reservation done by this client
            $waitingReservationquery = mysqli_query($db,"SELECT roomId,name,type,checkInDate,checkOutDate,tprice,clientId
                                             FROM(
                                                    SELECT roomId,hotelId,type,checkInDate,checkOutDate,tprice,clientId
                                                    FROM(
                                                            SELECT roomId,checkInDate,checkOutDate,price as tprice,clientId
                                                            FROM waitingreservation
                                                            WHERE clientId='$clientId'
                                                        )as temp1 INNER JOIN room
                                                        ON room.id=temp1.roomId
                                                )as temp2 INNER JOIN hotel
                                                ON hotel.id=temp2.hotelId");
            echo "<h2>Pending Reservations Request</h2></br>";
            
            //table columns
            echo "<table>
            <tr>
            <th>Hotel name</th>
            <th>Room type</th>
            <th>Check in</th>
            <th>Check out</th>
            <th>Price</th>
            <th></th>
            </tr>";

            while($row = mysqli_fetch_assoc($waitingReservationquery)){

                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $row['checkInDate'] . "</td>";
                echo "<td>" . $row['checkOutDate'] . "</td>";
                echo "<td>" . $row['tprice'] . "</td>";

                echo "<td> <a href='hotelLanding.php?cancel=1&clientId=".$row['clientId']."&roomId=".$row['roomId']."&checkIn=".$row['checkInDate']."'>Cancel</a>";
                echo "</tr>";
            }
            echo "</table>";

            //get the reservation done by the client
            $reservationquery = mysqli_query($db,"SELECT roomId,hotelId,name,type,checkInDate,checkOutDate,tprice,checkIn,clientId
                                                  FROM(
                                                           SELECT roomId,hotelId,type,checkInDate,checkOutDate,tprice,checkIn,clientId
                                                           FROM(
                                                                   SELECT roomId,checkInDate,checkOutDate,price as tprice,checkIn,clientId
                                                                   FROM reservation
                                                                   WHERE clientId='$clientId'
                                                               )as temp1 INNER JOIN room
                                                               ON room.id=temp1.roomId
                                                       )as temp2 INNER JOIN hotel
                                                       ON hotel.id=temp2.hotelId");

            echo "<h2>Reservations</h2><br/>";
            //table columns
            echo "<table>
            <tr>
            <th>Hotel name</th>
            <th>Room type</th>
            <th>Check in</th>
            <th>Check out</th>
            <th>Price</th>
            <th></th>
            </tr>";

            while($row = mysqli_fetch_assoc($reservationquery)){

                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $row['checkInDate'] . "</td>";
                echo "<td>" . $row['checkOutDate'] . "</td>";
                echo "<td>" . $row['tprice'] . "</td>";

                if($row['checkIn'] == -1){
                echo "<td> <a href='clientLanding.php?cancel=2&clientId=".$row['clientId']."&roomId=".$row['roomId']."&checkIn=".$row['checkInDate']."'>Cancel</a>";
                }
                else if($row['checkIn'] == 1){
                    echo "<td> <a href='Rate.php?rate=1&clientId=".$row['clientId']."&hotelId=".$row['hotelId']."'>Rate</a>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
?>







<?php
//if the search button is pressed
    if(isset($_GET['search'])){
        if($_GET['search'] == 1){
?>
                <form method = "post" action="clientLanding.php">
                    <div>
                        <label for="location"><b>Location</b></label>
                        <select name="location" required>
                            <option>---</option>
                            <option value="Alexandria">Alexandria</option>
                            <option value="Cairo">Cairo</option>
                            <option value="Fayuom">Fayoum</option>
                            <option value="NorthCoast">NorthCoast</option>
                            <option value="Siwa">Siwa</option>
                        </select><br/>
                    </div>

                    <div>
                        <label><b>Check in</b></label>
                        <input type="date" name="checkInDate" min="2018-12-1" required><br/>
                    </div>

                    <div>
                        <label><b>Check out</b></label>
                        <input type="date" name="checkOutDate" min="2018-12-1" required><br/>
                    </div>

                    <div>
                        <label for="stars"><b>Stars</b></label>
                        <select name="stars">
                            <option >---</option>
                            <option value="1">1 star</option>
                            <option value="2">2 stars</option>
                            <option value="3">3 stars</option>
                            <option value="4">4 stars</option>
                            <option value="5">5 stars</option>
                        </select><br/>
                    </div>

                    <div>
                        <label ><b>Room type</b></label>
                        <select name="roomType">
                            <option >---</option>
                            <option value="single">Single</option>
                            <option value="double">Double</option>
                            <option value="triple">Triple</option>
                            <option value="suite">Suite</option>
                        </select><br/>
                    </div>

                    <div>
                    <label><b>Minimum price</b></label>
                    <input type="text" name="minPrice"><br/>
                    <div>

                    <div>
                    <label><b>Maximum price</b></label>
                    <input type="text" name="maxPrice"><br/>
                    <div>

                    <button type="submit" name="searchFor">Search</button>
                </form>
<?php
        }
    }
?>




 <div>
 <a href='clientLanding.php?search=1'><button>Search</button></a>
 <a href='clientLanding.php?showReservation=1'><button>Show reservation</button></a>
 <a href='clientLanding.php?showAll=1'><button>Show all available hotels</button></a>
 <p> <a href="hotelLanding.php?logout='1'" style="color: red;">logout</a> </p>
 </div>   








</body>
</html>

