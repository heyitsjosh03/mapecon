<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");

// Include the FPDF library
require("/xampp/htdocs/mapecon/fpdf/fpdf.php");

// Fetch leave application details from the database
$application_id = $_GET['application_id'];
$query = "SELECT l.*, 
                 u.firstname AS user_firstname, 
                 u.lastname AS user_lastname, 
                 u.department, 
                 u.contactnumber, 
                 a.checkedby
                 a.firstname AS approver_firstname, 
                 a.lastname AS approver_lastname
          FROM leave_applications AS l
          INNER JOIN users AS u ON l.user_id = u.user_id
          LEFT JOIN users AS a ON a.department = u.department AND a.user_status = 'Approver'
          WHERE l.application_id = '$application_id'
          ORDER BY a.user_id LIMIT 1"; // Ensure one approver is selected

$result = mysqli_query($connection, $query);
if (!$result) {
    die('Query Failed: ' . mysqli_error($connection));
}

$row = mysqli_fetch_assoc($result);
if (!$row) {
    die('No application found with ID: ' . $application_id);
}

// Fetch the logged-in user details from the session
$user_data = check_login($connection);
$logged_in_user_firstname = $user_data['firstname'];
$logged_in_user_lastname = $user_data['lastname'];

// Debugging: Log the fetched data to a file
file_put_contents('debug_log.txt', print_r($row, true));

// Create a new FPDF object
$pdf = new FPDF();

// Add a page
$pdf->AddPage();

// Set the path to your background image
$background = "/xampp/htdocs/mapecon/Pictures/leave form.png";

// Check if the image file exists before attempting to use it
if (file_exists($background)) {
    // Place the image as a background
    $pdf->Image($background, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
} else {
    die('Background image not found: ' . $background);
}

// Set font
$pdf->SetFont('Arial', 'B', 16);

// Set position for the content
$pdf->SetXY(10, 10);

// Output the title
$pdf->Cell(0, 10, '', 0, 1, 'C');

// Set font for the content
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 139);

// Output form
$pdf->Cell(0, 15, '', 0, 1);
$pdf->Cell(35, 10, '', 0, 0);
$pdf->Cell(91, 15, $row['date_filed'], 0, 0);
$pdf->Cell(0, 15, $row['department'], 0, 1);
$pdf->Cell(20, 10, '', 0, 0);
$pdf->Cell(0, 1, strtoupper($row['user_firstname'] . ' ' . $row['user_lastname']), 0, 1);
$pdf->Cell(96, 10, '', 0, 0);
$pdf->Cell(0, 15, $row['contactnumber'], 0, 1);
$pdf->Cell(21, 10, '', 0, 0);
$pdf->Cell(81, 16, $row['from_date'], 0, 0);
$pdf->Cell(0, 16, $row['to_date'], 0, 1);
$pdf->Cell(102, 10, '', 0, 0);
$pdf->Cell(0, 1, $row['working_days_covered'], 0, 1);
$pdf->Cell(46, 10, '', 0, 0);
if ($row['leave_type'] == "Others") {
    $pdf->Cell(0, 14, "Others ", 0, 1);
    $pdf->Cell(57, 0, '', 0, 0);
    $pdf->Cell(0, 1, "Others: " . $row['leave_type_others'], 0, 1);
} else {
    $pdf->Cell(0, 14, $row['leave_type'], 0, 1);
}
$pdf->Cell(3, 10, '', 0, 0);
$pdf->Cell(0, 42, $row['reason'], 0, 1);

$pdf->SetXY(20, 155); // Adjust X and Y as needed
$pdf->Cell(75, 1, strtoupper($row['user_firstname']) . ' ' . strtoupper($row['user_lastname']), 0, 0, "C");

// Output the approver's details
$pdf->Cell(0, 1, strtoupper($row['approver_firstname']) . ' ' . strtoupper($row['approver_lastname']), 0, 0, "C");

// Vacation Leave Balances
// Set the position for the 'With Pay' leave balance
$pdf->SetXY(50, 201); // Adjust X and Y as needed
$pdf->Cell(31, 10, $row['vl_wpay_bal'], 0, 0, 'R');

// Set the position for the 'Without Pay' leave balance
$pdf->SetXY(30, 201); // Adjust X and Y as needed
$pdf->Cell(0, 10, $row['vl_wopay_bal'], 0, 0, 'C');

// Set the position for the 'Total Balance' leave balance
$pdf->SetXY(150, 201); // Adjust X and Y as needed
$pdf->Cell(30, 10, $row['vl_total_bal'], 0, 1, 'C');

// Sick Leave Balances
// Set the position for the 'With Pay' leave balance
$pdf->SetXY(50, 210); // Adjust X and Y as needed
$pdf->Cell(31, 10, $row['sl_wpay_bal'], 0, 0, 'R');

// Set the position for the 'Without Pay' leave balance
$pdf->SetXY(30, 210); // Adjust X and Y as needed
$pdf->Cell(0, 10, $row['sl_wopay_bal'], 0, 0, 'C');

// Set the position for the 'Total Balance' leave balance
$pdf->SetXY(150, 210); // Adjust X and Y as needed
$pdf->Cell(30, 10, $row['sl_total_bal'], 0, 1, 'C');

// Output the logged-in user's name at the bottom
$pdf->SetXY(20, 251); // Adjust X and Y as needed
$pdf->Cell(54, 1, strtoupper($row['checkedby']) . ' ' . strtoupper($row['checkedby']), 0, 0, 'C');

// Output the PDF
$pdf->Output();
?>
