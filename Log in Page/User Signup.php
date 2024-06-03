<?php
session_start();
  include("../sql/config.php");
  include("../sql/function.php");

  //isset($signuser) || isset($signpass) || isset($signmail) || isset($signconpass)
  //$_SERVER['REQUEST_METHOD'] == "POST"
  if($_SERVER['REQUEST_METHOD'] == "POST"){
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

                  $query = "INSERT INTO users (user_id, user_status, firstname, lastname, contactnumber, email, password, department) 
                            VALUES ('$user_id', 'User', '$fname', '$lname', '$contact', '$email', '$hashed_password', '$department')";
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
  <title>Sign Up</title>
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
</head>

<body class="no-header-padding">
  <div class="background-image-1">
  </div>
  <div class="container-sign">
    <div class="sign-form">
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo" class="logo"> 
      <h2>Sign Up</h2>
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
          <option value="Greenovations (1st and 2nd Floor)">Greenovations (1st and 2nd Floor)</option>
          <option value="Greenovations (MGCPI Table)">Greenovations (MGCPI Table)</option>
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
        <button type="submit" class="login-btn">Register</button>  
      </form>
      <div class="sign-up-link">
        Have an account? <a href="User Log in.php">Login</a>
      </div>
    </div>
  </div>
</body>
</html>