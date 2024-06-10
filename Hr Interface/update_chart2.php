<?php
session_start();

include("../sql/config.php");

$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Construct the WHERE clause based on the provided month and year
$whereClause = '';
if ($month != '' && $year != '') {
  $whereClause = "AND MONTH(date_filed) = '$month' AND YEAR(date_filed) = '$year'";
} else if ($month != '') {
  $whereClause = "AND MONTH(date_filed) = '$month'";
} else if ($year != '') {
  $whereClause = "AND YEAR(date_filed) = '$year'";
}

// Retrieve data for pending, approved, and declined leaves based on the provided month and year
$queryCasual = "SELECT COUNT(*) AS casual_count FROM leave_applications WHERE leave_type = 'Casual Leave' AND status = 'Approved' $whereClause";
$queryCompensatory = "SELECT COUNT(*) AS compensatory_count FROM leave_applications WHERE leave_type = 'Compensatory Off' AND status = 'Approved' $whereClause";
$queryWithoutPay = "SELECT COUNT(*) AS withoutpay_count FROM leave_applications WHERE leave_type = 'Leave without Pay' AND status = 'Approved' $whereClause";
$queryPrivilege = "SELECT COUNT(*) AS privilege_count FROM leave_applications WHERE leave_type = 'Privilege Leave' AND status = 'Approved' $whereClause";
$querySick = "SELECT COUNT(*) AS sick_count FROM leave_applications WHERE leave_type = 'Sick Leave' AND status = 'Approved' $whereClause";
$queryVacation = "SELECT COUNT(*) AS vacation_count FROM leave_applications WHERE leave_type = 'Vacation Leave' AND status = 'Approved' $whereClause";

$resultCasual = mysqli_query($connection, $queryCasual);
$resultCompensatory = mysqli_query($connection, $queryCompensatory);
$resultWithoutPay = mysqli_query($connection, $queryWithoutPay);
$resultPrivilege = mysqli_query($connection, $queryPrivilege);
$resultSick = mysqli_query($connection, $querySick);
$resultVacation = mysqli_query($connection, $queryVacation);

$rowCasual = mysqli_fetch_assoc($resultCasual);
$rowCompensatory = mysqli_fetch_assoc($resultCompensatory);
$rowWithoutPay = mysqli_fetch_assoc($resultWithoutPay);
$rowPrivilege = mysqli_fetch_assoc($resultPrivilege);
$rowSick = mysqli_fetch_assoc($resultSick);
$rowVacation = mysqli_fetch_assoc($resultVacation);

// Close database connection
mysqli_close($connection);

// Return data as JSON
echo json_encode([
  'casual_count' => $rowCasual['casual_count'],
  'compensatory_count' => $rowCompensatory['compensatory_count'],
  'withoutpay_count' => $rowWithoutPay['withoutpay_count'],
  'privilege_count' => $rowPrivilege['privilege_count'],
  'sick_count' => $rowSick['sick_count'],
  'vacation_count' => $rowVacation['vacation_count']
]);
?>
