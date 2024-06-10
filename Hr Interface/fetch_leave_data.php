<?php
session_start();

include("../sql/config.php");

$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

$condition = '';
if ($month && $year) {
    $condition = "WHERE MONTH(date_filed) = '$month' AND YEAR(date_filed) = '$year'";
} elseif ($month) {
    $condition = "WHERE MONTH(date_filed) = '$month'";
} elseif ($year) {
    $condition = "WHERE YEAR(date_filed) = '$year'";
}

$queryPending = "SELECT COUNT(*) AS pending_count FROM leave_applications WHERE status = 'Pending' $condition";
$queryApproved = "SELECT COUNT(*) AS approved_count FROM leave_applications WHERE status = 'Approved' $condition";
$queryDeclined = "SELECT COUNT(*) AS declined_count FROM leave_applications WHERE status = 'Declined' $condition";

$resultPending = mysqli_query($connection, $queryPending);
$resultApproved = mysqli_query($connection, $queryApproved);
$resultDeclined = mysqli_query($connection, $queryDeclined);

$rowPending = mysqli_fetch_assoc($resultPending);
$rowApproved = mysqli_fetch_assoc($resultApproved);
$rowDeclined = mysqli_fetch_assoc($resultDeclined);

mysqli_close($connection);

$data = [
    'pending' => $rowPending['pending_count'],
    'approved' => $rowApproved['approved_count'],
    'declined' => $rowDeclined['declined_count']
];

echo json_encode($data);
?>
