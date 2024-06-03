<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");

$connection = mysqli_connect("localhost", "root", "", "mapecon"); // Update database name here

// Set MySQL session timezone to Singapore
mysqli_query($connection, "SET time_zone = '+08:00'");
date_default_timezone_set("Asia/Singapore");

if (isset($_GET['email']) && isset($_GET['otp'])) {
    
    $current_date = date("Y-m-d H:i:s");
    $email = $_GET['email'];
    $otp = $_GET['otp'];

    // Use token_expired >= current_date to check if the token is still valid
    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND otp = ? AND token_expired >= ?");
    $stmt->bind_param("sss", $email, $otp, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        // The link is valid
        $_SESSION['verified_email'] = $email;
        $_SESSION['otp'] = $otp;
    } else {
        echo '<script type="text/javascript">
                alert("Invalid or expired link");
                window.location.href="forgot password.php";
              </script>';
        exit();
    }
    $stmt->close();
}

if (isset($_POST['updatepass'])) {
    if (isset($_SESSION['verified_email']) && isset($_SESSION['otp'])) {
        $email = $_SESSION['verified_email'];
        $input_otp = $_POST['otpnum'];
        $newpass = $_POST['newpass'];

        // Verify the OTP
        if ($input_otp === $_SESSION['otp']) {
            // Validate the new password
            if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newpass)) {
                $hashed_password = password_hash($newpass, PASSWORD_DEFAULT);
                
                // Update the user's password in the database
                $update_stmt = $connection->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $hashed_password, $email);
                $update_result = $update_stmt->execute();
                $update_stmt->close();

                if ($update_result) {
                    unset($_SESSION['verified_email']);
                    unset($_SESSION['otp']);
                    echo "<script>
                            alert('Password updated successfully');
                            window.location.href='User Log in.php';
                          </script>";
                } else {
                    echo '<script type="text/javascript">
                            alert("Failed to update password. Please try again later");
                          </script>';
                }
            } else {
                echo '<script type="text/javascript">
                        alert("New password must be at least 8 characters long and contain at least one uppercase letter, one number, and one special character.");
                      </script>';
            }
        } else {
            echo '<script type="text/javascript">
                    alert("Wrong OTP. Please try again.");
                  </script>';
        }
    } else {
        echo '<script type="text/javascript">
                alert("Session expired. Please request a new password reset.");
                window.location.href="forgot password.php";
              </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password | MAPECON</title>
    <link rel="shortcut icon" type="image/png" href="CSS/Pictures/favicon.png">
    <link rel="stylesheet" href="CSS/createnewpass.css">
    <link rel="stylesheet" href="/mapecon/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Judson&family=Poppins&family=Quicksand:wght@300&display=swap" rel="stylesheet">
</head>
<body id="createnewpass-body" class="no-header-padding">
    <div class="background-image"></div>
    <div class="container-login">
        <div class="login-form">
            <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo" class="logo">
            <h2 class="h2-forgor">Create New Password</h2>
            <p class="p-forgor">Email verified. Create a password that you can remember.</p>
            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="otpnum" id="otpnum" required placeholder="Enter the OTP number">
                    <input type="password" name="newpass" id="newpass" required placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <button type="submit" class="login-btn" name="updatepass">Update</button>
                </div>
            </form>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
