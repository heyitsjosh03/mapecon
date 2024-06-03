<?php

session_start();

if(isset($_SESSION['user_id']))
{
    unset($_SESSION['user_id']);
}

header("Location: ../Log in Page/User Log in.php");
die;
?>