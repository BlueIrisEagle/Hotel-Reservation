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
    if( $_SESSION['type'] != 'hotel' )
    {
        // if it's an admin
        if( $_SESSION['type'] == 'client' )
            header("Location: clientLanding.php");
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
    <title>Hotel home page</title>
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


$varHotelId = $_SESSION['hotelId'];


//connect to databse
 $db=mysqli_connect('localhost', 'root', '', 'hotelreservation');

 //check if the button in the checkin column is pressed or not
if(isset($_GET['checkIn'])){
    //if the client checked in change the value of checkIn in reservation table to 1
    if($_GET['checkIn'] == 1){

        $varClientId = $_GET['clientId'];
        $varPrice = $_GET['price'];
        $brokerComm = 0.09 * $varPrice;
        $query ="UPDATE reservation
                 SET checkIn = '1'
                 WHERE clientId = '$varClientId'";
         mysqli_query($db,$query);

         //to increase the money that the hotel owe to the broker
         $resultMoney = mysqli_query($db, "SELECT moneyOwed
                                           FROM hotel
                                           WHERE id = '$varHotelId'");
        $rowMoney = mysqli_fetch_assoc($resultMoney);
        $newMoneyOwed = $rowMoney['moneyOwed'] + $brokerComm ;
        
        //UPDATE the value of moneyOwed in hotel
        mysqli_query($db,"UPDATE hotel
                          SET moneyOwed = '$newMoneyOwed'
                          WHERE id = '$varHotelId'");

        }

     //if the client didn't checked in and the checkInDate is passed then this client must be blocked
     else if($_GET['checkIn'] == 0){

                 $suspedCLientDate = $_GET['suspendDate'];
                 $varClientId = $_GET['clientId'];

                 //select client to be suspended
                $result = mysqli_query($db,"SELECT * FROM client WHERE id = '$varClientId'");
                $rowBlock = mysqli_fetch_assoc($result);

                //temp variables
                $clientEmail = $rowBlock['email'];
                $clientName = $rowBlock['name'];
                $clientPassword = $rowBlock['password'];

                //put the client in suspendedclient
                mysqli_query($db,"INSERT INTO suspendedclient (id,email,name,password,suspensionDate) VALUES ('$varClientId','$clientEmail','$clientName','$clientPassword','$suspedCLientDate')");

                //delete client from client table
                mysqli_query($db,"DELETE FROM client WHERE id ='$varClientId'");  
                }
}

//waiting hotel action
if(isset($_GET['approved'])){

    if($_GET['approved'] == 1){

        $temproomId = $_GET['wroomId'];
        $tempclientId = $_GET['wclientId'];
        $tempcheckInDate = $_GET['wcheckInDate'];
        $tempcheckOutDate = $_GET['wcheckOutDate'];
        $tempprice = $_GET['wprice'];

        //move approved reservation from waitngreservation table to reservation table
        mysqli_query($db,"INSERT INTO reservation (roomId,clientId,checkInDate,checkOutDate,price)
                              VALUES ('$temproomId','$tempclientId','$tempcheckInDate','$tempcheckOutDate','$tempprice')");

        mysqli_query($db,"DELETE FROM waitingreservation
                          WHERE roomId='$temproomId' AND clientId='$tempclientId' AND checkInDate='$tempcheckInDate'");
                          
        
    }
    else if($_GET['approved'] == 0)
    {
        $temproomId = $_GET['wroomId'];
        $tempclientId = $_GET['wclientId'];
        $tempcheckInDate = $_GET['wcheckInDate'];

        //increment room number due to rejection of reservation request
      /*  $tempToincrement = mysqli_query($db,"SELECT count
                                             FROM room
                                             WHERE id='$temproomId'");
        $rowToincrement = mysqli_fetch_assoc($tempToincrement);
        $oldValue = $rowToincrement['count'];
        $newValue = $oldValue + 1;
        mysqli_query($db,"UPDATE room
                          SET count='$newValue'
                          WHERE id ='$temproomId'");
        */
        
        //delete the rejected reservation from waiting reservation
        mysqli_query($db,"DELETE FROM waitingreservation
                         WHERE roomId='$temproomId' AND clientId='$tempclientId' AND checkInDate='$tempcheckInDate'");

    }

}

echo $_SESSION['success']."</br>";
 echo "<h1>Welcome ".$_SESSION['username']."</h1>";

 //if the show reservation button is pressed then preview the reservation table
 if(isset($_POST['showReservation'])){

 $result = mysqli_query($db, "SELECT roomId,clientId,name,type,checkInDate,checkOutDate,checkIn,price
                              FROM(
                                     SELECT roomId,clientId,type,checkInDate,checkOutDate,checkIn,price
                                     FROM(
                                            SELECT id,type
                                            FROM room
                                            WHERE room.hotelId = '$varHotelId'
                                         )as temp1 INNER JOIN reservation
                                             ON reservation.roomId = temp1.id
                                     ) as temp2 INNER JOIN client
                                        ON temp2.clientId = client.id");

echo "<h2>Reservations</h2></br>";
echo "<hr>";

//table columns
echo "<table>
<tr>
<th>roomId</th>
<th>Client name</th>
<th>room type</th>
<th>Checkin Date</th>
<th>checkout Date</th>
<th>checkin</th>
<th>price</th>
</tr>";

//Get day of today and use it in checking the checkIn date
$dateToday =date("Y-m-d");
$dateTodaySec = strtotime($dateToday);

while($row = mysqli_fetch_assoc($result)){

    echo "<tr>";
    echo "<td>" . $row['roomId'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "<td>" . $row['checkInDate'] . "</td>";
    echo "<td>" . $row['checkOutDate'] . "</td>";
 //   echo "<td>" . $row['checkIn'] . "</td>";

    //check if today is the checkInDate of the client
    if($dateTodaySec == strtotime($row['checkInDate'])){
        if($row['checkIn'] == '-1'){

        echo "<td> <a href='hotelLanding.php?checkIn=1&clientId=".$row['clientId']."&price=".$row['price']."'>Check in</a>";
        }
        else{
            echo "<td>checked in</td>";
        }
    }
    //if the checkInDate didn't came yet
    else if($dateTodaySec < strtotime($row['checkInDate'])){
        echo "<td>_</td>";
    }
    //if the checkInDate passed
    else{
        if($row['checkIn'] == '-1'){

        echo "<td> <a href='hotelLanding.php?checkIn=0&clientId=".$row['clientId']."&suspendDate=".$dateToday."'>Block Client</a>";
        }
        else{
            echo "<td>checked in</td>";
        }
    }
    echo "<td>" . $row['price'] . "</td>";
    echo "</tr>";
    }
    echo "</table>";
 }


 //show waiting reservation
 if(isset($_POST['showWaitingReservation'])){

    $result = mysqli_query($db, "SELECT roomId,clientId,name,type,checkInDate,checkOutDate,checkIn,price
                                 FROM(
                                         SELECT roomId,clientId,type,checkInDate,checkOutDate,checkIn,price
                                         FROM(
                                                 SELECT id,type
                                                 FROM room
                                                 WHERE room.hotelId = '$varHotelId'
                                               )as temp1 INNER JOIN waitingreservation
                                               ON waitingreservation.roomId = temp1.id
                                       ) as temp2 INNER JOIN client
                                      ON temp2.clientId = client.id");
   
   echo "<h2>Waiting Reservations</h2>";
   
   //table columns
   echo "<table>
   <tr>
   <th>roomId</th>
   <th>Client name</th>
   <th>room type</th>
   <th>Checkin Date</th>
   <th>checkout Date</th>
   <th>Approve or reject</th>
   <th>price</th>
   </tr>";
   
   while($row = mysqli_fetch_assoc($result)){
   
       echo "<tr>";
       echo "<td>" . $row['roomId'] . "</td>";
       echo "<td>" . $row['name'] . "</td>";
       echo "<td>" . $row['type'] . "</td>";
       echo "<td>" . $row['checkInDate'] . "</td>";
       echo "<td>" . $row['checkOutDate'] . "</td>";
     //  echo "<td>" . $row['checkIn'] . "</td>";

        echo "<td><a href='hotelLanding.php?approved=1&wroomId=".$row['roomId']."&wclientId=".$row['clientId']."&wcheckInDate=".$row['checkInDate']."&wcheckOutDate=".$row['checkOutDate']."&wprice=".$row['price']."'>approve</a></br>
        <a href='hotelLanding.php?approved=0&wroomId=".$row['roomId']."&wclientId=".$row['clientId']."&wcheckInDate=".$row['checkInDate']."'>reject</a>";
       echo "<td>" . $row['price'] . "</td>";
       echo "</tr>";
       }
       echo "</table>";
    }


?>



<body>
    

        <div>
        <form method="post" action="hotelLanding.php">
        <hr>
        <button type="submit" name="showReservation">Show reservations</button>
        <button type="submit" name="showWaitingReservation">Show  waiting reservations</button>


            <p> <a href="hotelLanding.php?logout='1'" style="color: red;">logout</a> </p>
        </form>
        </div>

</body>
</html>