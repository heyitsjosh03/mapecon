<?php
session_start();

include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

// Check if cancel request button is clicked
if (isset($_POST['id_to_delete'])) {
    $id_to_delete = $_POST['id_to_delete'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $connection->prepare("DELETE FROM leave_applications WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);

    if ($stmt->execute()) {
        // Send success response
        header("Location: " . $_SERVER['REQUEST_URI']);
    } else {
        // Send error response
        echo "Error deleting record: " . $connection->error;
    }

    $stmt->close();
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch leave history data specific to the logged-in user
$sql = "SELECT l.*, UCASE(CONCAT(u.lastname, ', ', u.firstname)) AS full_name
        FROM leave_applications AS l 
        INNER JOIN users AS u ON l.user_id = u.user_id
        WHERE l.user_id = ?
        ORDER BY l.id DESC";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Leave History</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
<link rel="stylesheet" href="/mapecon/style3.css">
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

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span>  EMP<div id="date-time"></div></div>

 <!-- Content -->
<div class="content" id="content">
<div class="container_report_report">
  
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a href="/mapecon/User Interface/User Leave Home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    <span class="leave-label">NAVIGATE</span>
    <a href="/mapecon/User Interface/User Leave Form.php"><i class="fa fa-file-text-o"></i>Leave Application</a>
    <a href="/mapecon/User Interface/User Leave History.php" id="active"><i class="fa fa-file-word-o"></i> Leave History</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  
    <div class="leave-report-header">
      <h2>Leave History</h2>
      <!-- <div class="dropdown">
        <button class="dropdown-button" onclick="showDropdown()">Export   <i class="fa fa-caret-down"></i></button>
        <ul class="dropdown-menu">
          <li><a href="#">Compiled PDF</a></li>
          <li><a href="#">Excel Format</a></li>
        </ul>
      </div> -->
    </div>
    
    <div class="filters">
      <table>
        <tr class="filter-row-approved">
        <th>
            <select id="monthFilter">
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
            <select id="yearFilter">
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
        </tr>
      </table>
    </div>

<div>
  <table>
    <tr>
      <!-- <th class="th-history"><input type="checkbox"></th> -->
      <th class="th-history"></th>
      <th class="th-history">Type of Leave</th>
      <th class="th-history">Date Filed</th>
      <th class="th-history">Date Requested</th>
      <th class="th-history">Leave Until</th>
      <th class="th-history">Status</th>
      <th class="th-history"></th>
      <th class="th-history" colspan="3">Actions</th>
    </tr>
    <?php 
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            //echo "<td class='td-history'><input type='checkbox'></td>";
            echo "<td class='td-history'></td>";
            echo "<td class='td-history'>" . $row["leave_type"] . "</td>";
            echo "<td class='td-history'>" . $row["date_filed"] . "</td>";
            echo "<td class='td-history'>" . $row["from_date"] . "</td>";
            echo "<td class='td-history'>" . $row["to_date"] . "</td>";
            echo "<td class='td-history'>";
            switch ($row["status"]) {
                case 'Pending':
                    echo "<span class='pending-leave'>Pending</span>";
                    break;
                case 'Approved':
                    echo "<span class='approved-leave'>Approved</span>";
                    break;
                case 'Declined':
                    echo "<span class='rejected-leave'>Declined</span>";
                    break;
                default:
                    echo "-";
            }
            echo "</td>";
            echo "<td class='td-history'> -</td>";
            echo "<td class='actions eye tooltip td-history'><a href='view leave docs.php?application_id=" . $row["application_id"] . "' target='_blank'><i class='fa fa-eye'></i><span class='tooltiptext-eye'>View Leave Document</span></a></td>";
            echo "<td class='td actions floppy tooltip td-history'><a href='download leave docs.php?application_id=" . $row["application_id"] . "' target='_blank'><i class='fa fa-floppy-o'></i><span class='tooltiptext-approve'>Save as PDF</span></a></td>";
            // Check if the status is "Pending"
            if ($row["status"] == "Pending") {
              echo "<td class='td actions cancel-history tooltip td-history'>";
              echo "<button class='btn-leaveHistory' onclick='openCancelModal(" . $row['id'] . ")'>
                    <i class='fa fa-close'></i><span class='tooltiptext-reject'>Cancel Request</span> 
                    </button>";
              echo "</td>";
          } else {
              echo "<td class='td actions cancel-history-disabled tooltip td-history'>";
              echo "<button class='btn-leaveHistory-disabled' disabled>
                    <i class='fa fa-ban'></i><span class='tooltiptext-disabled'>Cannot Cancel</span>
                    </button>";
              echo "</td>";
          }
  
          echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No data found</td></tr>";
    }
    ?>
  </table>
</div>
</div>
</div>

<!-- Modals -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to cancel this leave request?</p>
        <form method='post'>
            <input type='hidden' name='id_to_delete' id='cancelIdToDelete'>
            <button type='submit' class='btn-leaveHistory' name='cancel_request'>Yes</button>
            <button type='button' class='btn-grey' onclick="closeModal('cancelModal')">No</button>
        </form>
    </div>
</div>

<!-- Scripts -->

<script>
function openCancelModal(idToDelete) {
    document.getElementById('cancelIdToDelete').value = idToDelete;
    document.getElementById('cancelModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
</script>

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
      content.style.marginLeft = "0";
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
  
   // Filter table rows based on date filed
   document.getElementById('dateFilter').addEventListener('input', function() {
        var inputDate = this.value;
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var dateFiled = rows[i].getElementsByTagName("td")[2]; // Changed index to 1 for "Date Filed" column
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
    document.getElementById('monthFilter').addEventListener('change', function() {
        var inputMonth = this.value;
        var inputYear = document.getElementById('yearFilter').value;
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var dateFiled = rows[i].getElementsByTagName("td")[2]; // Changed index to 1 for "Date Filed" column
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
    document.getElementById('yearFilter').addEventListener('change', function() {
        var inputYear = this.value;
        var inputMonth = document.getElementById('monthFilter').value;
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var dateFiled = rows[i].getElementsByTagName("td")[2]; // Changed index to 1 for "Date Filed" column
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
            document.getElementById('monthFilter').value = "";
            document.getElementById('yearFilter').value = "";
        }
    });

    // Clear date filter when month or year filter is utilized
    document.getElementById('monthFilter').addEventListener('change', function() {
        var inputMonth = this.value;
        var inputYear = document.getElementById('yearFilter').value;
        if (inputMonth !== "" || inputYear !== "") {
            document.getElementById('dateFilter').value = "";
        }
    });

    document.getElementById('yearFilter').addEventListener('change', function() {
        var inputYear = this.value;
        var inputMonth = document.getElementById('monthFilter').value;
        if (inputMonth !== "" || inputYear !== "") {
            document.getElementById('dateFilter').value = "";
        }
    });

  
  
</script>  
</body>
</html>
<?php
// Close database connection
$connection->close();
?>
