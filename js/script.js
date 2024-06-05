if (localStorage.getItem('isLoggedInApprover') === 'true') {
    // Redirect the user to the Approver interface
    window.location.href = '../Approver Interface/Approver home.php';
} else {
    // If not logged in, proceed with regular login process
    // Example login code here...

    // Assuming login is successful and the user is an Approver
    if (userStatus === 'Approver') {
        // Set a flag indicating the user is logged in as an Approver
        localStorage.setItem('isLoggedInApprover', 'true');

        // Redirect the user to the Approver interface
        window.location.href = '../Approver Interface/Approver home.php';
    } else {
        // Handle other login scenarios if needed
    }
}