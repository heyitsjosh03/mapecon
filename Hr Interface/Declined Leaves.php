<?php
session_start();

include("../sql/config.php");
// Connect to database
$conn = $connection;

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT l.*, UCASE(CONCAT(u.lastname, ', ', u.firstname)) AS full_name
        FROM leave_applications AS l 
        INNER JOIN users AS u ON l.user_id = u.user_id
        ORDER BY l.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Declined Leaves</title>
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

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span>  HR(Human Resources Management) <div id="date-time"></div></div>

 <!-- Content -->
 <div class="content" id="content">
<div class="container_report_report">
  
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="Hr Home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
    <span class="leave-label">LEAVE REPORTS</span>
    <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
    <a href="Approval Leaves.php"><i class="fa fa-file-text-o"></i>Request for Approval</a>
    <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
    <a href="Declined Leaves.php" id="active"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Add users.php"><i class="fa fa-user-o"></i> Add Users</a>
    <a href="Users Table.php"><i class="fa fa-user-o"></i> Edit Users</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  
    <div class="leave-report-header">
      <h2>Declined Leaves</h2>
    </div>
    
    <div class="filters">
      <table>
        <tr class="filter-row">
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
      <th class="th" colspan="2">Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if($row["status"] === "Declined") {
                echo "<tr>";
                echo "<td class='td'></td>";
                echo "<td class='td-declined'>" . $row["full_name"] . "</td>";
                echo "<td class='td'>" . $row["leave_type"] . "</td>";
                echo "<td class='td'>" . $row["date_filed"] . "</td>";
                echo "<td class='td'>" . $row["from_date"] . "</td>";
                echo "<td class='td'>" . $row["to_date"] . "</td>";
                echo "<td class='td days-covered'>" . $row["working_days_covered"] . "</td>";
                echo "<td class='td actions eye tooltip'><a href='view leave docs declined.php?application_id=" . $row["application_id"] . "' target='_blank'><i class='fa fa-eye'></i><span class='tooltiptext-eye'>View Leave Document</span></a></td>";
                echo "</tr>";
            }
        }
    } else {
        echo "<tr><td colspan='10'>No data found</td></tr>";
    }
    ?>
  </table>
</div>
</div>
</div>

<button onclick="scrollToTop()" id="scrollToTopBtn" title="Go to top"><i class="fa fa-arrow-up"></i></button>
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
  

  // Filter table rows based on name
  document.getElementById('nameFilter').addEventListener('input', function() {
    var input = this.value.toUpperCase();
    var rows = document.querySelectorAll('table tr');
    for (var i = 1; i < rows.length; i++) {
        var name = rows[i].getElementsByTagName("td")[1];
        if (name) {
            var textValue = name.textContent || name.innerText;
            if (textValue.toUpperCase().indexOf(input) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
    });

    // Filter table rows based on date filed
    document.getElementById('dateFilter').addEventListener('input', function() {
    var inputDate = this.value;
    var rows = document.querySelectorAll('table tr');
    for (var i = 1; i < rows.length; i++) {
        var dateFiled = rows[i].getElementsByTagName("td")[3];
        if (dateFiled) {
            var textValue = dateFiled.textContent || dateFiled.innerText;
            if (textValue === inputDate) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
      }
    });

    // Filter table rows based on month and year
    document.getElementById('monthFilter-pending').addEventListener('change', function() {
        var inputMonth = this.value;
        var inputYear = document.getElementById('yearFilter-pending').value;
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var dateFiled = rows[i].getElementsByTagName("td")[3];
            if (dateFiled) {
                var textValue = dateFiled.textContent || dateFiled.innerText;
                var month = textValue.split("-")[1];
                var year = textValue.split("-")[0];
                if ((inputMonth === "" || month === inputMonth) && (inputYear === "" || year === inputYear)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });

    // Filter table rows based on year
    document.getElementById('yearFilter-pending').addEventListener('change', function() {
        var inputYear = this.value;
        var inputMonth = document.getElementById('monthFilter-pending').value;
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var dateFiled = rows[i].getElementsByTagName("td")[3];
            if (dateFiled) {
                var textValue = dateFiled.textContent || dateFiled.innerText;
                var month = textValue.split("-")[1];
                var year = textValue.split("-")[0];
                if ((inputMonth === "" || month === inputMonth) && (inputYear === "" || year === inputYear)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });

    // Reset table rows when date filter is cleared
    document.getElementById('dateFilter').addEventListener('change', function() {
        if (this.value === "") {
            var rows = document.querySelectorAll('table tr');
            for (var i = 1; i < rows.length; i++) {
                rows[i].style.display = "";
            }
        } else {
            // Clear month and year filters
            document.getElementById('monthFilter-pending').value = "";
            document.getElementById('yearFilter-pending').value = "";
        }
    });

    // Clear date filter when month or year filter is utilized
    document.getElementById('monthFilter-pending').addEventListener('change', function() {
        var inputMonth = this.value;
        var inputYear = document.getElementById('yearFilter-pending').value;
        if (inputMonth !== "" || inputYear !== "") {
            document.getElementById('dateFilter').value = "";
        }
    });

    document.getElementById('yearFilter-pending').addEventListener('change', function() {
        var inputYear = this.value;
        var inputMonth = document.getElementById('monthFilter-pending').value;
        if (inputMonth !== "" || inputYear !== "") {
            document.getElementById('dateFilter').value = "";
        }
    });
    // Show scroll-to-top button when user scrolls down 20px from the top of the document
window.onscroll = function() { scrollFunction() };

function scrollFunction() {
  var scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    scrollToTopBtn.style.display = "block";
  } else {
    scrollToTopBtn.style.display = "none";
  }
}

// Scroll to the top of the document when the button is clicked
function scrollToTop() {
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}

</script>
<script src="script.js"></script>
</body>
</html>
