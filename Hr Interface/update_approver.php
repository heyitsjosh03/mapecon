<?php
session_start();
include("../sql/config.php");
include("../sql/function.php");

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/path/to/PHPMailer/src/PHPMailer.php';
require '/path/to/PHPMailer/src/SMTP.php';
require '/path/to/PHPMailer/src/Exception.php';

// Check if user is logged in
$user_data = check_login($connection);

if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Fetch application details from database
    $query = "SELECT * FROM leave_applications WHERE application_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $application_data = $result->fetch_assoc();

        // Fetch user details for the applicant
        $user_id = $application_data['user_id'];
        $user_query = "SELECT firstname, lastname, contactnumber, department FROM users WHERE user_id = ?";
        $user_stmt = $connection->prepare($user_query);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if (!$user_result) {
            die('Error: ' . $connection->error);
        }
        $user_data = $user_result->fetch_assoc();
    } else {
        die('Error: Application not found.');
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approver-btn'])) {
    // Update leave application details (if necessary)
    
    // Fetch approver's email
    $applicant_department = $user_data['department'];

    $query = "SELECT email FROM users WHERE user_status = 'approver' AND department = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $applicant_department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $supervisor_email = $row['email'];

        // Send email notification to the supervisor
        $email_sent = sendEmailToSupervisor($supervisor_email, $user_data, $application_data);

        if ($email_sent) {
            echo 'Email notification sent successfully';
        } else {
            echo 'Failed to send email notification';
        }
    } else {
        echo 'No approver found for the department.';
    }

    // Redirect or perform further actions as needed
    header("Location: Pending Leaves.php");
    exit; // Stop further execution
}

// Function to send an email
function sendEmailToSupervisor($supervisor_email, $user_data, $application_data) {
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
    $mail->addAddress($supervisor_email);

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'New Pending Leave Application';
    $mail->Body = "
        <p>Hello Supervisor,</p>
        <p>A new leave application requires your approval. Please review it in the HR system.</p>
        <p><strong>Applicant:</strong> {$user_data['firstname']} {$user_data['lastname']}</p>
        <p><strong>Leave Type:</strong> {$application_data['leave_type']}</p>
        <p><strong>From Date:</strong> {$application_data['from_date']}</p>
        <p><strong>To Date:</strong> {$application_data['to_date']}</p>
        <p><strong>Working Days Covered:</strong> {$application_data['working_days_covered']}</p>
        <br>
        <p><a href='http://localhost/mapecon/Hr%20Interface/Pending%20Leaves.php'>View Pending Leaves</a></p>
        <br><br>
        If you have any questions or concerns, please do not hesitate to contact us with this email. Thank you for your cooperation. Have a great day ahead! <br><br></p>
        <p style='color: #000;'><em>Best regards, <br><b>MAPECON</b></em> <br></p>
        <br><br>
        My Almythy's Plan to Exalt Christ Operates Now!
    ";

    if (!$mail->send()) {
        error_log("Error sending email: " . $mail->ErrorInfo);
        return false;
    }
    return true;
}
?>
