<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$user_data = [];
$user_reservations = [];

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $new_username = $_POST['username'];
  $new_email = $_POST['email'];
  $new_password = $_POST['password'];

  if (empty($new_username) || empty($new_email)) {
    $message = "Username and Email cannot be empty.";
  } else {
    $update_query = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    $params = [$new_username, $new_email, $_SESSION['user_id']];
    $types = "ssi";

    if (!empty($new_password)) {
      $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $update_query = "UPDATE users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?";
      $params = [$new_username, $new_email, $password_hash, $_SESSION['user_id']];
      $types = "sssi";
    }

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
      $_SESSION['username'] = $new_username;
      $_SESSION['email'] = $new_email;
      $user_data['username'] = $new_username;
      $user_data['email'] = $new_email;
      $message = "Profile updated successfully!";
    } else {
      $message = "Error updating profile: " . $conn->error;
    }
    $stmt->close();
  }
}

// Fetch user's reservation history (if customer)
if ($user_data['role'] === 'customer') {
  $stmt = $conn->prepare("SELECT reservation_date, reservation_time, number_of_guests, special_requests, status FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, reservation_time DESC");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $user_reservations[] = $row;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - My Profile</title>
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

  <section class="profile-management">
    <h2>My Profile</h2>
    <?php if (!empty($message)): ?>
    <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3>Update Profile Information</h3>
    <form action="profile.php" method="POST">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>"
        required><br><br>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>"
        required><br><br>

      <label for="password">New Password (leave blank to keep current):</label>
      <input type="password" id="password" name="password"><br><br>

      <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <?php if ($user_data['role'] === 'customer'): ?>
    <h3>My Reservation History</h3>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Guests</th>
          <th>Requests</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($user_reservations)): ?>
        <tr>
          <td colspan="5">No past reservations found.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($user_reservations as $res): ?>
        <tr>
          <td><?php echo htmlspecialchars($res['reservation_date']); ?></td>
          <td><?php echo htmlspecialchars($res['reservation_time']); ?></td>
          <td><?php echo htmlspecialchars($res['number_of_guests']); ?></td>
          <td><?php echo htmlspecialchars($res['special_requests']); ?></td>
          <td><?php echo htmlspecialchars($res['status']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if ($user_data['role'] === 'employee'): ?>
    <h3>My Attendance (Employee)</h3>
    <p>View your attendance records on the <a href="attendance.php">Attendance Management</a> page.</p>
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