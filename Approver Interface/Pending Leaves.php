<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if the session user_id is set
if (!isset($_SESSION['user_id'])) {
    die('Error: User ID is not set in the session.');
}

// Get the approver's id
$approver_id = $_SESSION['user_id'];
$approver_query = "SELECT approver_id FROM users WHERE user_id = '$approver_id'";
$approver_result = mysqli_query($connection, $approver_query);

// Debugging: Output the query result
if (!$approver_result) {
    die('Error: Query failed. ' . mysqli_error($connection));
}

// Fetch leave applications for the same approver as the approver
$sql = "SELECT l.*, UCASE(CONCAT(u.lastname, ', ', u.firstname)) AS full_name
        FROM leave_applications AS l 
        INNER JOIN users AS u ON l.user_id = u.user_id
        WHERE u.approver_id = '$approver_id' 
        ORDER BY l.id DESC";

$result = $connection->query($sql);

// Debugging: Check if the query executed successfully
if (!$result) {
    die('Error: Query failed. ' . $connection->error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pending Leaves</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
<link rel="stylesheet" href="/mapecon/style3.css">
<style>
  td.days-covered {
    text-align: center; /* Center align text in Days Covered column */
}
    th.Action{
      text-align:center;
      padding-left: 14px;
    }
</style>
</head>

<body>
<header>
  <div class="logo_header">
    <a href="../Approver Interface/Approver Home.php"> 
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

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span>  Approver <div id="date-time"></div></div>

 <!-- Content -->
 <div class="content" id="content">
<div class="container_report_report">
  
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Approver home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php" id="active"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Approver Leave Form.php"><i class="fa fa-file-text-o"></i> Leave Application </a>
    <a href="Approver History.php"><i class="fa fa-file-text-o"></i> Leave History</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  
    <div class="leave-report-header">
      <h2>Pending Leaves</h2>
    </div>
    
    <div class="filters">
      <table>
        <tr class="filter-row-pending">
          <th><input type="text" placeholder="Name" id="nameFilter"></th>
          <th>
            <select id="monthFilter-pending">
              <option value="">Month</option>
              <option value="01">January</option>
              <option value="02">February</option>
              <option value="03">March</option>
              <option value="04">April</option>
              <option value="05">May</option>
              <option value="06">June</option>
              <option value="07">July</option>
              <option value="08">August</option>
              <option value="09">September</option>
              <option value="10">October</option>
              <option value="11">November</option>
              <option value="12">December</option>
            </select>
          </th>
          <th>
            <select id="yearFilter-pending">
              <option value="">Year</option>
              <?php 
                $start_year = 2010;
                $end_year = date('Y');
                for( $j=$end_year; $j>=$start_year; $j-- ) {
                    echo '<option value="'.$j.'">'.$j.'</option>';
                }
              ?>
            </select>
          </th>
          <th><input type="date" id="dateFilter"></th>
        </tr>
      </table>
    </div>

<div>
  <table>
    <tr>
      <!-- <th class="th"><input type="checkbox"></th> -->
      <th class="th"></th>
      <th class="th">Full Name</th>
      <th class="th">Type of Leave</th>
      <th class="th">Date Filed</th>
      <th class="th">Date Requested</th>
      <th class="th">Leave Until</th>
      <th class="th">Days Covered</th>
      <th class="th Action" colspan="3">Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        $found = false;
        while($row = $result->fetch_assoc()) {
            if (($row["checked_by"] !== null && $row["checked_by"] != 0) && $row["status"] === "Pending") {
                $found = true;
                echo "<tr>";
                echo "<td class='td'></td>";
                echo "<td class='td'>" . $row["full_name"] . "</td>";
                echo "<td class='td'>" . $row["leave_type"] . "</td>";
                echo "<td class='td'>" . $row["date_filed"] . "</td>";
                echo "<td class='td'>" . $row["from_date"] . "</td>";
                echo "<td class='td'>" . $row["to_date"] . "</td>";
                echo "<td class='td days-covered'>" . $row["working_days_covered"] . "</td>";
                echo "<td class='td actions eye tooltip'><a href='view leave docs.php?application_id=" . $row["application_id"] . "' target='_blank'><i class='fa fa-eye'></i><span class='tooltiptext-eye'>View Leave Document</span></a></td>";
                
                echo "<td class='td actions tooltip'>";
                echo "<button class='btn-approved' name='btn-approved' onclick='openModal(\"approve\", " . $row['application_id'] . ")'>";
                echo "<i class='fa fa-check'></i>";
                echo "<span class='tooltiptext-approve'>Approve Leave</span>";
                echo "</button>";
                echo "</td>";

                echo "<td class='td actions tooltip'>";
                echo "<button class='btn-leaveHistory' onclick='openModal(\"decline\", " . $row['application_id'] . ")'>";
                echo "<i class='fa fa-close'></i>";
                echo "<span class='tooltiptext-reject'>Decline Leave</span>";
                echo "</button>";
                echo "</td>";
                echo "</tr>";
            }
        }
        if (!$found) {
            echo "<tr><td colspan='10'>No data found</td></tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No data found</td></tr>";
    }
    ?>
  </table>
</div>
</div>
</div>

<!-- Modal for Approving Leave -->
<div id="approveModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to <b>approve</b> this leave request?</p>
    <form id="approveForm" action='update_status.php' method='post'>
      <input type='hidden' name='application_id' id='approveApplicationId'>
      <input type='hidden' name='status' value='Approved'>
      <input type='hidden' name='email' value='<?php echo $_SESSION['user_email']; ?>'>
      <button type="submit" name="btn-approved" class="btn-approved">Yes</button>
      <button type="button" class="btn-cancel" onclick="closeModal('approve')">No</button>
    </form>
  </div>
</div>

<!-- Modal for Declining Leave -->
<div id="declineModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to <b>decline</b> this leave request?</p>
    <form id="declineForm" action='update_status.php' method='post'>
      <input type='hidden' name='application_id' id='declineApplicationId'>
      <input type='hidden' name='status' value='Declined'>
      <input type='hidden' name='email' value='<?php echo $_SESSION['user_email']; ?>'>
      <button type="submit" name="btn-declined" class="btn-declined">Yes</button>
      <button type="button" class="btn-cancel" onclick="closeModal('decline')">No</button>
    </form>
  </div>
</div>

<!-- Modal for Viewing Documents -->
<div id="viewDocModal" class="modal">
  <div class="modal-content">
    <iframe id="docFrame" src="" width="100%" height="500px"></iframe>
    <button type="button" class="btn-close" onclick="closeModal('viewDoc')">Close</button>
  </div>
</div>

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
  


  function openModal(action, id) {
    if (action === 'approve') {
        document.getElementById('approveApplicationId').value = id;
        document.getElementById('approveModal').style.display = 'block';
    } else if (action === 'decline') {
        document.getElementById('declineApplicationId').value = id;
        document.getElementById('declineModal').style.display = 'block';
    }
  }

  function closeModal(action) {
    if (action === 'approve') {
        document.getElementById('approveModal').style.display = 'none';
    } else if (action === 'decline') {
        document.getElementById('declineModal').style.display = 'none';
    } else if (action === 'viewDoc') {
        document.getElementById('viewDocModal').style.display = 'none';
    }
  }

  function toggleNav() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('overlay');
    if (sidebar.style.width === '250px') {
        sidebar.style.width = '0';
        overlay.style.display = 'none';
    } else {
        sidebar.style.width = '250px';
        overlay.style.display = 'block';
    }
  }

  function closeNav() {
    document.getElementById('sidebar').style.width = '0';
    document.getElementById('overlay').style.display = 'none';
  }

  // Adding event listeners for the filter inputs
  document.getElementById('nameFilter').addEventListener('input', filterTable);
  document.getElementById('monthFilter-pending').addEventListener('change', filterTable);
  document.getElementById('yearFilter-pending').addEventListener('change', filterTable);
  document.getElementById('dateFilter').addEventListener('input', filterTable);

  function filterTable() {
    var nameFilter = document.getElementById('nameFilter').value.toUpperCase();
    var monthFilter = document.getElementById('monthFilter-pending').value;
    var yearFilter = document.getElementById('yearFilter-pending').value;
    var dateFilter = document.getElementById('dateFilter').value;
    var table = document.querySelector('table');
    var tr = table.getElementsByTagName('tr');

    for (var i = 1; i < tr.length; i++) {
        var tdName = tr[i].getElementsByTagName('td')[1];
        var tdDateFiled = tr[i].getElementsByTagName('td')[3];
        var tdFromDate = tr[i].getElementsByTagName('td')[4];

        if (tdName) {
            var nameText = tdName.textContent || tdName.innerText;
            var dateFiledText = tdDateFiled.textContent || tdDateFiled.innerText;
            var fromDateText = tdFromDate.textContent || tdFromDate.innerText;

            var dateFiled = new Date(dateFiledText);
            var fromDate = new Date(fromDateText);

            var show = true;

            if (nameFilter && !nameText.toUpperCase().includes(nameFilter)) {
                show = false;
            }

            if (monthFilter && (dateFiled.getMonth() + 1) != monthFilter && (fromDate.getMonth() + 1) != monthFilter) {
                show = false;
            }

            if (yearFilter && dateFiled.getFullYear() != yearFilter && fromDate.getFullYear() != yearFilter) {
                show = false;
            }

            if (dateFilter && dateFiledText !== dateFilter && fromDateText !== dateFilter) {
                show = false;
            }

            tr[i].style.display = show ? "" : "none";
        }
    }
  }
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
