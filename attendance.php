<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is an employee or manager
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'employee' && $_SESSION['role'] !== 'manager')) {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$attendance_records = [];

// Handle clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $employee_id = $_SESSION['user_id'];
  $current_time = date('Y-m-d H:i:s');
  $current_date = date('Y-m-d');

  if (isset($_POST['clock_in'])) {
    // Check if already clocked in for today
    $stmt = $conn->prepare("SELECT attendance_id FROM attendance WHERE employee_id = ? AND DATE(clock_in_time) = ? AND clock_out_time IS NULL");
    $stmt->bind_param("is", $employee_id, $current_date);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $message = "You are already clocked in.";
    } else {
      $stmt = $conn->prepare("INSERT INTO attendance (employee_id, clock_in_time) VALUES (?, ?)");
      $stmt->bind_param("is", $employee_id, $current_time);
      if ($stmt->execute()) {
        $message = "Clocked in successfully at " . date('H:i:s');
      } else {
        $message = "Error clocking in: " . $conn->error;
      }
    }
    $stmt->close();
  } elseif (isset($_POST['clock_out'])) {
    // Find the latest clock-in without a clock-out
    $stmt = $conn->prepare("SELECT attendance_id, clock_in_time FROM attendance WHERE employee_id = ? AND clock_out_time IS NULL ORDER BY clock_in_time DESC LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($attendance_id, $clock_in_time);
    $stmt->fetch();
    $stmt->close();

    if ($attendance_id) {
      $stmt = $conn->prepare("UPDATE attendance SET clock_out_time = ?, hours_worked = TIMEDIFF(?, clock_in_time) WHERE attendance_id = ?");
      $stmt->bind_param("ssi", $current_time, $current_time, $attendance_id);
      if ($stmt->execute()) {
        $message = "Clocked out successfully at " . date('H:i:s');
      } else {
        $message = "Error clocking out: " . $conn->error;
      }
    } else {
      $message = "You need to clock in first.";
    }
    $stmt->close();
  }
}

// Fetch attendance records for the current employee or all for manager
if ($_SESSION['role'] === 'manager') {
  $stmt = $conn->prepare("SELECT u.username, a.clock_in_time, a.clock_out_time, a.hours_worked FROM attendance a JOIN users u ON a.employee_id = u.user_id ORDER BY a.clock_in_time DESC");
} else {
  $stmt = $conn->prepare("SELECT u.username, a.clock_in_time, a.clock_out_time, a.hours_worked FROM attendance a JOIN users u ON a.employee_id = u.user_id WHERE a.employee_id = ? ORDER BY a.clock_in_time DESC");
  $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $attendance_records[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Attendance Management</title>
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
      <a href="Hagerbet-login.html" class="login" target="_blank">Login</a>
      <a href="profile.php" class="profile">Profile</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </header>

  <section class="attendance-system">
    <h2>Attendance Management</h2>
    <?php if (!empty($message)): ?>
    <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'employee'): ?>
    <div class="clock-buttons">
      <form action="attendance.php" method="POST" style="display: inline;">
        <button type="submit" name="clock_in">Clock In</button>
      </form>
      <form action="attendance.php" method="POST" style="display: inline;">
        <button type="submit" name="clock_out">Clock Out</button>
      </form>
    </div>
    <?php endif; ?>

    <h3>Your Attendance Records</h3>
    <table>
      <thead>
        <tr>
          <?php if ($_SESSION['role'] === 'manager'): ?>
          <th>Employee</th>
          <?php endif; ?>
          <th>Clock In</th>
          <th>Clock Out</th>
          <th>Hours Worked</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($attendance_records)): ?>
        <tr>
          <td colspan="<?php echo ($_SESSION['role'] === 'manager' ? '4' : '3'); ?>">No attendance records found.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($attendance_records as $record): ?>
        <tr>
          <?php if ($_SESSION['role'] === 'manager'): ?>
          <td><?php echo htmlspecialchars($record['username']); ?></td>
          <?php endif; ?>
          <td><?php echo htmlspecialchars($record['clock_in_time']); ?></td>
          <td><?php echo htmlspecialchars($record['clock_out_time'] ?? 'N/A'); ?></td>
          <td><?php echo htmlspecialchars($record['hours_worked'] ?? 'N/A'); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($_SESSION['role'] === 'manager'): ?>
    <h3>Attendance Reports (Manager Only)</h3>
    <form action="attendance_report.php" method="GET">
      <label for="report_start_date">Start Date:</label>
      <input type="date" id="report_start_date" name="start_date" required>
      <label for="report_end_date">End Date:</label>
      <input type="date" id="report_end_date" name="end_date" required>
      <button type="submit">Generate Report</button>
    </form>
    <?php endif; ?>
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