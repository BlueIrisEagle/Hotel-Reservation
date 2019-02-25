<?php
session_start();
?>

<!--- <!DOCTYPE html> --->
<html>
<body>
<link rel="stylesheet" href="ClientRegisteration.css" type="text/css">
<div class="body-content">
  <div class="module">
    <h1 align="center">Client Registeration</h1>
    <form class="form" name ="loginForm" method="post" onsubmit="return validateForm()" enctype="multipart/form-data" autocomplete="off">  <!--- HTML5 Auto Validation is Off--->
      
      <input type="text" placeholder="Client Name" id="idname" name="clientName" required="true"  />
      <input type="email" placeholder="Email" id="idemail" name="email" required="true" />
      <input type="password" placeholder="Password" id="idpass" name="password" autocomplete="new-password" required="true" />
      <input type="password" placeholder="Confirm Password" id="idpass2" name="confirmpassword" autocomplete="new-password" required="true" />
      <input type="submit" value="Register" name="register" class="btn btn-block btn-primary" />
      <p><a href="Login.php"> Already A Member ? </a></p>  
    </form>
  </div>
</div>
</body>
</html>





<?php
$username="";
$email="";
$_SESSION['success']="";
$error_flag=false;

$dataBaseConnection =  mysqli_connect('localhost','root','','hotelreservation'); //Instantiating a database connection.


if (isset($_POST['register'])){

$clientName = mysqli_real_escape_string($dataBaseConnection, $_POST['clientName']);
$email = mysqli_real_escape_string($dataBaseConnection, $_POST['email']);
$password = mysqli_real_escape_string($dataBaseConnection, $_POST['password']);
$confirmPassword = mysqli_real_escape_string($dataBaseConnection, $_POST['confirmpassword']);

//encrypt password
$password = md5($password);
$confirmPassword = md5($confirmPassword);

$error = 0;

if (empty($clientName)) {                          // Some Simple Validations
    echo "<script type='text/javascript'>
                    alert('Name field is empty !');
              </script>";
              $error =1;
                    }
  else if (empty($email)) {
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
            }
    else if (empty($confirmPassword)) {
    echo "<script type='text/javascript'>
                    alert('Confirm Password field is empty !');
              </script>";
              $error =1;
            }
    else if ( $confirmPassword != $password ) {
    echo "<script type='text/javascript'>
                    alert('Password Mismatch !');
              </script>";
              $error =1;
    

  }else{

  /*Do nothing and Move on*/
        }

        if($error == 1)
        { echo "<script type='text/javascript'>
                    alert('Registeration Failure');
              </script>";}
         else{     

          $insertionIntoActiveClients = "INSERT INTO client (name,email,password) VALUES ('$clientName','$email','$password')";
          $resultOfInsertion = mysqli_query($dataBaseConnection, $insertionIntoActiveClients);
        

          //GET the id of the recently inserted client
          $resultOfId = mysqli_query($dataBaseConnection, "SELECT id
                                                           FROM client
                                                           WHERE email ='$email'");
          $row = mysqli_fetch_assoc($resultOfId);


        if($resultOfInsertion == true ){ 
          
          $_SESSION['username'] = $clientName;
          echo $_SESSION['username'];
          $_SESSION['clientId'] = $row['id'];
          $_SESSION['type'] = "client";
          echo "<script type='text/javascript'>
                    alert('Registeration Success !');
                </script>";

          header("location: clientLanding.php");                           // INSERT CLIENT LANDING PAGE HERE
        
        }
        else
        {
          echo "<script type='text/javascript'>
                    alert('Registeration Failed !');
                </script>";
        }
      
        }



      }



?>
