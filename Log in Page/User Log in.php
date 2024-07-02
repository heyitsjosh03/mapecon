<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_status']) {
        case 'Hr':
            header("Location: ../Hr Interface/Hr Home.php");
            exit;
        case 'Approver':
            header("Location: ../Approver Interface/Approver home.php");
            exit;
        case 'User':
            header("Location: ../User Interface/User Leave Home.php");
            exit;
        default:
            // Handle other cases if necessary
            break;
    }
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Something was posted
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Read from database
        $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($connection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $stored_hashed_password = $user_data['password'];

            if (password_verify($password, $stored_hashed_password)) {
                $_SESSION['user_id'] = $user_data['user_id'];
                $_SESSION['user_status'] = $user_data['user_status'];

                // Check if "Remember Me" is checked
                if (isset($_POST['remember_me'])) {
                    // Set cookies for email and password
                    setcookie('email', $email, time() + (86400 * 30), "/");
                    setcookie('password', $password, time() + (86400 * 30), "/");
                }

                if ($user_data['user_status'] == 'Hr') {
                    header("Location: ../Hr Interface/Hr Home.php");
                    die;
                } elseif ($user_data['user_status'] == 'User') {
                    header("Location: ../User Interface/User Leave Home.php");
                    die;
                }elseif ($user_data['user_status'] == 'Approver') {
                    header("Location: ../Approver Interface/Approver home.php");
                    die;
                }

                
            } else {
                $_SESSION['alert'] = 'Wrong password. Please try again.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            $_SESSION['alert'] = 'User not found. Please register.';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['alert'] = 'Please fill out the blank form.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
    $mail = $_COOKIE['email'];
    $pass = $_COOKIE['password'];
} else {
    $mail = "";
    $pass = "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
</head>

<body class="no-header-padding">
  <div class="background-image">
  </div>
  <div class="container-login">
    <div class="login-form">
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo" class="logo"> 
      <h2>Welcome to Leave Simulation System!</h2>
      <p>Log in to access our Leave Management System, streamlining our leave application process.</p>
      <?php
            if (isset($_SESSION['alert'])) {
                echo '<div class="alert">' . $_SESSION['alert'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
                unset($_SESSION['alert']);
            }
            else if (isset($_SESSION['alert-success'])) {
                echo '<div class="alert-success">' . $_SESSION['alert-success'] . '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button></div>';
                unset($_SESSION['alert-success']);
            }
            ?>
      <form action="" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo $mail; ?>">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password" value="<?php echo $pass; ?>">
            <div class="login-form-footer">
                <div class="remember-forgot-container">
                    <label class="remember-me">
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="forgot password.php" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </div>
        </form>
    </div>
  </div>
  <script src="path/to/script.js"></script>
</body>

</html>
