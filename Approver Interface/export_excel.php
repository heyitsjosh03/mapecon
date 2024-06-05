<?php
session_start();

include("../sql/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedIds = json_decode($_POST['selected']);

    // Select leave applications with the selected ids and user details
    $sql = "SELECT l.*, 
                   CONCAT(u.lastname) AS last_name, 
                   CONCAT(u.firstname) AS first_name, 
                   u.contactnumber, 
                   u.department 
            FROM leave_applications AS l 
            INNER JOIN users AS u ON l.user_id = u.user_id
            WHERE l.id IN (" . implode(",", $selectedIds) . ")
            ORDER BY l.id DESC";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        // Define CSV content
        $csvData = "Last Name,First Name,Date Filed,Department,Contact Number,Type of Leave,Date Requested,Leave Until,Working days covered,Reason\n";
        while($row_data = $result->fetch_assoc()) {
            $csvData .= '"' . $row_data['last_name'] . '","' . $row_data['first_name'] . '","' . $row_data['date_filed'] . '","' . $row_data['department'] . '","' . $row_data['contactnumber'] . '","' . $row_data['leave_type'] . '","' . $row_data['from_date'] . '","' . $row_data['to_date'] . '","' . $row_data['working_days_covered'] . '","' . $row_data['reason'] . "\"\n";
        }

        // Set headers to force download
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=approved_leaves.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Output CSV data
        echo $csvData;
        exit;
    } else {
        echo "No data found";
    }
}
?>
