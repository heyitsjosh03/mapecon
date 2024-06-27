<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

$update_msg = ""; // Initialize the update message
$errors = []; // Array to store validation errors

// Fetch user's current profile data
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($connection, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
    } else {
        die('Error: User not found.');
    }
} else {
    die('Error: User ID not provided.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utype = $_POST['user_status'];
    $user_id = $_POST['user_id'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $contact = $_POST['contactnumber'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $approver_id = $_POST['approver_id'];

    // Validate user type
    if (empty($utype)) {
        $errors['user_status'] = "User Type is required.";
    }

    // Validate first name
    if (empty($fname)) {
        $errors['firstname'] = "First name is required.";
    }

    // Validate last name
    if (empty($lname)) {
        $errors['lastname'] = "Last name is required.";
    }

    // Validate contact
    if (empty($contact)) {
        $errors['contactnumber'] = "Contact number is required.";
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    }

    // Validate department
    if (empty($department)) {
        $errors['department'] = "Department is required.";
    }

    // Validate approver
    if (empty($approver_id)) {
        $errors['approver_id'] = "Supervisor is required.";
    }

    // If no errors, proceed with the update
    if (empty($errors)) {
        $query = "UPDATE users 
                  SET user_status = '$utype', firstname = '$fname', lastname = '$lname', contactnumber = '$contact', email = '$email', department = '$department', approver_id = '$approver_id'
                  WHERE user_id = $user_id";

        if (mysqli_query($connection, $query)) {
            $_SESSION['alert'] = ['message' => 'Profile updated successfully!', 'type' => 'success'];
        } else {
            $_SESSION['alert'] = ['message' => 'Error updating profile: ' . mysqli_error($connection), 'type' => 'error'];
        }

        header("Location: edit user.php?user_id=$user_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
  <style>
    form label {
      display: block;
      font-weight: bold;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
<header>
  <div class="logo_header">
    <a href="../Hr Interface/Hr Home.php"> 
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo">
    </a> 
  </div>
  <div class="profile-dropdown">
    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
    <label for="profile-dropdown-toggle" class="profile-dropdown">
      <img src="/mapecon/Pictures/profile.png" alt="Profile">
      <div class="dropdown-content">
        <a href="Hr Profile.php">Profile </a>
        <a href="Hr Change Password.php">Change Password</a>
        <a href="../sql/logout.php">Logout</a>
      </div>
    </label>
  </div>
</header>

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span> HR (Human Resources Management) <div id="date-time"></div></div>

<!-- Content -->
<div class="content" id="content">
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Hr Home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approval Leaves.php"><i class="fa fa-file-text-o"></i>Request for Approval</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Add users.php"><i class="fa fa-user-o"></i> Add Users</a>
    <a href="Users Table.php"  id="active"><i class="fa fa-user-o"></i> Edit Users</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>

  <div class="profile-edit">
    <h2>Edit User Profile</h2>
    <?php
    if (isset($_SESSION['alert'])) {
        $alert_type = $_SESSION['alert']['type'] == 'success' ? 'alert-success' : 'alert-error';
        echo '<div class="alert ' . $alert_type . '">' . $_SESSION['alert']['message'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
        unset($_SESSION['alert']);
    }
    ?>
    <form id="editForm" action="<?php echo($_SERVER["PHP_SELF"]); ?>?user_id=<?php echo $user_id; ?>" method="post" onsubmit="return validateForm()">
      <label for="user_status">User Type:</label>
      <div class="department-edit">
        <select name="user_status" id="user_status" required>
          <option value="">Select</option>
          <option value="HR" <?php echo (isset($row['user_status']) && $row['user_status'] == 'HR') ? 'selected' : ''; ?>>HR</option>
          <option value="Approver" <?php echo (isset($row['user_status']) && $row['user_status'] == 'Approver') ? 'selected' : ''; ?>>Approver</option>
          <option value="User" <?php echo (isset($row['user_status']) && $row['user_status'] == 'User') ? 'selected' : ''; ?>>User</option>
        </select>
        <?php if (isset($errors['user_status'])): ?><span class="error"><?php echo $errors['user_status']; ?></span><?php endif; ?>
      </div>

      <label for="user_id">Employee ID:</label>
      <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($row['user_id']); ?>" readonly>

      <label for="firstname">First Name:</label>
      <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($row['firstname']); ?>" required>
      <?php if (isset($errors['firstname'])): ?><span class="error"><?php echo $errors['firstname']; ?></span><?php endif; ?>

      <label for="lastname">Last Name:</label>
      <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($row['lastname']); ?>" required>
      <?php if (isset($errors['lastname'])): ?><span class="error"><?php echo $errors['lastname']; ?></span><?php endif; ?>

      <label for="contactnumber">Contact Number:</label>
      <input type="tel" id="contactnumber" name="contactnumber" value="<?php echo htmlspecialchars($row['contactnumber']); ?>" required>
      <?php if (isset($errors['contactnumber'])): ?><span class="error"><?php echo $errors['contactnumber']; ?></span><?php endif; ?>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
      <?php if (isset($errors['email'])): ?><span class="error"><?php echo $errors['email']; ?></span><?php endif; ?>

      <label for="department">Department:</label>
      <div class="department-edit">
        <select name="department" id="department" required>
          <option value="">Select</option>
          <option value="Accounting" <?php echo (isset($row['department']) && $row['department'] == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
          <option value="Admin and Shared Services" <?php echo (isset($row['department']) && $row['department'] == 'Admin and Shared Services') ? 'selected' : ''; ?>>Admin and Shared Services</option>
          <option value="Ads and Promo" <?php echo (isset($row['department']) && $row['department'] == 'Ads and Promo') ? 'selected' : ''; ?>>Ads and Promo</option>
          <option value="Business Development Group" <?php echo (isset($row['department']) && $row['department'] == 'Business Development Group') ? 'selected' : ''; ?>>Business Development Group</option>
          <option value="Chem Room" <?php echo (isset($row['department']) && $row['department'] == 'Chem Room') ? 'selected' : ''; ?>>Chem Room</option>
          <option value="Clinic" <?php echo (isset($row['department']) && $row['department'] == 'Clinic') ? 'selected' : ''; ?>>Clinic</option>
          <option value="Collection" <?php echo (isset($row['department']) && $row['department'] == 'Collection') ? 'selected' : ''; ?>>Collection</option>
          <option value="EVP Office" <?php echo (isset($row['department']) && $row['department'] == 'EVP Office') ? 'selected' : ''; ?>>EVP Office</option>
          <option value="Greenovations-Floor" <?php echo (isset($row['department']) && $row['department'] == 'Greenovations-Floor') ? 'selected' : ''; ?>>Greenovations (1st and 2nd Floor)</option>
          <option value="Greenovations-Table" <?php echo (isset($row['department']) && $row['department'] == 'Greenovations-Table') ? 'selected' : ''; ?>>Greenovations (MGCPI Table)</option>
          <option value="Operator and HR" <?php echo (isset($row['department']) && $row['department'] == 'Operator and HR') ? 'selected' : ''; ?>>Operator and HR</option>
          <option value="OTD" <?php echo (isset($row['department']) && $row['department'] == 'OTD') ? 'selected' : ''; ?>>OTD</option>
          <option value="Research and Development"  class="department" <?php echo (isset($row['department']) && $row['department'] == 'Research and Development') ? 'selected' : ''; ?>>Research and Development</option>
          <option value="Sales" <?php echo (isset($row['department']) && $row['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
          <option value="Service" <?php echo (isset($row['department']) && $row['department'] == 'Service') ? 'selected' : ''; ?>>Service</option>
        </select>
        <?php if (isset($errors['department'])): ?><span class="error"><?php echo $errors['department']; ?></span><?php endif; ?>
      </div>

      <label for="approver_id">Supervisor ID:</label>
      <input type="text" id="approver_id" name="approver_id" value="<?php echo htmlspecialchars($row['approver_id']); ?>" required onblur="fetchSupervisorName()">
      <?php if (isset($errors['approver_id'])): ?><span class="error"><?php echo $errors['approver_id']; ?></span><?php endif; ?>

      <label for="approver_name">Supervisor Name:</label>
      <input type="text" id="approver_name" name="approver_name" readonly>

      <div class="buttons">
        <button type="button" onclick="window.location.href='/mapecon/Hr Interface/Users Table.php';">Cancel</button>
        <button type="submit" id="submit-btn">Save</button>
      </div>
    </form>
  </div>
</div>

<script>

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
function updateTime() {
  var today = new Date();
  var time = today.toLocaleTimeString();
  var options = { month: 'long', day: 'numeric', year: 'numeric' };
  var date = today.toLocaleDateString("en-US", options); // May 12, 2024
  
  document.getElementById("date-time").innerHTML = "Today is " + date + " | " + time;
  setTimeout(updateTime, 1000); // Update time every second
}

updateTime();

function fetchSupervisorName() {
  var approverId = document.getElementById("approver_id").value;
  if (approverId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_supervisor_name.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("approver_name").value = xhr.responseText;
      }
    };
    xhr.send("approver_id=" + approverId);
  }
}

function validateForm() {
  var approverId = document.getElementById("approver_id").value;
  if (!approverId) {
    alert("Supervisor is Required!");
    return false;
  }
  return true;
}

window.onload = function() {
  fetchSupervisorName();
};
</script>
</body>
</html>
