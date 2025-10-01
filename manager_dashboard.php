<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Manager Dashboard</title>
  <link rel="stylesheet" href="hagerBet.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <header class="other">
    <div class="left-header">
      <p>&#8962;</p>
      <p>HagerBet</p>
    </div>
    <div class="right-header">
      <a href="hagerBet.html" class="home" target="_blank">Home</a>
      <a href="aboutHagerBet.html" class="about" target="_blank">About</a>
      <a href="menuHagerBet.html" class="menu" target="_blank">Menu</a>
      <a href="profile.php" class="profile">Profile</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </header>

  <section class="manager-dashboard">
    <h2>Welcome, Manager <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Here you can manage various aspects of Hagerbet Restaurant.</p>

    <div class="dashboard-links">
      <h3>Restaurant Management</h3>
      <ul>
        <li><a href="menu_editor.php">Manage Menu</a></li>
        <li><a href="about_editor.php">Edit About Us Page</a></li>
        <li><a href="feedback.php">View Customer Feedback</a></li>
      </ul>

      <h3>Employee Management</h3>
      <ul>
        <li><a href="attendance.php">View Employee Attendance</a></li>
        <li><a href="salary_management.php">Manage Salaries & Payroll</a></li>
      </ul>

      <h3>Reservations</h3>
      <ul>
        <li><a href="manage_reservations.php">Manage Customer Reservations</a></li>
      </ul>
    </div>
  </section>

  <footer>
    <p>Contact us on</p>
    <div style="justify-content: space-around">
      <a href="https://www.facebook.com/yourpage" target="_blank" aria-label="Facebook">
        <i class="fab fa-facebook-f"></i>
      </a>
      <a href="https://www.instagram.com/yourprofile" target="_blank" aria-label="Instagram">
        <i class="fab fa-instagram"></i>
      </a>
      <a href="https://twitter.com/yourprofile" target="_blank" aria-label="Twitter">
        <i class="fab fa-twitter"></i>
      </a>
    </div>
    <p>&copy; 2025 Hager Bet Restaurant. All Rights Reserved.</p>
  </footer>
</body>

</html>