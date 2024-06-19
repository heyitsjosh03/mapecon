<?php
include("../sql/config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approver_id'])) {
  $approver_id = $_POST['approver_id'];

  $query = "SELECT firstname, lastname FROM users WHERE user_id = ?";
  $stmt = $connection->prepare($query);
  $stmt->bind_param("s", $approver_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $supervisor_name = $row['firstname'] . ' ' . $row['lastname'];
    echo htmlspecialchars($supervisor_name);
  } else {
    echo "Supervisor not found";
  }
}
?>
