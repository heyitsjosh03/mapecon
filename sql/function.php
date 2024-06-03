<?php

function check_login($connection){
   if(isset($_SESSION['user_id'])){
        $id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE user_id = '$id' limit 1";

        $result = mysqli_query($connection, $query);
        if($result && mysqli_num_rows($result) > 0){
            $user_data = mysqli_fetch_assoc($result);
            return $user_data;
        }
    }
    //redirect to login
    header("Location: ../Log in Page/User Log in.php");
    die;
}

function random_num($length)
{
    $text = "";
    if($length < 5)
    {
        $length = 5;
    }

    $len = rand(4, $length);

    for ($i=0; $i < $len; $i++)
    {
        $text .= rand(0,9);
    }

    return $text;
}

function updatePassword($connection, $email, $new_password) {
    // Update user's password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
    if (mysqli_query($connection, $update_sql)) {
        return true;
    } else {
        return false;
    }
}