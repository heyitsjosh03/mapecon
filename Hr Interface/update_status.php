<?php
session_start();
include("../sql/config.php");

$conn = mysqli_connect("localhost","root","","mapecon") or die("Couldn't connect");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $application_id = $_POST["application_id"];
    $status = $_POST["status"];

    // Update status in database
    $sql = "UPDATE leave_applications SET status = '$status' WHERE application_id = $application_id";

    if ($conn->query($sql) === TRUE) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    // Redirect back to the previous page
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}
?>
