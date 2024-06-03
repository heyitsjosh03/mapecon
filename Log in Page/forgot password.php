<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$connection = mysqli_connect("localhost", "root", "", "mapecon"); // Update database name here
mysqli_query($connection, "SET time_zone = '+08:00'");
date_default_timezone_set("Asia/Singapore");

if (isset($_POST['forgotSubmit'])) {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'"; // Update table name here
    $result = mysqli_query($connection, $checkEmailQuery);

    if (mysqli_num_rows($result) > 0) {
        // Generate a random OTP
        $otp = generateOTP(); // Define the function to generate an OTP
        $expiration = date("Y-m-d H:i:s", strtotime('+5 minutes'));

        // Store the OTP in the database
        $sql = "UPDATE `users` SET `otp` = '$otp', `token_expired` = '$expiration' WHERE `email` = '$email'"; // Update column name here
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            echo "Error updating database: " . mysqli_error($connection);
            exit();
        }

        // Send the OTP to the user's email
        sendEmail($email, $otp); // Define the function to send an email

        // Display the success message and redirect
        echo '<script type="text/javascript">';
        echo 'alert("Password reset OTP has been sent to your email.");';
        echo 'window.location.href = "User Log in.php";';
        echo '</script>';
        exit();
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("Email does not exist");';
        echo 'window.location.href = "forgot password.php";';
        echo '</script>';
        exit();
    }
}

// Function to generate a random OTP
function generateOTP() {
    // Generate a random 6-digit number
    $otp = rand(100000, 999999);
    return $otp;
}

// Function to send an email with the OTP
function sendEmail($email, $otp) {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sorpresabakeshop2019@gmail.com';
    $mail->Password = 'qgmb eomy gogu rsux';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
    );

    $mail->setFrom('sorpresabakeshop2019@gmail.com', 'Sorpresa Bakeshop');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'MAPECON: Password Reset OTP';
    $mail->Body = "
        <body style='background: #FCFCFC; color: #000; padding: 50px; border-radius: 10px; font-family: \"Oxygen\", Arial, sans-serif; font-size:1rem; border: 2px solid #D6DDE1'>
            <center><img src='https://github.com/paulopoig/KalyeFeast/assets/78188625/b383b91a-6182-4e5b-950f-23337602412a' alt='MAPECON Logo' class='logo' style='width: 250px;'></center>
            <p style='color: #000;'><em>Good day!<em></p>
            <p style='color: #000;'>We have received a request to reset the password associated with your account. If you did not initiate this request, please disregard this message. To reset your password, please click on the link below:<br><br></p>
            <p style='color: #789;'>This is your otp: $otp<br></p>
            <center>
                <a href='http://localhost/mapecon/Log%20in%20Page/createnewpass.php?email=$email&otp=$otp' 
                style='background-color: #F32424; padding: 10px; color: #fff; font-weight: bolder; font-size: 1rem; font-family: \"Oxygen\", Arial, sans-serif; text-decoration: none; border-radius: 5px;'>RESET PASSWORD</a><br><br>
            </center> 
            <p style='color: #000;'>You will be taken to a page where you can enter a new password. Please make sure to choose a strong, unique password that you have not used before. <br><br>
            If you have any questions or concerns, please do not hesitate to contact us with this email. Thank you for your cooperation. Have a great day ahead! <br><br></p>
            <p style='color: #000;'><em>Best regards, <br><b>MAPECON</b></em> <br></p>
        </body>
    ";

    if (!$mail->send()) {
        echo '<script type="text/javascript">';
        echo 'alert("Error sending email: ' . $mail->ErrorInfo . '");';
        echo 'window.location.href = "forgot password.php";';
        echo '</script>';
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="shortcut icon" href="images/favicon.jpg" />
    <link rel="stylesheet" href="/mapecon/style.css">
</head>

<body class="no-header-padding">
    <div class="background-image"></div>
    <div class="container-login">
        <div class="login-form">
            <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo" class="logo">
            <h2 class="h2-forgor">Forgot your Password?</h2>
            <p class="p-forgor">We have to verify first by sending you an OTP.</p>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="login-btn" name="forgotSubmit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
