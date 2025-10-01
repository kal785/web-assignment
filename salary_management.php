<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$employees = [];
$payroll_records = [];

// Fetch all employees for salary calculation
$stmt = $conn->prepare("SELECT user_id, username, email FROM users WHERE role = 'employee'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $employees[] = $row;
}
$stmt->close();

// Handle salary calculation and payroll generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_payroll'])) {
  $employee_id = $_POST['employee_id'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $hourly_rate = $_POST['hourly_rate']; // This should ideally come from employee settings
  $bonus = $_POST['bonus'] ?? 0;

  if (empty($employee_id) || empty($start_date) || empty($end_date) || empty($hourly_rate)) {
    $message = "Please fill in all required fields for payroll generation.";
  } elseif ($hourly_rate <= 0) {
    $message = "Hourly rate must be positive.";
  } else {
    // Calculate total hours worked for the period
    $stmt = $conn->prepare("
            SELECT SUM(TIME_TO_SEC(hours_worked)) AS total_seconds_worked
            FROM attendance
            WHERE employee_id = ? AND DATE(clock_in_time) BETWEEN ? AND ?
        ");
    $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
    $stmt->execute();
    $stmt->bind_result($total_seconds_worked);
    $stmt->fetch();
    $stmt->close();

    $total_hours_worked = $total_seconds_worked / 3600;
    $gross_salary = $total_hours_worked * $hourly_rate + $bonus;

    // Insert into payroll records
    $stmt = $conn->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, total_hours, hourly_rate, bonus, gross_salary) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdddd", $employee_id, $start_date, $end_date, $total_hours_worked, $hourly_rate, $bonus, $gross_salary);

    if ($stmt->execute()) {
      $message = "Payroll generated successfully for employee ID " . $employee_id . ". Gross Salary: $" . number_format($gross_salary, 2);
    } else {
      $message = "Error generating payroll: " . $conn->error;
    }
    $stmt->close();
  }
}

// Fetch payroll records
$stmt = $conn->prepare("SELECT p.*, u.username FROM payroll p JOIN users u ON p.employee_id = u.user_id ORDER BY p.pay_period_end DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $payroll_records[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Salary Management</title>
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

  <section class="salary-management">
    <h2>Salary Management (Manager Only)</h2>
    <?php if (!empty($message)): ?>
    <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3>Generate Payroll</h3>
    <form action="salary_management.php" method="POST">
      <label for="employee_id">Select Employee:</label>
      <select id="employee_id" name="employee_id" required>
        <option value="">--Select Employee--</option>
        <?php foreach ($employees as $emp): ?>
        <option value="<?php echo $emp['user_id']; ?>"><?php echo htmlspecialchars($emp['username']); ?></option>
        <?php endforeach; ?>
      </select><br><br>

      <label for="start_date">Pay Period Start:</label>
      <input type="date" id="start_date" name="start_date" required><br><br>

      <label for="end_date">Pay Period End:</label>
      <input type="date" id="end_date" name="end_date" required><br><br>

      <label for="hourly_rate">Hourly Rate ($):</label>
      <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" required><br><br>

      <label for="bonus">Bonus ($):</label>
      <input type="number" id="bonus" name="bonus" step="0.01" min="0" value="0"><br><br>

      <button type="submit" name="generate_payroll">Generate Payroll</button>
    </form>

    <h3>Payroll Records</h3>
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Pay Period</th>
          <th>Total Hours</th>
          <th>Hourly Rate</th>
          <th>Bonus</th>
          <th>Gross Salary</th>
          <th>Generated On</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($payroll_records)): ?>
        <tr>
          <td colspan="7">No payroll records found.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($payroll_records as $record): ?>
        <tr>
          <td><?php echo htmlspecialchars($record['username']); ?></td>
          <td>
            <?php echo htmlspecialchars($record['pay_period_start']) . ' to ' . htmlspecialchars($record['pay_period_end']); ?>
          </td>
          <td><?php echo number_format($record['total_hours'], 2); ?></td>
          <td>$<?php echo number_format($record['hourly_rate'], 2); ?></td>
          <td>$<?php echo number_format($record['bonus'], 2); ?></td>
          <td>$<?php echo number_format($record['gross_salary'], 2); ?></td>
          <td><?php echo htmlspecialchars($record['generated_at']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
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