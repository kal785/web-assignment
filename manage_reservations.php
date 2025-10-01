<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$reservations = [];

// Handle reservation status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reservation_status'])) {
  $reservation_id = $_POST['reservation_id'];
  $new_status = $_POST['new_status'];

  $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
  $stmt->bind_param("si", $new_status, $reservation_id);
  if ($stmt->execute()) {
    $message = "Reservation status updated successfully!";
    // Optionally send email notification to customer about status change
  } else {
    $message = "Error updating reservation status: " . $conn->error;
  }
  $stmt->close();
}

// Fetch all reservations
$stmt = $conn->prepare("SELECT r.*, u.username FROM reservations r JOIN users u ON r.user_id = u.user_id ORDER BY r.reservation_date DESC, r.reservation_time DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $reservations[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Manage Reservations</title>
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

  <section class="manage-reservations">
    <h2>Manage Customer Reservations (Manager Only)</h2>
    <?php if (!empty($message)): ?>
    <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Reservation ID</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Time</th>
          <th>Guests</th>
          <th>Requests</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reservations)): ?>
        <tr>
          <td colspan="8">No reservations found.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($reservations as $res): ?>
        <tr>
          <td><?php echo htmlspecialchars($res['reservation_id']); ?></td>
          <td><?php echo htmlspecialchars($res['username']); ?></td>
          <td><?php echo htmlspecialchars($res['reservation_date']); ?></td>
          <td><?php echo htmlspecialchars($res['reservation_time']); ?></td>
          <td><?php echo htmlspecialchars($res['number_of_guests']); ?></td>
          <td><?php echo htmlspecialchars($res['special_requests']); ?></td>
          <td><?php echo htmlspecialchars($res['status']); ?></td>
          <td>
            <form action="manage_reservations.php" method="POST">
              <input type="hidden" name="reservation_id" value="<?php echo $res['reservation_id']; ?>">
              <select name="new_status">
                <option value="pending" <?php echo ($res['status'] == 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="confirmed" <?php echo ($res['status'] == 'confirmed' ? 'selected' : ''); ?>>Confirmed
                </option>
                <option value="cancelled" <?php echo ($res['status'] == 'cancelled' ? 'selected' : ''); ?>>Cancelled
                </option>
                <option value="completed" <?php echo ($res['status'] == 'completed' ? 'selected' : ''); ?>>Completed
                </option>
              </select>
              <button type="submit" name="update_reservation_status">Update</button>
            </form>
          </td>
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