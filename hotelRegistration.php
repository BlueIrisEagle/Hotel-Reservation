<?php
session_start();
?>

<<!DOCTYPE html>

    <html>

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Hotel Registration</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
        <script src="main.js"></script>
    </head>


    <?php
    $username="";
    $email="";
    $_SESSION['success']="";
    $error_flag=false;

    //connect to databse
    $db=mysqli_connect('localhost', 'root', '', 'hotelreservation');
    
    if( isset($_POST['registerHotel'])){

        //take the inputs in variables
        $hotelName = mysqli_real_escape_string($db, $_POST['hotelName']);
        $hotelEmail = mysqli_real_escape_string($db, $_POST['hotelEmail']);
        $hotelPassword = mysqli_real_escape_string($db, $_POST['hotelPassword']);
        $hotelConfirmPassword = mysqli_real_escape_string($db, $_POST['hotelConfirmPassword']);
        $stars = mysqli_real_escape_string($db, $_POST['stars']);
        $location = mysqli_real_escape_string($db, $_POST['location']);
        $premium = mysqli_real_escape_string($db, $_POST['premium']);
      //  $spa = mysqli_real_escape_string($db, $_POST['spa']);
      //  $swimmingPool = mysqli_real_escape_string($db, $_POST['swimmingPool']);
      //  $gym =  mysqli_real_escape_string($db, $_POST['gym']);
      //  $beach = mysqli_real_escape_string($db, $_POST['beach']);

        if(isset($_POST['spa'])){
            $spa = mysqli_real_escape_string($db, $_POST['spa']);
        }
        else{
            $spa = "not avaialble";
        }

        if(isset($_POST['swimmingPool'])){
            $swimmingPool = mysqli_real_escape_string($db, $_POST['swimmingPool']);
        }
        else{
            $swimmingPool = "not avaialble";
        }

        if(isset($_POST['gym'])){
            $gym = mysqli_real_escape_string($db, $_POST['gym']);
        }
        else{
            $gym ="not avaialble";
        }

        if(isset($_POST['beach'])){
            $beach = mysqli_real_escape_string($db, $_POST['beach']);
        }
        else{
            $beach = "not avaialble";
        }

        //Get room values
        $countSingle = mysqli_real_escape_string($db, $_POST['count']);
        $priceSingle = mysqli_real_escape_string($db, $_POST['price']);

        $countDouble = mysqli_real_escape_string($db, $_POST['count1']);
        $priceDouble = mysqli_real_escape_string($db, $_POST['price1']);
        
        $countTriple = mysqli_real_escape_string($db, $_POST['count2']);
        $priceTriple = mysqli_real_escape_string($db, $_POST['price2']);

        $countSuite = mysqli_real_escape_string($db, $_POST['count3']);
        $priceSuite = mysqli_real_escape_string($db, $_POST['price3']);

        //check that hotel doesn't exists in the database
        $queryHotel = "SELECT * FROM hotel WHERE email='$hotelEmail'";
        $queryWaitingHotel = "SELECT * FROM waitinghotel WHERE email='$hotelEmail'";
        $checkHotel = mysqli_query($db, $queryHotel);
        $checkWaitingHotel = mysqli_query($db, $queryWaitingHotel);


        if(mysqli_num_rows($checkHotel) >0 || mysqli_num_rows($checkWaitingHotel) >0)
        {
            $error_msg= "email already exists";
            echo "<div class=\"error\">".$error_msg."</div>";
            $error_flag = true;

        }
        //check that passwords matches
        else if($hotelPassword != $hotelConfirmPassword){
            $error_msg= "password doesn't match";
            echo "<div class=\"error\">".$error_msg."</div>";
            $error_flag = true;

        }

        if(!$error_flag){
            $hotelPassword = md5($hotelPassword);

            //default of rating of a hotel is the same as the stars of the hotel
            $query = "INSERT INTO waitinghotel (name,email,password,location,stars,rating,premium)
                        VALUES ('$hotelName','$hotelEmail','$hotelPassword','$location','$stars','$stars','$premium')";

            //insert into waitinghotel tabel
            mysqli_query($db, $query);

            //Get the id of the hotel and put with this id with the facilities of this in facilities table 
            $hotelid = "SELECT id
                        FROM waitinghotel
                         WHERE email='$hotelEmail'";
            $result = mysqli_query($db, $hotelid);
            $row = mysqli_fetch_assoc($result);
            $hotelId = $row["id"];
            
            //insert the facilities for the specific hotel in facilities table
            $queryf = "INSERT INTO waitingfacilities (hotelId,spa,beach,swimmingPool,GYM)
                        VALUES($hotelId,'$spa','$beach','$swimmingPool','$gym')";
            
            //insert into facilities table
           $var = mysqli_query($db, $queryf);
           

            //insert single rooms
            $querySingle = "INSERT INTO waitingroom (hotelId,price,type)
                        VALUES ('$hotelId', '$priceSingle', 'single')";
            
        //    mysqli_query($db, $querySingle);

            for($count=1; $count<=$countSingle; $count++){
                 
               // insert into waitingroom table
                mysqli_query($db, $querySingle);
            }

            //insert double rooms
            $queryDouble = "INSERT INTO waitingroom (hotelId,price,type)
                        VALUES ('$hotelId', '$priceDouble', 'double')";

        //    mysqli_query($db, $queryDouble);

            for($count=1; $count<=$countDouble; $count++){
                 
                //insert into waitingroom table
                mysqli_query($db, $queryDouble);
            }

            //insert triple rooms
            $queryTriple = "INSERT INTO waitingroom (hotelId,price,type)
                        VALUES ('$hotelId', '$priceTriple', 'triple')";

        //    mysqli_query($db, $queryTriple);

            for($count=1; $count<=$countTriple; $count++){
                 
                //insert into waitingroom table
                mysqli_query($db, $queryTriple);
            }

            //insert suites
            $querySuite = "INSERT INTO waitingroom (hotelId,price,type)
                        VALUES ('$hotelId', '$priceSuite', 'suite')";

        //    mysqli_query($db, $querySuite);

            for($count=1; $count<=$countSuite; $count++){
                 
                //insert into waitingroom table
                mysqli_query($db, $querySuite);
            }
            
            $_SESSION['type'] = "hotel";
            $_SESSION['userName'] = $hotelName;
            $_SESSION['success'] = "You are now logged in";
            header("location: hotelLanding.php");
            

        }



    }



    
   // check if a user is already logged in
    if ( isset($_SESSION['userName'] ))
    {
        // redirect them to their respective landing pages
       if( $_SESSION['type'] == 'admin')
            header('Location: adminLanding.php');
       else if( $_SESSION['type'] == 'client' )
            header('Location: clientLanding.php');
       else if( $_SESSION['type'] == 'hotel' )
            header('Location: hotelLanding.php');
    }
    ?>



        <body>

            <form method="POST" action="hotelRegistration.php">
                <header>
                    <h1>Hotel registration</h1>
                </header>
                <hr>
                <div>
                    <label for="hotelName"><b>Hotel name</b></label><br>
                    <input type="text" placeholder="hotel name" name="hotelName" required><br>
                </div>

                <div>
                    <label for="hotelEmail"><b>Hotel email</b></label><br>
                    <input type="text" placeholder="hotel email" name="hotelEmail" required><br>
                </div>

                <div>
                    <label for="hotelPassword"><b>Password</b></label><br>
                    <input type="password" placeholder="********" name="hotelPassword" required><br>
                </div>

                <div>
                    <label for="confirmPassword"><b>Confirm Password</b></label><br>
                    <input type="password" placeholder="********" name="hotelConfirmPassword" required><br>
                </div>


                <label for="stars"><b>Stars</b><br/></label>
                <select name="stars">
                     <option >---</option>
                    <option value="1">1 star</option>
                    <option value="2">2 stars</option>
                    <option value="3">3 stars</option>
                    <option value="4">4 stars</option>
                    <option value="5">5 stars</option>
                </select>


                <div>
                <label for="location"><b>Location</b><br/></label>
                <select name="location">
                    <option>---</option>
                    <option value="Alexandria">Alexandria</option>
                    <option value="Cairo">Cairo</option>
                    <option value="Fayuom">Fayoum</option>
                    <option value="NorthCoast">NorthCoast</option>
                    <option value="Siwa">Siwa</option>
                </select>
                </div>

                <div>
                <label ><b>Premium</b><br/></label>
                <select name="premium">
                    <option>---</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
                </div>

                <div>
                <label for="Facilities"><br><b>Facilities</b></label><br>
                    <input type="checkbox" name="spa" value="spa">Spa<br>
                    <input type="checkbox" name="swimmingPool" value="swimmingPool">Swimmingpool<br>
                    <input type="checkbox" name="beach" value="beach">Beach<br>
                    <input type="checkbox" name="gym" value="gym">GYM<br>
                </div>


            <!--  Rooms -->
            	<div >
			<label>Rooms</label> <br/>
			<div>
				<label>Single: </label> <br/>
				<label >Count</label>
				<input type="text" name="count"  required>
				<label >Price</label>
				<input type="text" name="price" required> <br/>
<br/>

				<label>Double: </label> <br/>
				<label >Count</label>
				<input type="text" name="count1"  required>
				<label>Price</label>
				<input type="text" name="price1" required> <br/>
<br/>

				<label>Triple: </label> <br/>
				<label >Count</label>
				<input type="text" name="count2" required >
				<label >Price</label> 
				<input type="text" name="price2" required> <br/>
<br/>
				<label>Suite: </label> <br/>
				<label >Count</label>
				<input type="text" name="count3" required >
				<label >Price</label> 
				<input type="text" name="price3" required> <br/><br/>
			</div>
		</div>


                <div>
                <br/>
                <button type="submit" name="registerHotel">Register</button>
                </div>

                <p>
                Already have an account? <a href="Login.php">Sign in</a> 
                </p>




            </form>

        </body>

    </html>