<?php
session_start();

include("../sql/config.php");

$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Construct the WHERE clause based on the provided month and year
$whereClause = '';
if ($month != '' && $year != '') {
  $whereClause = "WHERE MONTH(date_filed) = '$month' AND YEAR(date_filed) = '$year'";
} else if ($month != '') {
  $whereClause = "WHERE MONTH(date_filed) = '$month'";
} else if ($year != '') {
  $whereClause = "WHERE YEAR(date_filed) = '$year'";
}

// Retrieve data for pending, approved, and declined leaves based on the provided month and year
$queryPending = "SELECT COUNT(*) AS pending_count FROM leave_applications $whereClause AND status = 'Pending'";
$queryApproved = "SELECT COUNT(*) AS approved_count FROM leave_applications $whereClause AND status = 'Approved'";
$queryDeclined = "SELECT COUNT(*) AS declined_count FROM leave_applications $whereClause AND status = 'Declined'";

$resultPending = mysqli_query($connection, $queryPending);
$resultApproved = mysqli_query($connection, $queryApproved);
$resultDeclined = mysqli_query($connection, $queryDeclined);

$rowPending = mysqli_fetch_assoc($resultPending);
$rowApproved = mysqli_fetch_assoc($resultApproved);
$rowDeclined = mysqli_fetch_assoc($resultDeclined);

// Close database connection
mysqli_close($connection);

// Return data as JSON
echo json_encode([
  'pending_count' => $rowPending['pending_count'],
  'approved_count' => $rowApproved['approved_count'],
  'declined_count' => $rowDeclined['declined_count']
]);
?>
