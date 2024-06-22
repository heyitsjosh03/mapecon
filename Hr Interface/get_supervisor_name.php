<?php
include("../sql/config.php");

if (isset($_POST['approver_id'])) {
    $approver_id = $_POST['approver_id'];
    $query = "SELECT firstname, lastname FROM users WHERE user_id = $approver_id";
    $result = mysqli_query($connection, $query);
    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
    } else {
        echo 'Supervisor not found';
    }
} else {
    echo 'Error: Supervisor ID not provided';
}
?>
