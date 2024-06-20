<?php
session_start();
include("../sql/config.php");
include("../sql/function.php");

$user_data = check_login($connection);

if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    $query = "SELECT * FROM leave_applications WHERE application_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $application_data = $result->fetch_assoc();
        
        $user_id = $application_data['user_id'];
        $user_query = "SELECT firstname, lastname, contactnumber, department FROM users WHERE user_id = ?";
        $user_stmt = $connection->prepare($user_query);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if (!$user_result) {
            die('Error: ' . $connection->error);
        }
        $user_data = $user_result->fetch_assoc();

        $approver_query = "SELECT approver_id FROM users WHERE user_id = ?";
        $approver_stmt = $connection->prepare($approver_query);
        $approver_stmt->bind_param("i", $user_id);
        $approver_stmt->execute();
        $approver_result = $approver_stmt->get_result();
        if ($approver_result->num_rows == 0) {
            echo "<script>alert('Approver Required!!');</script>";
        }
   } else {
       die('Error: Application not found.');
   }
}

$leave_types_query = "SELECT DISTINCT leave_type FROM leave_applications";
$leave_types_result = $connection->query($leave_types_query);
if (!$leave_types_result) {
    die('Error: ' . $connection->error);
}
$leave_types = [];
while ($row = $leave_types_result->fetch_assoc()) {
    $leave_types[] = $row['leave_type'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_filed = $_POST['date_filed'];
    $leave_from = $_POST['from-date'];
    $leave_to = $_POST['to-date'];
    $leave_type = $_POST['leave-type'];
    $leave_type_others = $_POST['others'];
    $working_days_covered = $_POST['numofDays'];
    $reason = $_POST['reason'];

    $vl_wpay_bal = $_POST['vl_wpay_bal'];
    $vl_wopay_bal = $_POST['vl_wopay_bal'];
    $sl_wpay_bal = $_POST['sl_wpay_bal'];
    $sl_wopay_bal = $_POST['sl_wopay_bal'];
    $vl_total_bal = $_POST['vl_total_bal'];
    $sl_total_bal = $_POST['sl_total_bal'];

    $checked_by = $_POST['checked_by'];

    $update_query = "UPDATE leave_applications 
                     SET date_filed = ?, leave_type = ?, from_date = ?, 
                         to_date = ?, working_days_covered = ?, reason = ?,
                         vl_wpay_bal = ?, vl_wopay_bal = ?, sl_wpay_bal = ?, sl_wopay_bal = ?, vl_total_bal = ?, sl_total_bal = ?, checked_by = ?
                     WHERE application_id = ?";
    $update_stmt = $connection->prepare($update_query);
    $update_stmt->bind_param("ssssssiiiiissi", $date_filed, $leave_type, $leave_from, $leave_to, $working_days_covered, $reason, $vl_wpay_bal, $vl_wopay_bal, $sl_wpay_bal, $sl_wopay_bal, $vl_total_bal, $sl_total_bal, $checked_by, $application_id);
    $update_result = $update_stmt->execute();
    if (!$update_result) {
        die('Error: ' . $connection->error);
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

    // Redirect to Pending Leaves after updating
    header("Location: Pending Leaves.php");
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Leave Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="shortcut icon" href="/mapecon/Pictures/favicon.png">
    <link rel="stylesheet" href="/mapecon/style.css">
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
                <a href="../Hr Interface/Hr Profile.php">Profile </a>
                <a href="../Hr Interface/Hr Change Password.php">Change Password</a>
                <a href="../sql/logout.php">Logout</a>
            </div>
        </label>
    </div>
</header>

<div class="menu"><span class="openbtn" onclick="toggleNav()">&#9776;</span> HR(Human Resources Management)<div id="date-time"></div></div>

<!-- Content -->
<div class="content" id="content">

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="Hr Home.php" class="home-sidebar" id="active"><i class="fa fa-home"></i> Home</a>
        <!-- <a href="Admin Dashboard.php" class="home-sidebar"><i class="fa fa-pie-chart"></i> Dashboard</a> -->
        <span class="leave-label">LEAVE REPORTS</span>
        <a href="Pending Leaves.php"><i class="fa fa-file-text-o"></i> Pending Leaves</a>
        <a href="Approved Leaves.php"><i class="fa fa-file-word-o"></i> Approved Leaves</a>
        <a href="Declined Leaves.php"><i class="fa fa-file-excel-o"></i> Declined Leaves</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeNav()"></div>
    <div class="leave-application">
        <h2 class="edit-text">Edit Leave Application</h2>
        <form action="<?php echo($_SERVER["PHP_SELF"]); ?>?application_id=<?php echo $application_id; ?>" method="post" onsubmit="return validateForm()">
        
            <label for="date_filed" class="edit-date">Date Filed:</label>
            <input type="date" id="date_filed" class="edit-filed" name="date_filed" value="<?php echo $application_data['date_filed']; ?>" readonly>
            <label for="department" class="department">Department:</label>
            <div class="edit-department">
            <select name="department" id="department" required>
            <option value="<?php echo $user_data['department']; ?>"><?php echo $user_data['department']; ?></option>
            <option value="Accounting" <?php echo (isset($row['department']) && $row['department'] == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
            <option value="Admin and Shared Services" <?php echo (isset($row['department']) && $row['department'] == 'Admin and Shared Services') ? 'selected' : ''; ?>>Admin and Shared Services</option>
            <option value="Ads and Promo" <?php echo (isset($row['department']) && $row['department'] == 'Ads and Promo') ? 'selected' : ''; ?>>Ads and Promo</option>
            <option value="Business Development Group" <?php echo (isset($row['department']) && $row['department'] == 'Business Development Group') ? 'selected' : ''; ?>>Business Development Group</option>
            <option value="Chem Room" <?php echo (isset($row['department']) && $row['department'] == 'Chem Room') ? 'selected' : ''; ?>>Chem Room</option>
            <option value="Clinic" <?php echo (isset($row['department']) && $row['department'] == 'Clinic') ? 'selected' : ''; ?>>Clinic</option>
            <option value="Collection" <?php echo (isset($row['department']) && $row['department'] == 'Collection') ? 'selected' : ''; ?>>Collection</option>
            <option value="EVP Office" <?php echo (isset($row['department']) && $row['department'] == 'EVP Office') ? 'selected' : ''; ?>>EVP Office</option>
            <option value="Greenovations-Floor" <?php echo (isset($row['department']) && $row['department'] == 'Greenovations-Floor') ? 'selected' : ''; ?>>Greenovations (1st and 2nd Floor)</option>
            <option value="Greenovations-Table" <?php echo (isset($row['department']) && $row['department'] == 'Greenovations-Table') ? 'selected' : ''; ?>>Greenovations (MGCPI Table)</option>
            <option value="Operator and HR" <?php echo (isset($row['department']) && $row['department'] == 'Operator and HR') ? 'selected' : ''; ?>>Operator and HR</option>
            <option value="OTD" <?php echo (isset($row['department']) && $row['department'] == 'OTD') ? 'selected' : ''; ?>>OTD</option>
            <option value="Research" <?php echo (isset($row['department']) && $row['department'] == 'Research') ? 'selected' : ''; ?>>Research and Development</option>
            <option value="Sales" <?php echo (isset($row['department']) && $row['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
            <option value="Service" <?php echo (isset($row['department']) && $row['department'] == 'Service') ? 'selected' : ''; ?>>Service</option>
            </select>
            </div>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $user_data['firstname'] . ' ' . $user_data['lastname']; ?>">
            
            <label for="contactnumber">Contact Number:</label>
            <input type="text" id="contactnumber" name="contactnumber" value="<?php echo $user_data['contactnumber']; ?>">
            
            <label for="leave-type">Leave Type:</label>
            <div class="leave-type">
                <select name="leave-type" id="leave-type">
                    <option value="">Select</option>
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Vacation Leave">Vacation Leave</option>
                </select>
            </div>
            <div class="others" id="others-container" style="display: none;">
                <label for="others">Others:</label>
                <input type="others" id="others" name="others">
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
                        <th>Number of Leave Days Available</th>
                        <th>With Pay</th>
                        <th>Without Pay</th>
                        <th>Balance as of:</th>
                    </tr>
                    <tr>
                        <td>Vacation Leave</td>
                        <td><input type="number" id="vl_wpay_bal" name="vl_wpay_bal" value="<?php echo $application_data['vl_wpay_bal']; ?>"></td>
                        <td><input type="number" id="vl_wopay_bal" name="vl_wopay_bal" value="<?php echo $application_data['vl_wopay_bal']; ?>"></td>
                        <td><input type="number" id="vl_total_bal" name="vl_total_bal" value="<?php echo $application_data['vl_total_bal']; ?>"></td>
                    </tr>
                    <tr>
                        <td>Sick Leave</td>
                        <td><input type="number" id="sl_wpay_bal" name="sl_wpay_bal" value="<?php echo $application_data['sl_wpay_bal']; ?>"></td>
                        <td><input type="number" id="sl_wopay_bal" name="sl_wopay_bal" value="<?php echo $application_data['sl_wopay_bal']; ?>"></td>
                        <td><input type="number" id="sl_total_bal" name="sl_total_bal" value="<?php echo $application_data['sl_total_bal']; ?>"></td>
                    </tr>
                </table>
            </div>

            <label for="checked_by">Checked by:</label>
            <input type="text" id="checked_by" name="checked_by" value="<?php echo $application_data['checked_by']; ?>" required>

            <div class="buttons">
                <button type="button" onclick="window.location.href='/mapecon/Hr Interface/Pending Leaves.php';">Cancel</button>
                <button type="submit" id="submit-btn" name="approver-btn" action="update_approver">Send to Supervisor</button>
            </div>
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
