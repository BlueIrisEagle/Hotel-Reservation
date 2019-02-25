<?php
session_start();
?>


<link rel="stylesheet" href="Login.css" type="text/css">
<div class="body-content">
  <div class="module">
    <h1 align="center">Login</h1>
    <form class="form" name ="loginForm" method="post" enctype="multipart/form-data" autocomplete="off">
      <input type="email" placeholder="Email" name="email"  />
      <input type="password" placeholder="*********" name="password" autocomplete="new-password" />
      
      <input type="radio" name="RadioButtonLogin" value="clientRadio" checked> Client
      <input type="radio" name="RadioButtonLogin" value="ownerRadio"> Hotel Owner
      <input type="radio" name="RadioButtonLogin" value="adminRadio"> Admin 

      <input type="submit" value="Login" name="login" class="btn btn-block btn-primary" />

      <p><a href="hotelRegistration.php"> Register Your Hotel Now ! </a></p>                               <!--- HOTEL REGISTERATION PAGE HERE--->   
      <p><a href="ClientRegisteration.php"> Don't Have An Account ? </a></p>           <!--- CLIENT REGISTERATION PAGE HERE--->
      
    </form>
  </div>
</div>




<?php

$_SESSION['success']="";

$dataBaseConnection =  mysqli_connect('localhost','root','','hotelreservation'); //Instantiating a database connection.


