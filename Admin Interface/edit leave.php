<?php
session_start();
include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

// Check if application_id is provided
if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Fetch the leave application details
    $query = "SELECT * FROM leave_applications WHERE application_id = '$application_id'";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $application_data = mysqli_fetch_assoc($result);
    } else {
        die('Error: Application not found.');
    }
}

// Fetch leave types from the database
$leave_types_query = "SELECT DISTINCT leave_type FROM leave_applications";
$leave_types_result = mysqli_query($connection, $leave_types_query);
if (!$leave_types_result) {
    die('Error: ' . mysqli_error($connection));
}
$leave_types = [];
while ($row = mysqli_fetch_assoc($leave_types_result)) {
    $leave_types[] = $row['leave_type'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $date_filed = $_POST['date_filed'];
    $leave_from = $_POST['from-date'];
    $leave_to = $_POST['to-date'];
    $leave_type = $_POST['leave-type'];
    $leave_type_others = $_POST['others'];
    $working_days_covered = $_POST['numofDays'];
    $reason = $_POST['reason'];

    // Get leave balance data
    $vl_wpay_bal = $_POST['vl_wpay_bal'];
    $vl_wopay_bal = $_POST['vl_wopay_bal'];
    $sl_wpay_bal = $_POST['sl_wpay_bal'];
    $sl_wopay_bal = $_POST['sl_wopay_bal'];
    $vl_total_bal = $_POST['vl_total_bal'];
    $sl_total_bal = $_POST['sl_total_bal'];

    // Get user information from the database
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT firstname, lastname, contactnumber, department FROM users WHERE user_id = '$user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (!$user_result) {
        die('Error: ' . mysqli_error($connection));
    }
    $user_data = mysqli_fetch_assoc($user_result);

    // Update leave application data in the database
    $update_query = "UPDATE leave_applications 
                     SET date_filed = '$date_filed', leave_type = '$leave_type', from_date = '$leave_from', 
                         to_date = '$leave_to', working_days_covered = '$working_days_covered', reason = '$reason',
                         vl_wpay_bal = '$vl_wpay_bal', vl_wopay_bal = '$vl_wopay_bal', sl_wpay_bal = '$sl_wpay_bal',
                         sl_wopay_bal = '$sl_wopay_bal', vl_total_bal = '$vl_total_bal', sl_total_bal = '$sl_total_bal'
                     WHERE application_id = '$application_id'";

    $update_result = mysqli_query($connection, $update_query);
    if (!$update_result) {
        die('Error: ' . mysqli_error($connection));
    }

    // Generate PDF (same as before)

    // Include the FPDF library
    require("/xampp/htdocs/mapecon/fpdf/fpdf.php");

    // Create a new FPDF object
    $pdf = new FPDF();

    // Add a page
    $pdf->AddPage();

    // Set the path to your background image
    $background = "/xampp/htdocs/mapecon/Pictures/leave form.png";

    // Check if the image file exists before attempting to use it
    if (file_exists($background)) {
        // Place the image as a background
        $pdf->Image($background, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    } else {
        die('Background image not found: ' . $background);
    }

    // Set font
    $pdf->SetFont('Arial', 'B', 16);

    // Set position for the content
    $pdf->SetXY(10, 10);

    // Output the title
    $pdf->Cell(0, 10, '', 0, 1, 'C');

    // Set font for the content
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0, 0, 139);

    // Output form
    $pdf->Cell(0, 15, '', 0, 1);
    $pdf->Cell(35, 10, '', 0, 0);
    $pdf->Cell(91, 15, $date_filed, 0, 0);
    $pdf->Cell(0, 15, $user_data['department'], 0, 1);
    $pdf->Cell(20, 10, '', 0, 0);
    $pdf->Cell(0, 1, $user_data['firstname'] . ' ' . $user_data['lastname'], 0, 1);
    $pdf->Cell(96, 10, '', 0, 0);
    $pdf->Cell(0, 15, $user_data['contactnumber'], 0, 1);
    $pdf->Cell(21, 10, '', 0, 0);
    $pdf->Cell(81, 16, $leave_from, 0, 0);
    $pdf->Cell(0, 16, $leave_to, 0, 1);
    $pdf->Cell(102, 10, '', 0, 0);
    $pdf->Cell(0, 1, $working_days_covered, 0, 1);
    $pdf->Cell(46, 10, '', 0, 0);
    if ($leave_type == "Others") {
        $pdf->Cell(0, 14, "Others ", 0, 1);
        $pdf->Cell(57, 0, '', 0, 0);
        $pdf->Cell(0, 1, $leave_type_others, 0, 1);
    } else {
        $pdf->Cell(0, 14, $leave_type, 0, 1);
    }
    $pdf->Cell(3, 10, '', 0, 0);
    $pdf->Cell(0, 42, $reason, 0, 1);

    // Output the PDF (uncomment one option)
    // $pdf->Output('leave_form.pdf', 'D'); // Download
    $pdf->Output('filename.pdf', 'S'); // Display in browser for preview
    header("Location: User Leave History.php");
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Leave Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
    <link rel="stylesheet" href="/mapecon/style.css">
</head>
<body>
<header>
    <div class="logo_header">
        <a href="../Admin Interface/Admin Home.php"> 
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

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span> Leave Management System<div id="date-time"></div></div>

<!-- Content -->
<div class="content" id="content">

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="Admin Home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
        <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
        <span class="leave-label">LEAVE REPORTS</span>
        <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
        <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
        <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeNav()"></div>
    <div class="leave-application">
        <h2>Edit Leave Application</h2>
        <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm()">
            <label for="leave-type">Leave Type:</label>
            <div class="leave-type">
                <select name="leave-type" id="leave-type">
                    <option value="">Select</option>
                    <?php foreach ($leave_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($application_data['leave_type'] == $type) ? 'selected' : ''; ?>>
                            <?php echo $type; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="others-container" style="display: <?php echo ($application_data['leave_type'] == 'Others') ? 'block' : 'none'; ?>;">
                <label for="others">Others:</label>
                <input type="text" id="others" name="others" value="<?php echo $application_data['leave_type_others']; ?>">
            </div>
            <div class="date-range">
                <div class="from-date">
                    <label for="from-date">From Date:</label>
                    <input type="date" name="from-date" id="from-date" value="<?php echo $application_data['from_date']; ?>">
                </div>
                <div class="to-date">
                    <label for="to-date">To Date:</label>
                    <input type="date" name="to-date" id="to-date" value="<?php echo $application_data['to_date']; ?>">
                </div>
                <div class="num-of-days">
                    <label for="numofDays">Days covered:</label>
                    <input type="number" id="numofDays" name="numofDays" value="<?php echo $application_data['working_days_covered']; ?>">
                </div>
            </div>
            <label for="reason" class="reason-label">Reason:</label>
            <div class="reason">
                <textarea name="reason" id="reason" cols="30" rows="10"><?php echo $application_data['reason']; ?></textarea>
            </div>
        
            <div class="leave-balances">
  <table>
    <tr>
      <th>Leave Type</th>
      <th>With Pay</th>
      <th>Without Pay</th>
    </tr>
    <tr>
      <td>Vacation Leave</td>
      <td><input type="number" id="vl_wpay_bal" name="vl_wpay_bal" value="<?php echo $application_data['vl_wpay_bal'];?>"></td>
      <td><input type="number" id="vl_wopay_bal" name="vl_wopay_bal" value="<?php echo $application_data['vl_wopay_bal'];?>"></td>
    </tr>
    <tr>
      <td>Sick Leave</td>
      <td><input type="number" id="sl_wpay_bal" name="sl_wpay_bal" value="<?php echo $application_data['sl_wpay_bal'];?>"></td>
      <td><input type="number" id="sl_wopay_bal" name="sl_wopay_bal" value="<?php echo $application_data['sl_wopay_bal'];?>"></td>
    </tr>
    <tr>
      <td>Total</td>
      <td><input type="number" id="vl_total_bal" name="vl_total_bal" value="<?php echo $application_data['vl_total_bal'];?>"></td>
      <td><input type="number" id="sl_total_bal" name="sl_total_bal" value="<?php echo $application_data['sl_total_bal'];?>"></td>
    </tr>
  </table>
</div>
            <div class="buttons">
                <button type="button" onclick="window.location.href='/mapecon/Admin Interface/Admin Home.php';">Cancel</button>
                <button type="submit" id="submit-btn">Update Application</button>
            </div>
            <input type="hidden" id="date_filed" name="date_filed" value="<?php echo $application_data['date_filed']; ?>">
        </form>
    </div>
</div>
</body>

<script>
    function updateTime() {
        var today = new Date();
        var time = today.toLocaleTimeString();
        var options = { month: 'long', day: 'numeric', year: 'numeric' };
        var date = today.toLocaleDateString("en-US", options); // May 12, 2024
        document.getElementById("date-time").innerHTML = "Today is " + date + " | " + time;
        setTimeout(updateTime, 1000); // Update time every second
    }

    updateTime();

    document.getElementById('leave-type').addEventListener('change', function() {
        var othersContainer = document.getElementById('others-container');
        if (this.value === 'Others') {
            othersContainer.style.display = 'block';
        } else {
            othersContainer.style.display = 'none';
        }
    });

    function validateForm() {
        var leaveType = document.getElementById('leave-type').value;
        var fromDate = document.getElementById('from-date').value;
        var toDate = document.getElementById('to-date').value;
        var numOfDays = document.getElementById('numofDays').value;

        if (leaveType === "" || fromDate === "" || toDate === "" || numOfDays === "") {
            alert("Please fill in all fields.");
            return false;
        }
        return true;
    }

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
