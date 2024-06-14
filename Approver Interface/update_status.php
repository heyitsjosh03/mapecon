<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];

    // Retrieve user email based on application_id and users_id
    $query = "SELECT u.email
              FROM leave_applications AS la
              INNER JOIN users AS u ON la.user_id = u.user_id
              WHERE la.application_id = '$application_id'";

    $result = mysqli_query($connection, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $email = $row['email'];

            // Update the status of the leave application
            $update_query = "UPDATE leave_applications SET status = '$status' WHERE application_id = '$application_id'";
            if (mysqli_query($connection, $update_query)) {
                // If the status is approved, send an email
                if ($status == 'Approved') {
                    sendApprovalEmail($email,$application_id);
                }
                elseif ($status == 'Declined') {
                    sendDeclinedEmail($email,$application_id);
                }

                // Redirect to Pending Leaves page with success message
                echo '<script type="text/javascript">';
                echo 'alert("Leave status updated successfully.");';
                echo 'window.location.href = "Pending Leaves.php";';
                echo '</script>';
            } else {
                // Redirect with error message
                echo '<script type="text/javascript">';
                echo 'alert("Error updating leave status: ' . mysqli_error($connection) . '");';
                echo 'window.location.href = "Pending Leaves.php";';
                echo '</script>';
            }
        } else {
            // Handle case where no or multiple rows are found (should ideally be one)
            echo '<script type="text/javascript">';
            echo 'alert("No user found for the given application ID.");';
            echo 'window.location.href = "Pending Leaves.php";';
            echo '</script>';
        }
    } else {
        // Query execution failed
        echo '<script type="text/javascript">';
        echo 'alert("Error retrieving user email: ' . mysqli_error($connection) . '");';
        echo 'window.location.href = "Pending Leaves.php";';
        echo '</script>';
    }
}


// Function to send an approval email
function sendApprovalEmail($result,$application_id) {
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

    $mail->setFrom('Mapecon@gmail.com', 'MAPECON');
    $mail->addAddress($result);

    // Content
    $mail->isHTML(true); // Set email format to HTML
      $mail->Subject = 'Leave Application';
      $mail->Body = " <body style='background: #FCFCFC; color: #000; padding: 50px; border-radius: 10px; font-family: \"Oxygen\", Arial, sans-serif; font-size:1rem; border: 2px solid #D6DDE1'>
            <center><img src='https://github.com/paulopoig/KalyeFeast/assets/78188625/b383b91a-6182-4e5b-950f-23337602412a' alt='MAPECON Logo' class='logo' style='width: 250px;'></center>
            <p style='color: #000;'><em>Good day!<em></p>
            <p style='color: #000;'>We are happy to inform you that your request Leave Application is:<br><br></p>
            <h2> APPROVED! </h2>
            <br>
            <a href='http://localhost/mapecon/User%20Interface/view%20leave%20docs%20approved.php?application_id={$application_id}'>View Leave Application</a>
            </center>
            <br><br>
            If you have any questions or concerns, please do not hesitate to contact us with this email. Thank you for your cooperation. Have a great day ahead! <br><br></p>
            <p style='color: #000;'><em>Best regards, <br><b>MAPECON</b></em> <br></p>
            <br><br>
            <em>
            My Almythy's Plan to Exalt Christ Operates Now...
            <br>
            ...by the power of the Holy Spirit!
            </em>
        </body>
    ";

    if (!$mail->send()) {
        echo '<script type="text/javascript">';
        echo 'alert("Error sending email: ' . $mail->ErrorInfo . '");';
        echo 'window.location.href = "Pending Leaves.php";';
        echo '</script>';
        exit();
    }
}

// Function to send an declined email
function sendDeclinedEmail($result,$application_id) {
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

    $mail->setFrom('Mapecon@gmail.com', 'MAPECON');
    $mail->addAddress($result);

    // Content
    $mail->isHTML(true); // Set email format to HTML
      $mail->Subject = 'Leave Application';
      $mail->Body = " <body style='background: #FCFCFC; color: #000; padding: 50px; border-radius: 10px; font-family: \"Oxygen\", Arial, sans-serif; font-size:1rem; border: 2px solid #D6DDE1'>
            <center><img src='https://github.com/paulopoig/KalyeFeast/assets/78188625/b383b91a-6182-4e5b-950f-23337602412a' alt='MAPECON Logo' class='logo' style='width: 250px;'></center>
            <p style='color: #000;'><em>Good day!<em></p>
            <p style='color: #000;'>We regret to inform you that your Leave Application request is:<br><br></p>
            <h2> DECLINED! </h2>
            <a href='http://localhost/mapecon/User%20Interface/view%20leave%20docs%20declined.php?application_id={$application_id}'>View Leave Application</a>
            </center>
            <br><br>
            If you have any questions or concerns, please do not hesitate to contact us with this email. Thank you for your cooperation. Have a great day ahead! <br><br></p>
            <p style='color: #000;'><em>Best regards, <br><b>MAPECON</b></em> <br></p>
            <br><br>
            <em>
            My Almythy's Plan to Exalt Christ Operates Now...
            <br>
            ...by the power of the Holy Spirit!
            </em>
        </body>
    ";

    if (!$mail->send()) {
        echo '<script type="text/javascript">';
        echo 'alert("Error sending email: ' . $mail->ErrorInfo . '");';
        echo 'window.location.href = "Pending Leaves.php";';
        echo '</script>';
        exit();
    }
}
?>
