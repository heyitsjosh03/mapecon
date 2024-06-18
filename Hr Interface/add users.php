<?php
session_start();
  include("../sql/config.php");
  include("../sql/function.php");


  
$user_id = $_SESSION['user_id'];

// Retrieve the current user's first name
$queryUser = "SELECT firstname FROM users WHERE user_id = ?";
$stmt = $connection->prepare($queryUser);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultUser = $stmt->get_result();

if ($resultUser->num_rows > 0) {
  $rowUser = $resultUser->fetch_assoc();
  $firstName = $rowUser["firstname"]; // Escape for security
} else {
  $firstName = "User";
}


  //isset($signuser) || isset($signpass) || isset($signmail) || isset($signconpass)
  //$_SERVER['REQUEST_METHOD'] == "POST"
  if($_SERVER['REQUEST_METHOD'] == "POST"){
    $utype = $_POST['utype'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $contact = $_POST['contact'];
    $department = $_POST['department'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];

    $fname = ucwords($fname);
    $lname = ucwords($lname);

    $check_email = "SELECT email FROM users WHERE email = '$email' LIMIT 1";
    $check_email_query = mysqli_query($connection, $check_email);

    if (mysqli_num_rows($check_email_query) > 0) {
      $_SESSION['alert'] = 'Email address already exists';
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    } else {
      if (!empty($fname) && !empty($lname) && !empty($contact) && !empty($department) && !empty($email) && !empty($password)) {
          if ($password == $conpassword) {
              // Validate the password
              if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                  function generate5DigitNumber() {
                      return str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                  }

                  function numberExistsInDatabase($number, $connection) {
                      $query = "SELECT COUNT(*) AS count FROM users WHERE user_id = ?";
                      $statement = $connection->prepare($query);
                      $statement->bind_param("s", $number);
                      $statement->execute();
                      $result = $statement->get_result();
                      $row = $result->fetch_assoc();
                      return $row['count'] > 0;
                  }

                  $user_id = generate5DigitNumber();
                  while (numberExistsInDatabase($user_id, $connection)) {
                      $user_id = generate5DigitNumber();
                  }

                  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                  $query = "INSERT INTO users (user_status, user_id, user_status, firstname, lastname, contactnumber, email, password, department) 
                            VALUES ('$user_id', '$utype', '$fname', '$lname', '$contact', '$email', '$hashed_password', '$department')";
                  $query_run = mysqli_query($connection, $query);

                  if ($query_run) {
                      $_SESSION['alert-success'] = 'Registration successful! Login your account.';
                      header("Location: User Log in.php");
                      exit;
                  } else {
                      $_SESSION['alert'] = 'Registration Failed. Please try again';
                      header("Location: " . $_SERVER['PHP_SELF']);
                      exit;
                  }
              } else {
                  $_SESSION['alert'] = 'Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character';
                  header("Location: " . $_SERVER['PHP_SELF']);
                  exit;
              }
          } else {
              $_SESSION['alert'] = 'Passwords do not match';
              header("Location: " . $_SERVER['PHP_SELF']);
              exit;
          }
      }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js library -->

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
<div class="menu">
  <span class="openbtn" onclick="toggleNav()">&#9776;</span> HR(Human Resources Management) 
  <div id="name-greeting">Welcome <span class='user-name'><?php echo $firstName; ?></span>!</div>
</div>
    
<!-- Content -->
<div class="content" id="content">

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Hr Home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
    <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Approval Leaves.php"><i class="fa fa-file-text-o"></i>Request for Approval</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Users Table.php"><i class="fa fa-user-o"></i> Edit Users</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>

  <div class="container-sign">
    <div class="sign-form">
      <h2>Add Users </h2>
      <br>
      <?php
          if (isset($_SESSION['alert'])) {
              echo '<div class="alert">' . $_SESSION['alert'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
              unset($_SESSION['alert']);
          }
          ?>
      <form action="" method="post">
      <div class="name-fields">
        <div class="form-group">
        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required placeholder="Enter your first name">
        </div>  
        <div class="form-group">
        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required placeholder="Enter your last name">
        </div>
      </div>
        <label for="contact">Contact:</label>
        <input type="tel" id="contact" name="contact" required placeholder="Enter your contact # (Ex. 09#########)">
        <label for="department">Department:</label>
      <div class="department-edit">
        <select name="department" id="department-edit" required>
          <option value="">Select</option>
          <option value="Accounting">Accounting</option>
          <option value="Admin and Shared Services">Admin and Shared Services</option>
          <option value="Ads and Promo">Ads and Promo</option>
          <option value="Business Development Group">Business Development Group</option>
          <option value="Chem Room">Chem Room</option>
          <option value="Clinic">Clinic</option>
          <option value="Collection">Collection</option>
          <option value="EVP Office">EVP Office</option>
          <option value="Greenovations-Floor">Greenovations (1st and 2nd Floor)</option>
          <option value="Greenovations-Table">Greenovations (MGCPI Table)</option>
          <option value="Operator and HR">Operator and HR</option>
          <option value="OTD">OTD</option>
          <option value="Research and Development">Research and Development</option>
          <option value="Sales">Sales</option>
          <option value="Service">Service</option>
        </select>
      </div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="Enter your password">
        <label for="password">Confirm Password:</label>
        <input type="password" id="conpassword" name="conpassword" required placeholder="Re-enter your password">
        <label for="email">Assigned Supervisor ID:</label>
        <input type="email" id="approver_id" name="approver_id" required placeholder="Enter your Supervisor ID">
        <button type="submit" class="login-btn">Submit</button>  
      </form>
    </div>
  </div>
</body>
</html>