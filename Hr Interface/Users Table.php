<?php
session_start();

include("../sql/config.php");
// Connect to database
$conn = $connection;

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if cancel request button is clicked
if (isset($_POST['id_to_delete'])) {
  $id_to_delete = $_POST['id_to_delete'];

  // Prepare the SQL statement to prevent SQL injection
  $stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
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

// Fetch users grouped by department and user status
$sql = "SELECT * FROM users ORDER BY user_status OR department DESC, id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Users</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
<link rel="stylesheet" href="/mapecon/style4.css">
<style>
  td.days-covered {
    text-align: center; /* Center align text in Days Covered column */
}
    th.Action{
      text-align:center;
    }
    td.department{
      width: 18%; /* Makes the cell take up the full width */
    text-align: center; /* Centers the text horizontally */
    white-space: nowrap; /* Prevents wrapping for exact fitting */
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

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span> HR(Human Resources Management) <div id="date-time"></div></div>

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
    <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    <a href="Add users.php"><i class="fa fa-user-o"></i> Add Users</a>
    <a href="Users Table.php"  id="active"><i class="fa fa-user-o"></i> Edit Users</a>
  </div>


  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  
    <div class="leave-report-header">
      <h2>Edit Users</h2>
    </div>
    
    <div class="filters">
      <table>
        <tr class="filter-row-name">
          <th><input type="text" placeholder="Name" id="nameFilter"></th>
        </tr>
      </table>
    </div>

<div>
  <table>
    <tr>
      <!-- <th class="th"><input type="checkbox"></th> -->
      <th class="th"></th>
      <th class="th">User Type</th>
      <th class="th">User ID</th>
      <th class="th">First Name</th>
      <th class="th">Last Name</th>
      <th class="th">Contact Number</th>
      <th class="th">Email</th>
      <th class="th">Department</th>
      <th class="th">Supervisor ID</th>
      <th class="th Action" colspan="3">Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo "<tr>";
                echo "<td class='td'></td>";
                echo "<td class='td'>" . $row["user_status"] . "</td>";
                echo "<td class='td'>" . $row["user_id"] . "</td>";
                echo "<td class='td'>" . strtoupper($row["firstname"]) . "</td>";
                echo "<td class='td'>" . strtoupper($row["lastname"]) . "</td>";
                echo "<td class='td'>" . $row["contactnumber"] . "</td>";
                echo "<td class='td'>" . $row["email"] . "</td>";
                echo "<td class='td department'>" . $row["department"] . "</td>";
                echo "<td class='td'>" . $row["approver_id"] . "</td>";
                echo "<td class='td actions edit tooltip'><a href='edit user.php?user_id=" . $row["user_id"] . "'><i class='fa fa-pencil'></i><span class='tooltiptext-edit'>Edit</span></a></td>";
                echo "<td class='td actions cancel-history tooltip td-history'>";
                echo "<button class='btn-leaveHistory' onclick='openCancelModal(" . $row['id'] . ")'>
                      <i class='fa fa-trash'></i><span class='tooltiptext-reject'>Delete User</span> 
                      </button>";
                echo "</td>";
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
        <p>Are you sure you want to delete this user?</p>
        <form method='post'>
            <input type='hidden' name='id_to_delete' id='cancelIdToDelete'>
            <button type='submit' class='btn-leaveHistory' name='cancel_request'>Yes</button>
            <button type='button' class='btn-grey' onclick="closeModal('cancelModal')">No</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
function openModal(action, applicationId) {
  if (action === 'approve') {
    document.getElementById('approveApplicationId').value = applicationId;
    document.getElementById('approveModal').style.display = 'block';
  } else if (action === 'decline') {
    document.getElementById('declineApplicationId').value = applicationId;
    document.getElementById('declineModal').style.display = 'block';
  }
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

function openCancelModal(idToDelete) {
    document.getElementById('cancelIdToDelete').value = idToDelete;
    document.getElementById('cancelModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
  if (event.target == document.getElementById('approveModal')) {
    closeModal('approveModal');
  } else if (event.target == document.getElementById('declineModal')) {
    closeModal('declineModal');
  }
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

function confirmApproval() {
  return confirm("Are you sure you want to approve this leave application?");
}

function confirmDecline() {
  return confirm("Are you sure you want to decline this leave application?");
}
  

  // Filter table rows based on name
  document.getElementById('nameFilter').addEventListener('input', function() {
        var input = this.value.toUpperCase();
        var rows = document.querySelectorAll('table tr');
        for (var i = 1; i < rows.length; i++) {
            var firstNameCell = rows[i].getElementsByTagName("td")[2]; // Assuming first name is in the second column
            var lastNameCell = rows[i].getElementsByTagName("td")[3];  // Assuming last name is in the third column
            if (firstNameCell && lastNameCell) {
                var firstName = firstNameCell.textContent || firstNameCell.innerText;
                var lastName = lastNameCell.textContent || lastNameCell.innerText;
                if (firstName.toUpperCase().indexOf(input) > -1 || lastName.toUpperCase().indexOf(input) > -1) {
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
</script>
</body>
</html>