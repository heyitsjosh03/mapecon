<?php
session_start();
include("../sql/config.php");
include("../sql/function.php");
$user_data = check_login($connection);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $date_filed = $_POST['date_filed'];
        $leave_from = $_POST['from-date'];
        $leave_to = $_POST['to-date'];
        $leave_type = $_POST['leave-type'];
        $leave_type_others= $_POST['others'];
        $working_days_covered= $_POST['numofDays'];
        $reason = $_POST['reason'];


        // Get user information from the database
        $user_id = $_SESSION['user_id'];
        $user_query = "SELECT firstname, lastname, contactnumber, department FROM users WHERE user_id = '$user_id'";
        $user_result = mysqli_query($connection, $user_query);
        if (!$user_result) {
            die('Error: ' . mysqli_error($connection));
        }
        $user_data = mysqli_fetch_assoc($user_result);
        

        // Insert leave application data into the database
        //$user_id = $_SESSION['user_id'];
        // Function to generate a random 5-digit number
        function generate5DigitNumber() {
            return str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            }
        // Function to check if the generated number exists in the database
        function numberExistsInDatabase($number, $connection) {
            $query = "SELECT COUNT(*) AS count FROM leave_applications WHERE application_id = ?";
            $statement = $connection->prepare($query);
            $statement->bind_param("s", $number);
            $statement->execute();
            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
          }
          $application_id = generate5DigitNumber();
          // Check if the generated number exists in the database
          while (numberExistsInDatabase($application_id, $connection)) {
              $application_id = generate5DigitNumber();
          }

          if ($_POST['leave-type'] == "Others"){
            $leave_type_report = "Others: " . $_POST['others'];
            } else {
            $leave_type_report = $_POST['leave-type'];
            }
        $query = "INSERT INTO leave_applications (application_id, user_id, date_filed, leave_type, from_date, to_date, working_days_covered, reason) VALUES ('$application_id', '$user_id', '$date_filed', '$leave_type_report', '$leave_from', '$leave_to', '$working_days_covered', '$reason')";
        $result = mysqli_query($connection, $query);
            if (!$result) {
                die('Error: ' . mysqli_error($connection));
            }

        // Include the FPDF library (you need to download and include it in your project)
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
        $pdf->Cell(91, 15, /*'DATE FILED: ' .*/ $date_filed, 0, 0);
        $pdf->Cell(0, 15, /*'DEPARTMENT: ' .*/ $user_data['department'], 0, 1);
        $pdf->Cell(20, 10, '', 0, 0);
        $pdf->Cell(0, 1, /*'NAME: ' .*/ $user_data['firstname'] . ' ' . $user_data['lastname'], 0, 1);
        $pdf->Cell(96, 10, '', 0, 0);
        $pdf->Cell(0, 15, /*'CONTACT NUMBER WHILE ON LEAVE: ' .*/ $user_data['contactnumber'], 0, 1);
        $pdf->Cell(21, 10, '', 0, 0);
        $pdf->Cell(81, 16, /*'DATE/S OF REQUESTED LEAVE: FROM ' .*/ $leave_from, 0, 0);
        $pdf->Cell(0, 16, $leave_to, 0, 1);
        $pdf->Cell(102, 10, '', 0, 0);
        $pdf->Cell(0, 1, /*'NUMBER OF WORKING DAYS COVERED: ' .*/ $working_days_covered, 0, 1);
        $pdf->Cell(46, 10, '', 0, 0);
        if ($leave_type == "Others") {
            $pdf->Cell(0, 14, "Others ", 0, 1);
            $pdf->Cell(57, 0, '', 0, 0);
            $pdf->Cell(0, 1, /*"Others: " */ $leave_type_others, 0, 1);
        } else {
            $pdf->Cell(0, 14, $leave_type, 0, 1);
        }
        $pdf->Cell(3, 10, '', 0, 0);
        $pdf->Cell(0, 42, /*'REASON FOR LEAVE: ' .*/ $reason, 0, 1);

   
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

   <!-- Sidebar -->
   <div class="sidebar" id="sidebar">
    <a href="/mapecon/User Interface/User Leave Home.php" class="home-sidebar"><i class="fa fa-home"></i> Home</a>
    <span class="leave-label">NAVIGATE</span>
    <a href="/mapecon/User Interface/User Leave Form.php" id="active"><i class="fa fa-file-text-o"></i>Leave Application</a>
    <a href="/mapecon/User Interface/User Leave History.php"><i class="fa fa-file-word-o"></i> Leave History</a>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay" onclick="closeNav()"></div>
  <div class="leave-application">
    <h2>New Leave Application</h2>
    <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm()">
        <label for="leave-type">Leave Type:</label>
        <div class="leave-type">
            <select name="leave-type" id="leave-type">
                <option value="">Select</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Compensatory Off">Compensatory Off</option>
                <option value="Leave Without Pay">Leave Without Pay</option>
                <option value="Privilege Leave">Privilege Leave</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Vacation Leave">Vacation Leave</option>
                <option value="Others">Others</option>
            </select>
        </div>
        <div class="others" id="others-container" style="display: none;">
            <label for="others">Others:</label>
            <input type="others" id="others" name="others">
        </div>
        <div class="date-range">
            <div class="from-date">
                <label for="from-date">From Date:</label>
                <input type="date" name="from-date" id="from-date">
            </div>
            <div class="to-date">
                <label for="to-date">To Date:</label>
                <input type="date" name="to-date" id="to-date">
            </div>
            <div class="num-of-days">
                <label for="numofDays">Days covered:</label>
                <input type="number" id="numofDays" name="numofDays">
            </div>
        </div>
        <label for="reason" class="reason-label">Reason:</label>
        <div class="reason">
            <textarea name="reason" id="reason" cols="30" rows="10"></textarea>
        </div>
        <div class="buttons">
            <button type="button" onclick="window.location.href='/mapecon/User Interface/User Leave Home.php';">Cancel</button>
            <!--<button type="submit" name="pdf-btn" id="pdf-btn">Save as PDF</button> -->
            <button type="submit" id="submit-btn">Submit to HR</button>
        </div>
        <input type="hidden" id="date_filed" name="date_filed" value="<?php echo date('Y-m-d'); ?>">
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
    
    document.getElementById("date-time").innerHTML = "Today is " +  date + " | " + time;
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
