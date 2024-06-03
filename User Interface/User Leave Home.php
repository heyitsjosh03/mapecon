    <?php
    session_start();

      include("../sql/config.php");
      include("../sql/function.php");
      $user_data = check_login($connection);

      /*// Check if the user is logged in
      if (!isset($_SESSION['user_id'])) {
      header("Location: ../login.php");
      exit();
      }*/

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
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Leave Home</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
</head>
<body>
<header>
  <div class="logo_header">
    <a href="../User Interface/User Leave Home.php"> 
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo">
    </a> 
  </div>
  <div class="profile-dropdown">
    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
    <label for="profile-dropdown-toggle" class="profile-dropdown">
      <img src="/mapecon/Pictures/profile.png" alt="Profile">
      <div class="dropdown-content">
        <a href="../User Interface/User Profile.php">Profile </a>
        <a href="../User Interface/User Change Password.php">Change Password</a>
        <a href="../sql/logout.php">Logout</a>
      </div>
    </label>
  </div>
</header>
  <div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span>  EMP<div id="name-greeting">Welcome <span class='user-name'><?php echo $firstName; ?></span>!</div></div>
  
  <!-- Content -->
 <div class="content" id="content">

   <!-- Sidebar -->
   <div class="sidebar" id="sidebar">
    <a href="/mapecon/User Interface/User Leave Home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
    <span class="leave-label">NAVIGATE</span>
    <a href="/mapecon/User Interface/User Leave Form.php"><i class="fa fa-file-text-o"></i>Leave Application</a>
    <a href="/mapecon/User Interface/User Leave History.php"><i class="fa fa-file-word-o"></i> Leave History</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  <div class="card-container">
    <div class="card-container-wrapper">
      <div class="card" onclick="location.href='User Leave Form.php';" style="cursor: pointer;">
        <div class="card-content">
          <img src="/mapecon/Pictures/calendar_icon.png" alt="Leave Filing Icon">
        </div>
      </div>
      <p class="phrase">Leave Application</p>
    </div>
    <div class="card-container-wrapper">
      <div class="card" onclick="location.href='User Leave History.php';" style="cursor: pointer;">
        <div class="card-content">
          <img src="/mapecon/Pictures/history_icon.png" alt="Leave History Icon">
        </div>
      </div>
      <p class="phrase">Leave History</p>
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
