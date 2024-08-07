<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);
// Check if the user is logged in
/*if (!isset($_SESSION['user_id'])) {
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

// Retrieve data for pending, approved, and declined leaves
$queryPending = "SELECT COUNT(*) AS pending_count FROM leave_applications WHERE status = 'Pending'";
$queryApproved = "SELECT COUNT(*) AS approved_count FROM leave_applications WHERE status = 'Approved'";
$queryDeclined = "SELECT COUNT(*) AS declined_count FROM leave_applications WHERE status = 'Declined'";

$resultPending = mysqli_query($connection, $queryPending);
$resultApproved = mysqli_query($connection, $queryApproved);
$resultDeclined = mysqli_query($connection, $queryDeclined);

$rowPending = mysqli_fetch_assoc($resultPending);
$rowApproved = mysqli_fetch_assoc($resultApproved);
$rowDeclined = mysqli_fetch_assoc($resultDeclined);


// Retrieve counts for each leave type
$queryCasual = "SELECT COUNT(*) AS casual_count FROM leave_applications WHERE leave_type = 'Casual Leave' AND status = 'Approved'";
$queryCompensatory = "SELECT COUNT(*) AS compensatory_count FROM leave_applications WHERE leave_type = 'Compensatory Off' AND status = 'Approved'";
$queryWithoutPay = "SELECT COUNT(*) AS withoutpay_count FROM leave_applications WHERE leave_type = 'Leave without Pay' AND status = 'Approved'";
$queryPrivilege = "SELECT COUNT(*) AS privilege_count FROM leave_applications WHERE leave_type = 'Privilege Leave' AND status = 'Approved'";
$querySick = "SELECT COUNT(*) AS sick_count FROM leave_applications WHERE leave_type = 'Sick Leave' AND status = 'Approved'";
$queryVacation = "SELECT COUNT(*) AS vacation_count FROM leave_applications WHERE leave_type = 'Vacation Leave' AND status = 'Approved'";

$resultCasual = mysqli_query($connection, $queryCasual);
$resultCompensatory = mysqli_query($connection, $queryCompensatory);
$resultWithoutPay = mysqli_query($connection, $queryWithoutPay);
$resultPrivilege = mysqli_query($connection, $queryPrivilege);
$resultSick = mysqli_query($connection, $querySick);
$resultVacation = mysqli_query($connection, $queryVacation);

$rowCasual = mysqli_fetch_assoc($resultCasual);
$rowCompensatory = mysqli_fetch_assoc($resultCompensatory);
$rowWithoutPay = mysqli_fetch_assoc($resultWithoutPay);
$rowPrivilege = mysqli_fetch_assoc($resultPrivilege);
$rowSick = mysqli_fetch_assoc($resultSick);
$rowVacation = mysqli_fetch_assoc($resultVacation);


// Close database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Approver</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
  <link rel="stylesheet" href="/mapecon/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js library -->
</head>
<body>
<header>
  <div class="logo_header">
    <a href="../Approver Interface/Approver home.php"> 
      <img src="/mapecon/Pictures/MAPECON_logo.png" alt="MAPECON Logo">
    </a> 
  </div>
  <div class="profile-dropdown">
    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
    <label for="profile-dropdown-toggle" class="profile-dropdown">
      <img src="/mapecon/Pictures/profile.png" alt="Profile">
      <div class="dropdown-content">
        <a href="Approver Profile.php">Profile </a>
        <a href="Approver Change Password.php">Change Password</a>
        <a href="../sql/logout.php">Logout</a>
      </div>
    </label>
  </div>
</header>
<div class="menu">
  <span class="openbtn" onclick="toggleNav()">&#9776;</span>  
   Approver
  <div id="name-greeting">Welcome <span class='user-name'><?php echo $firstName; ?></span>!</div>
</div>
    
<!-- Content -->
<div class="content" id="content">

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Approver home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
    <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Approver Leave Form.php"><i class="fa fa-file-text-o"></i> Leave Application </a>
    <a href="Approver History.php"><i class="fa fa-file-text-o"></i> Leave History</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>

  <!-- Approver Home content -->
  <div class="admin-container">
    <div class="admin-home">
      <div class="dashboard-content">
      
        <div class="card-container-admin">
          <div class="card-container-wrapper">
            <div class="card" onclick="location.href='/mapecon/Approver Interface/Pending Leaves.php';" style="cursor: pointer;">
              <div class="card-content">
                <img src="/mapecon/Pictures/pending.png" alt="Pending">
              </div>
            </div>
            <p class="phrase">Pending</p>
          </div>
          <div class="card-container-wrapper">
            <div class="card" onclick="location.href='/mapecon/Approver Interface/Approved Leaves.php';" style="cursor: pointer;">
              <div class="card-content">
                <img src="/mapecon/Pictures/approved.png" alt="Approved">
              </div>
            </div>
            <p class="phrase">Approved</p>
          </div>
          <div class="card-container-wrapper">
            <div class="card" onclick="location.href='/mapecon/Approver Interface/Declined Leaves.php';" style="cursor: pointer;">
              <div class="card-content">
                <img src="/mapecon/Pictures/declined.png" alt="Declined">
              </div>
            </div>
            <p class="phrase">Declined</p>
          </div>
          <div class="card-container-wrapper">
          <div class="card" onclick="location.href='Approver Leave Form.php';" style="cursor: pointer;">
              <div class="card-content">
              <img src="/mapecon/Pictures/calendar_icon.png" alt="Leave Filing Icon">
              </div>
            </div>
            <p class="phrase">Leave Application</p>
          </div>

    <div class="card-container-wrapper">
      <div class="card" onclick="location.href='Approver History.php';" style="cursor: pointer;">
        <div class="card-content">
          <img src="/mapecon/Pictures/history_icon.png" alt="Leave History Icon">
        </div>
      </div>
      <p class="phrase">Leave History</p>
    </div>
  </div>
</div>
        </div>
      </div>
    </div>

<script>

  document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('leaveChart').getContext('2d');
    var myChart = new Chart(ctx, {
      type: 'pie', // Change chart type to pie
      data: {
        labels: ['Pending', 'Approved', 'Declined'],
        datasets: [{
          label: 'Leave Count',
          data: [
            <?php echo $rowPending['pending_count']; ?>, 
            <?php echo $rowApproved['approved_count']; ?>, 
            <?php echo $rowDeclined['declined_count']; ?>
          ],
          backgroundColor: [
            'rgb(58, 58, 58)', // Pending - Gray
            'rgba(255,214,213,255)', // Approved - Light Pink
            'rgb(192, 0, 0)' // Declined - Red
          ],
          borderColor: [
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          position: 'center'
        }
      }
    });

    // Function to update chart based on selected month and year
    function updateChart() {
      var selectedMonth = document.getElementById("monthFilter-dashboard").value;
      var selectedYear = document.getElementById("yearFilter-dashboard").value;

      // Fetch data based on selected month and year
      fetch('update_chart.php?month=' + selectedMonth + '&year=' + selectedYear)
        .then(response => response.json())
        .then(data => {
          myChart.data.datasets[0].data = [data.pending_count, data.approved_count, data.declined_count];
          myChart.update();
        });
    }

    // Listen for changes in month and year filters
    document.getElementById("monthFilter-dashboard").addEventListener("change", updateChart);
    document.getElementById("yearFilter-dashboard").addEventListener("change", updateChart);
  });
</script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var ctx = document.getElementById('leavetypeChart').getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'bar', // Change chart type to bar
      data: {
        labels: ['Casual Leave', 'Compensatory Off', 'Leave without Pay', 'Privilege Leave', 'Sick Leave', 'Vacation Leave'],
        datasets: [{
          label: 'Leave Count',
          data: [
            <?php echo $rowCasual['casual_count']; ?>, 
            <?php echo $rowCompensatory['compensatory_count']; ?>, 
            <?php echo $rowWithoutPay['withoutpay_count']; ?>, 
            <?php echo $rowPrivilege['privilege_count']; ?>, 
            <?php echo $rowSick['sick_count']; ?>, 
            <?php echo $rowVacation['vacation_count']; ?>
          ],
          backgroundColor: [
            'rgb(58, 58, 58)', // Casual Leave - Gray
            'rgba(255,214,213,255)', // Compensatory Off - Light Pink
            'rgb(192, 0, 0)', // Leave without Pay - Red
            'rgb(0, 128, 0)', // Privilege Leave - Green
            'rgb(0, 0, 255)', // Sick Leave - Blue
            'rgb(255, 165, 0)' // Vacation Leave - Orange
          ],
          borderColor: [
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)'
          ],
          borderWidth: 1
        }]
      },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: 'center'
          }
        }
      });

      // Function to update chart based on selected month and year
      function updateChart() {
        var selectedMonth = document.getElementById("monthFilter-dashboard").value;
        var selectedYear = document.getElementById("yearFilter-dashboard").value;

        // Fetch data based on selected month and year
        fetch('update_chart2.php?month=' + selectedMonth + '&year=' + selectedYear)
          .then(response => response.json())
          .then(data => {
            myChart.data.datasets[0].data = [data.casual_count, data.compensatory_count, data.withoutpay_count,
            data.privilege_count, data.sick_count, data.vacation_count
            ];
            myChart.update();
          });
      }

      // Listen for changes in month and year filters
      document.getElementById("monthFilter-dashboard").addEventListener("change", updateChart);
      document.getElementById("yearFilter-dashboard").addEventListener("change", updateChart);
    });
  </script>

<script>
  // JavaScript functions for sidebar toggling
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
<footer>
<div class="footer-bottom">
    &copy; 2024 MAPECON Philippines Inc. | Designed by Your Company
  </div>
</footer>
</body>
</html>

