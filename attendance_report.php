<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}

$report_data = [];
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!empty($start_date) && !empty($end_date)) {
  $stmt = $conn->prepare("
        SELECT
            u.username,
            DATE(a.clock_in_time) AS attendance_date,
            MIN(a.clock_in_time) AS first_clock_in,
            MAX(a.clock_out_time) AS last_clock_out,
            TIMEDIFF(MAX(a.clock_out_time), MIN(a.clock_in_time)) AS total_shift_duration,
            SUM(TIME_TO_SEC(a.hours_worked)) AS total_hours_worked_sec
        FROM attendance a
        JOIN users u ON a.employee_id = u.user_id
        WHERE DATE(a.clock_in_time) BETWEEN ? AND ?
        GROUP BY u.username, attendance_date
        ORDER BY attendance_date DESC, u.username ASC
    ");
  $stmt->bind_param("ss", $start_date, $end_date);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $report_data[] = $row;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Attendance Report</title>
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

  <section class="attendance-report">
    <h2>Attendance Report for <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
    </h2>

    <?php if (empty($report_data)): ?>
      <p>No attendance data available for the selected date range.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>First Clock In</th>
            <th>Last Clock Out</th>
            <th>Total Shift Duration</th>
            <th>Total Hours Worked</th>
            <th>Tardiness/Absence</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($report_data as $record):
            $early_shift_start = strtotime(date('Y-m-d', strtotime($record['attendance_date'])) . ' 09:30:00');
            $night_shift_start = strtotime(date('Y-m-d', strtotime($record['attendance_date'])) . ' 16:45:00');
            $clock_in_timestamp = strtotime($record['first_clock_in']);
            $tardiness = '';

            // Determine expected shift start based on clock-in time
            if ($clock_in_timestamp <= strtotime(date('Y-m-d', strtotime($record['attendance_date'])) . ' 12:00:00')) { // Assume early shift if clocked in before noon
              if ($clock_in_timestamp > $early_shift_start) {
                $diff = $clock_in_timestamp - $early_shift_start;
                $tardiness = gmdate("H:i:s", $diff) . " late (Early Shift)";
              }
            } else { // Assume night shift
              if ($clock_in_timestamp > $night_shift_start) {
                $diff = $clock_in_timestamp - $night_shift_start;
                $tardiness = gmdate("H:i:s", $diff) . " late (Night Shift)";
              }
            }

            // Calculate total hours worked from seconds
            $total_hours_worked_seconds = $record['total_hours_worked_sec'];
            $hours = floor($total_hours_worked_seconds / 3600);
            $minutes = floor(($total_hours_worked_seconds % 3600) / 60);
            $seconds = $total_hours_worked_seconds % 60;
            $total_hours_worked_formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            ?>
            <tr>
              <td><?php echo htmlspecialchars($record['username']); ?></td>
              <td><?php echo htmlspecialchars($record['attendance_date']); ?></td>
              <td><?php echo htmlspecialchars($record['first_clock_in']); ?></td>
              <td><?php echo htmlspecialchars($record['last_clock_out'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($record['total_shift_duration'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($total_hours_worked_formatted); ?></td>
              <td><?php echo htmlspecialchars($tardiness); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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