<?php
include('includes/lib.php');
session_destroy(); // destroy session
header("location:login.php"); 
?>