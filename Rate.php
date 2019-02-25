<?php
session_start();
$_SESSION['hotelRateId'] = $_GET['hotelId'];
?>

<<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Rating Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
    <script src="main.js"></script>
</head>
<body>
    
    <h2>Rate Now</h2>
    <form method="post" action="clientLanding.php">
    <label>rate the hotel now<br/></label>
    <input type="text" name="rate">
    <button type="submit" name="rateNow">Submit</button>
    </form>
</body>
</html>