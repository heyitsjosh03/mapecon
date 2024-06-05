<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

$update_msg = ""; // Initialize the update message
$errors = []; // Array to store validation errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $contact = $_POST['contact'];
    $department = $_POST['department'];

    // Validate first name
    if (empty($fname)) {
      $errors['fname'] = "First name is required.";
  }

  // Validate last name
  if (empty($lname)) {
      $errors['lname'] = "Last name is required.";
  }

  // Validate contact
  if (empty($contact)) {
      $errors['contact'] = "Contact number is required.";
  }

  // Validate department
  if (empty($department)) {
      $errors['department'] = "Department is required.";
  }

  // If no errors, proceed with the update
  if (empty($errors)) {
      $query = "UPDATE users 
                SET firstname = '$fname', lastname = '$lname', contactnumber = '$contact', department = '$department' 
                WHERE user_id = " . $_SESSION['user_id'];

      if (mysqli_query($connection, $query)) {
          $_SESSION['alert'] = ['message' => 'Profile updated successfully!', 'type' => 'success'];
      } else {
          $_SESSION['alert'] = ['message' => 'Error updating profile: ' . mysqli_error($connection), 'type' => 'error'];
      }

      header("Location: Admin Profile.php");
      exit;
  }
}

// Fetch user's current profile data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($connection, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Profile</title>
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

  <div class="profile-edit">
    <h2>Edit Profile</h2>
    <?php
    if (isset($_SESSION['alert'])) {
        $alert_type = $_SESSION['alert']['type'] == 'success' ? 'alert-success' : 'alert-error';
        echo '<div class="alert ' . $alert_type . '">' . $_SESSION['alert']['message'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
        unset($_SESSION['alert']);
    }
    ?>
    <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post">
      <label for="fname">First Name:</label>
      <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($fname ?? $row['firstname']); ?>" required>
      <?php if (isset($errors['fname'])): ?><span class="error"><?php echo $errors['fname']; ?></span><?php endif; ?>

      <label for="lname">Last Name:</label>
      <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($lname ?? $row['lastname']); ?>" required>
      <?php if (isset($errors['lname'])): ?><span class="error"><?php echo $errors['lname']; ?></span><?php endif; ?>

      <label for="contact">Contact:</label>
      <input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($contact ?? $row['contactnumber']); ?>" required>
      <?php if (isset($errors['contact'])): ?><span class="error"><?php echo $errors['contact']; ?></span><?php endif; ?>

      <label for="department">Department:</label>
      <div class="department-edit">
        <select name="department" id="department-edit" required>
          <option value="">Select</option>
          <option value="Accounting" <?php echo (isset($department) && $department == 'Accounting') ? 'selected' : ($row['department'] == 'Accounting' ? 'selected' : ''); ?>>Accounting</option>
          <option value="Admin" <?php echo (isset($department) && $department == 'Admin') ? 'selected' : ($row['department'] == 'Admin' ? 'selected' : ''); ?>>Admin and Shared Services</option>
          <option value="Ads" <?php echo (isset($department) && $department == 'Ads') ? 'selected' : ($row['department'] == 'Ads' ? 'selected' : ''); ?>>Ads and Promo</option>
          <option value="Business" <?php echo (isset($department) && $department == 'Business') ? 'selected' : ($row['department'] == 'Business' ? 'selected' : ''); ?>>Business Development Group</option>
          <option value="Chem Room" <?php echo (isset($department) && $department == 'Chem Room') ? 'selected' : ($row['department'] == 'Chem Room' ? 'selected' : ''); ?>>Chem Room</option>
          <option value="Clinic" <?php echo (isset($department) && $department == 'Clinic') ? 'selected' : ($row['department'] == 'Clinic' ? 'selected' : ''); ?>>Clinic</option>
          <option value="Collection" <?php echo (isset($department) && $department == 'Collection') ? 'selected' : ($row['department'] == 'Collection' ? 'selected' : ''); ?>>Collection</option>
          <option value="EVP" <?php echo (isset($department) && $department == 'EVP') ? 'selected' : ($row['department'] == 'EVP' ? 'selected' : ''); ?>>EVP Office</option>
          <option value="Greenovations-Floor" <?php echo (isset($department) && $department == 'Greenovations-Floor') ? 'selected' : ($row['department'] == 'Greenovations-Floor' ? 'selected' : ''); ?>>Greenovations (1st and 2nd Floor)</option>
          <option value="Greenovations-Table" <?php echo (isset($department) && $department == 'Greenovations-Table') ? 'selected' : ($row['department'] == 'Greenovations-Table' ? 'selected' : ''); ?>>Greenovations (MGCPI Table)</option>
          <option value="Operator-HR" <?php echo (isset($department) && $department == 'Operator-HR') ? 'selected' : ($row['department'] == 'Operator-HR' ? 'selected' : ''); ?>>Operator and HR</option>
          <option value="OTD" <?php echo (isset($department) && $department == 'OTD') ? 'selected' : ($row['department'] == 'OTD' ? 'selected' : ''); ?>>OTD</option>
          <option value="Research" <?php echo (isset($department) && $department == 'Research') ? 'selected' : ($row['department'] == 'Research' ? 'selected' : ''); ?>>Research and Development</option>
          <option value="Sales" <?php echo (isset($department) && $department == 'Sales') ? 'selected' : ($row['department'] == 'Sales' ? 'selected' : ''); ?>>Sales</option>
          <option value="Service" <?php echo (isset($department) && $department == 'Service') ? 'selected' : ($row['department'] == 'Service' ? 'selected' : ''); ?>>Service</option>
        </select>

        <?php if (isset($errors['department'])): ?><span class="error"><?php echo $errors['department']; ?></span><?php endif; ?>
          
      </div>

      <div class="buttons">
        <button type="button" onclick="window.location.href='/mapecon/User Interface/User Leave Home.php';">Cancel</button>
        <button type="submit" id="submit-btn">Save</button>
      </div>
    </form>
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
