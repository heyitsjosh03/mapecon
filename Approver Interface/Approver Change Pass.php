<?php
  session_start();
  include("../sql/config.php");
  include("../sql/function.php");
  
  // Check if user is logged in
  $user_data = check_login($connection);

  // Handle form submission
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $current_password = $_POST['current-password'];
      $new_password = $_POST['new-password'];
      $confirm_password = $_POST['confirm-new-password'];

      // Verify if the current password is correct
      $hashed_password = $user_data['password'];
      if (password_verify($current_password, $hashed_password)) {
          // Check if new password matches the confirmation
          if ($new_password === $confirm_password) {
              // Update the password in the database
              $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
              $id = $user_data['id'];
              $query = "UPDATE users SET password='$hashed_new_password' WHERE id='$id'";
              $result = mysqli_query($connection, $query);
              if ($result) {
                  // Password updated successfully
                  $_SESSION['alert'] = ['message' => 'Password updated successfully!', 'type' => 'success'];
              } else {
                  // Error updating password
                  $_SESSION['alert'] = ['message' => 'Error updating password!', 'type' => 'error'];
              }
          } else {
              // New password and confirmation do not match
              $_SESSION['alert'] = ['message' => 'New password and confirmation do not match!', 'type' => 'error'];
          }
      } else {
          // Current password is incorrect
          $_SESSION['alert'] = ['message' => 'Current password is incorrect!', 'type' => 'error'];
      }

      header("Location: Admin Change Password.php");
      exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Change Password</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
  
</head>
<body>
<header>
  <div class="logo_header">
    <a href="../Admin Interface/Admin Home.php"> 
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo">
    </a> 
  </div>
  <div class="profile-dropdown">
    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
    <label for="profile-dropdown-toggle" class="profile-dropdown">
      <img src="/mapecon/Pictures/profile.png" alt="Profile">
      <div class="dropdown-content">
        <a href="Admin Profile.php">Profile </a>
        <a href="Admin Change Password.php">Change Password</a>
        <a href="../sql/logout.php">Logout</a>
      </div>
    </label>
  </div>
</header>
<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span>  HR<div id="date-time"></div></div>
  
  <!-- Content -->
 <div class="content" id="content">

    <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Admin Home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>

  <!-- Change Password Form -->
  <div class="change-password" >
    <h2>Change Password</h2>
    <?php
      if (isset($_SESSION['alert'])) {
          $alert_type = $_SESSION['alert']['type'] == 'success' ? 'alert-success' : 'alert-error';
          echo '<div class="alert ' . $alert_type . '">' . $_SESSION['alert']['message'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
          unset($_SESSION['alert']);
      }
    ?>
    <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post">
      <label for="current-password">Current Password:</label>
      <input type="password" id="password" name="current-password" required>

      <label for="new-password">New Password:</label>
      <input type="password" id="password" name="new-password" required>

      <label for="confirm-new-password">Confirm New Password:</label>
      <input type="password" id="password" name="confirm-new-password" required>

      <div class="buttons">
        <button type="button">Cancel</button>
        <button type="submit" id="submit-btn">Save</button>
      </div>
    </form>
  </div>
</div>
</body>

<script>

function updateTime() {
    
    var today = new Date();
    var time = today.toLocaleTimeString();
    var options = { month: 'long', day: 'numeric', year: 'numeric' };
    var date = today.toLocaleDateString("en-US", options); // May 12, 2024
    
    document.getElementById("date-time").innerHTML = "Today is " +  date + " | " + time;
    setTimeout(updateTime, 1000); // Update time every second
  }

  updateTime();

  function toggleNav() {
    var sidebar = document.getElementById("sidebar");
    var content = document.getElementById("content");
    var overlay = document.getElementById("overlay");
    var openButton = document.querySelector(".openbtn");
  
    if (sidebar.style.width === "250px") {
      closeSidebar();
    } else {
      openSidebar();
    }
  }
  
  function openSidebar() {
    var sidebar = document.getElementById("sidebar");
    var content = document.getElementById("content");
    var overlay = document.getElementById("overlay");
    var openButton = document.querySelector(".openbtn");
  
    sidebar.style.width = "250px";
    sidebar.style.visibility = "visible";
    openButton.innerHTML = "&#10005;"; // Change icon to close symbol
  
    if (window.innerWidth <= 768) { // Mobile and tablet breakpoint
      overlay.style.display = "block"; // Display overlay
    } else {
      content.style.marginLeft = "250px"; // Move content to the right
    }
  }
  
  function closeSidebar() {
    var sidebar = document.getElementById("sidebar");
    var content = document.getElementById("content");
    var overlay = document.getElementById("overlay");
    var openButton = document.querySelector(".openbtn");
  
    sidebar.style.width = "0";
    sidebar.style.visibility = "hidden";
    openButton.innerHTML = "&#9776;"; // Change icon to hamburger
  
    if (window.innerWidth <= 768) { // Mobile and tablet breakpoint
      overlay.style.display = "none"; // Hide overlay
    } else {
      content.style.marginLeft = "0"; // Move content back to its original position
    }
  }
  
  // Close sidebar when clicking outside it
  window.onclick = function(event) {
    if (!event.target.matches('.openbtn') && !event.target.matches('#sidebar')) {
      if (document.getElementById("sidebar").style.width === "250px") {
        closeSidebar();
      }
    }
  }
</script>
</html>