if (isset($_POST['login'])){  

$email = mysqli_real_escape_string($dataBaseConnection, $_POST['email']);
$password = mysqli_real_escape_string($dataBaseConnection, $_POST['password']);
$radioAnswer = $_POST['RadioButtonLogin']; //Radion Button Readings
$error = 0;

//encrypt password
$password = md5($password);

if (empty($email)) {                          // Some Simple Validations
    echo "<script type='text/javascript'>
                    alert('Email field is empty !');
              </script>";
              $error =1;
                    }
  else if (empty($password)) {
    echo "<script type='text/javascript'>
                    alert('Password field is empty !');
              </script>";
              $error =1;
  }else{

  /*Do nothing and Move on*/
}



  


if($radioAnswer == "clientRadio" && $error == 0){

  $queryActiveClient = "SELECT * FROM client WHERE email='$email' AND password='$password'";
  $querySuspendedClient = "SELECT * FROM suspendedclient WHERE email='$email' AND password='$password'";
  $getSuspensionDate = "SELECT suspensionDate FROM suspendedclient WHERE email='$email' AND password='$password'";
  
  $resultActiveClient = mysqli_query($dataBaseConnection, $queryActiveClient);
  $resultSuspendedClient = mysqli_query($dataBaseConnection, $querySuspendedClient);
  //$resultSuspensionDate = mysqli_query($dataBaseConnection, $getSuspensionDate);

//get the id and username of active user
$rowActive = mysqli_fetch_assoc($resultActiveClient);


      //Calculating Suspension Time

$row = mysqli_fetch_array($resultSuspendedClient);
$your_date = strtotime($row["suspensionDate"]);
$datediff = time() - $your_date;

$numberOfSuspensionDays = round ($datediff / (60 * 60 * 24));





  if (mysqli_num_rows($resultActiveClient) == 1)  //If result is found in the active table 
  { 
      $tranId=$rowActive['id'];
      $_SESSION['clientId'] = $tranId;
      $_SESSION['username'] = $rowActive['name'];
      $_SESSION['type'] = "client";
      echo "<script type='text/javascript'>
                    alert('Client Login Success !');
              </script>"; 
      header("location: clientLanding.php");                                        // PLACE CLIENT LANDING PAGE HERE
  }




  else if(mysqli_num_rows($resultSuspendedClient) == 1 ) //Client Suspension Detection
  {
   if ($numberOfSuspensionDays >= 7) // Suspension interval is More than or equal to 7-days (One week)
    {
      // Swapping the entry from Client Suspended table into Active Client Table.
      $tranId=$row["id"];      //move the suspended user with same id   check if not wrong
      $transName=$row["name"];
      $transEmail=$row["email"];
      $transPassword=$row["password"];

      $insertionIntoActiveTable="INSERT INTO client(id,name,email,password)VALUES('$tranId','$transName','$transEmail','$transPassword')";
      $resultOfAciveClientTableInsertion = mysqli_query($dataBaseConnection, $insertionIntoActiveTable);


      $deletionFromSuspensionTable="DELETE FROM suspendedclient WHERE email='$transEmail' ";
      $resultOfSuspendedClientTableDeletion = mysqli_query($dataBaseConnection, $deletionFromSuspensionTable);

      //to get information of the logged in client
      $_SESSION['uername'] = $transName;
      $_SESSION['clientId'] = $tranId;
      $_SESSION['type'] = "client";


      echo "<script type='text/javascript'>
                    alert('Client Login Success !');
              </script>";
      header("location: clientLanding.php");                                 // PLACE CLIENT LANDING PAGE HERE
    } 

    else // Block days are less than 7 days
    echo "<script type='text/javascript'>
                    alert('Client Account is Suspended For Not Checking In !');
              </script>"; 
  }
       

 else{ echo "<script type='text/javascript'>
                    alert('Client Login Failure !');
              </script>";}
}
else if($radioAnswer == "ownerRadio" && $error == 0 ){

  $queryActiveHotel = "SELECT * FROM hotel WHERE email='$email' AND password='$password' AND suspended='0'";
  $querySuspendedHotel = "SELECT * FROM hotel WHERE email='$email' AND password='$password'AND suspended='1'";
  $queryWaitingHotel = "SELECT * FROM waitinghotel WHERE email='$email' AND password='$password'";
  
  $resultActiveHotel = mysqli_query($dataBaseConnection, $queryActiveHotel);
  $resultSuspendedHotel = mysqli_query($dataBaseConnection, $querySuspendedHotel);
  $resultWaitingHotel = mysqli_query($dataBaseConnection, $queryWaitingHotel);


  if (mysqli_num_rows($resultActiveHotel) == 1)  //If the result is found in the active table 
  { 
      //Get name and id of the hotel to use their information
      $result = mysqli_query($dataBaseConnection, "SELECT * FROM hotel WHERE email='$email' AND suspended ='0' ");
      $row = mysqli_fetch_assoc($result);
      $_SESSION['username'] = $row['name'];
      $_SESSION['hotelId'] = $row['id'];
      $_SESSION['type'] = "hotel";
      $_SESSION['success']="You are now logged in";

      echo $_SESSION['username'].$_SESSION['hotelId'].$_SESSION['type'].$_SESSION['success'];

      echo "<script type='text/javascript'>
                    alert('Hotel Owner Login Success !');
              </script>"; 
      header("location: hotelLanding.php");                                               // PLACE HOTEL LANDING PAGE HERE
  }
  else if(mysqli_num_rows($resultSuspendedHotel) == 1 )  // Hotel Suspension Detection--------------------------------------------------------
  {
    //Get name and id of the hotel to use their information
    $result = mysqli_query($dataBaseConnection, "SELECT * FROM hotel WHERE email='$email' AND suspended ='1' ");
    $row = mysqli_fetch_assoc($result);
    $_SESSION['username'] = $row['name'];
    $_SESSION['hotelId'] = $row['id'];
    $_SESSION['type'] = "hotel";
    $_SESSION['success']="You are suspended";

    echo "<script type='text/javascript'>
                    alert('Hotel is Suspended For Payment Issues ! Please Contact Adminstrator');
              </script>"; 
     header("location: hotelLanding.php");  
  }
   
   else if(mysqli_num_rows($resultWaitingHotel) == 1 ) 
  { echo "<script type='text/javascript'>
                    alert('Hotel is Pending Approval !');
              </script>"; 
  }


 else{ 
  echo "<script type='text/javascript'>
                    alert('Hotel OwnerLogin Failure !');
              </script>";}


}
 else if($radioAnswer == "adminRadio" && $error == 0) {                                   // Administrator Access
  if($email == "Admin@Admin.Admin" && $password == md5("password") && $error == 0 )             // Login Info For Admin is 
  {                                                                                       // email : Admin@Admin.Admin  CASE SENSITIVE
    echo "<script type='text/javascript'>                                  
                    alert('Welcome Admin !');
              </script>";                                                                   // Password: password       

              $_SESSION['type'] = "admin";
              $_SESSION['username'] = "admin";
              header("location: adminLanding.php");                             // PLACE ADMIN LANDING PAGE HERE
  }
  else{
    echo "<script type='text/javascript'>
                    alert('Admin Login Failure');
              </script>";
  }
  
  }
  else
   echo "<script type='text/javascript'>
                    alert('Error Submitting The Form');
              </script>"; 
 

 } 



?>

