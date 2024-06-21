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
    $firstName = htmlspecialchars($rowUser["firstname"]); // Escape for security
} else {
    $firstName = "User";
}

function generate5DigitNumber() {
    return str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

function numberExistsInDatabase($number, $connection) {
    $query = "SELECT COUNT(*) AS count FROM users WHERE user_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $number);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Handle form submission and CSV import
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
        $fileName = $_FILES['csv_file']['tmp_name'];

        if ($_FILES['csv_file']['size'] > 0) {
            $file = fopen($fileName, 'r');

            // Skip the first line if it contains column headers
            fgetcsv($file);

            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                $utype = $column[2];
                $fname = ucwords($column[3]);
                $lname = ucwords($column[4]);
                $contact = $column[5];
                $email = $column[6];
                $password = $column[7];
                $department = $column[8];
                $approver_id = !empty($column[11]) ? intval($column[11]) : null; // Ensure approver_id is an integer or NULL

                // Check if the email already exists in the database
                $check_email = "SELECT email FROM users WHERE email = ? LIMIT 1";
                $stmt = $connection->prepare($check_email);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $_SESSION['alert'] = 'Email address already exists: ' . $email;
                } else {
                    if (!empty($fname) && !empty($lname) && !empty($contact) && !empty($department) && !empty($email) && !empty($password)) {
                        if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            $user_id = generate5DigitNumber();
                            while (numberExistsInDatabase($user_id, $connection)) {
                                $user_id = generate5DigitNumber();
                            }

                            $query = "INSERT INTO users (user_status, user_id, firstname, lastname, contactnumber, email, password, department, approver_id) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $connection->prepare($query);
                            $stmt->bind_param("sssssssss", $utype, $user_id, $fname, $lname, $contact, $email, $hashed_password, $department, $approver_id);
                            $query_run = $stmt->execute();

                            if ($query_run) {
                                $_SESSION['alert-success'] = 'CSV Import successful!';
                            } else {
                                $_SESSION['alert'] = 'CSV Import Failed for: ' . $email;
                            }
                        } else {
                            $_SESSION['alert'] = 'Password does not meet requirements for: ' . $email;
                        }
                    }
                }
            }

            fclose($file);
            header("Location: add users.php");
            exit;
        }
    }
    // Existing form submission code
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
  <script>
    function fetchSupervisorName() {
      var approverId = document.getElementById('approver_id').value;
      if (approverId) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetch_supervisor.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('supervisor_name').value = xhr.responseText;
          }
        };
        xhr.send('approver_id=' + encodeURIComponent(approverId));
      }
    }
  </script>
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
    <a href="Hr Home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approval Leaves.php"><i class="fa fa-file-text-o"></i>Request for Approval</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Add users.php" id="active"><i class="fa fa-user-o"></i> Add Users</a>
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
          if (isset($_SESSION['alert-success'])) {
              echo '<div class="alert-success">' . $_SESSION['alert-success'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
              unset($_SESSION['alert-success']);
          }
      ?>
        <form action="<?php echo($_SERVER["PHP_SELF"]); ?>?user_id=<?php echo $user_id; ?>" method="post" enctype="multipart/form-data">
        <label for="csv_file">Upload CSV File:</label>
        <input type="file" id="csv_file"  name="csv_file" accept=".csv" required>
        <button type="submit" name="import_csv" class="login-btn">Import CSV</button>
      </form>
      <br><br><br>
      <form action="<?php echo($_SERVER["PHP_SELF"]); ?>?user_id=<?php echo $user_id; ?>" method="post">
        <label for="user_status">User Type:</label>
      <div class="department-edit">
        <select name="user_status" id="user_status" required>
          <option value="">Select</option>
          <option value="Hr" <?php echo (isset($row['user_status']) && $row['user_status'] == 'Hr') ? 'selected' : ''; ?>>Hr</option>
          <option value="Approver" <?php echo (isset($row['user_status']) && $row['user_status'] == 'Approver') ? 'selected' : ''; ?>>Approver</option>
          <option value="User" <?php echo (isset($row['user_status']) && $row['user_status'] == 'User') ? 'selected' : ''; ?>>User</option>
        </select>
        <?php if (isset($errors['user_status'])): ?><span class="error"><?php echo $errors['user_status']; ?></span><?php endif; ?>
      </div>
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
        <label for="conpassword">Confirm Password:</label>
        <input type="password" id="conpassword" name="conpassword" required placeholder="Re-enter your password">
        <label for="approver_id">Assigned Supervisor ID:</label>
        <input type="text" id="approver_id" name="approver_id" placeholder="Enter your Supervisor ID" oninput="fetchSupervisorName()">
        <label for="supervisor_name">Supervisor Name:</label>
        <input type="text" id="supervisor_name" name="supervisor_name" readonly>
        <button type="submit" class="login-btn">Submit</button>
      </form>
      <br><br>
    </div>
  </div>
</div>

</body>

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
</script>
</html>
