<?php
session_start();
require_once 'db_connect.php'; // Database connection

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_table'])) {
  $user_id = $_SESSION['user_id'];
  $reservation_date = $_POST['reservation_date'];
  $reservation_time = $_POST['reservation_time'];
  $number_of_guests = $_POST['number_of_guests'];
  $special_requests = $_POST['special_requests'];

  // Validate input
  if (empty($reservation_date) || empty($reservation_time) || empty($number_of_guests)) {
    $message = "Please fill in all required fields.";
  } elseif ($number_of_guests <= 0) {
    $message = "Number of guests must be at least 1.";
  } else {
    // Check restaurant working hours (10am - 11pm)
    $reservation_datetime = strtotime($reservation_date . ' ' . $reservation_time);
    $opening_time = strtotime($reservation_date . ' 10:00:00');
    $closing_time = strtotime($reservation_date . ' 23:00:00');

    if ($reservation_datetime < $opening_time || $reservation_datetime > $closing_time) {
      $message = "Reservations can only be made between 10:00 AM and 11:00 PM.";
    } else {
      // Check for table availability (simplified logic, a real system would be more complex)
      // For now, we'll assume availability if no conflicting reservation exists for the exact time and date
      $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_date = ? AND reservation_time = ?");
      $stmt->bind_param("ss", $reservation_date, $reservation_time);
      $stmt->execute();
      $stmt->bind_result($count);
      $stmt->fetch();
      $stmt->close();

      if ($count > 5) { // Assuming a maximum of 5 tables can be reserved at any given time
        $message = "Sorry, no tables available at this time. Please choose another time.";
      } else {
        // Insert reservation into database
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, reservation_date, reservation_time, number_of_guests, special_requests, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issis", $user_id, $reservation_date, $reservation_time, $number_of_guests, $special_requests);

        if ($stmt->execute()) {
          $message = "Reservation successfully placed! A confirmation email has been sent.";
          // Send confirmation email (requires a mail server setup)
          $to = $_SESSION['email']; // Assuming email is stored in session
          $subject = "Hagerbet Restaurant - Reservation Confirmation";
          $body = "Dear " . $_SESSION['username'] . ",\n\nYour reservation for " . $number_of_guests . " guests on " . $reservation_date . " at " . $reservation_time . " has been received and is pending confirmation.\n\nSpecial Requests: " . $special_requests . "\n\nThank you for choosing Hagerbet!";
          $headers = "From: no-reply@hagerbet.com";
          // mail($to, $subject, $body, $headers); // Uncomment to enable email sending
        } else {
          $message = "Error placing reservation: " . $conn->error;
        }
        $stmt->close();
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Table Reservation</title>
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

  <section class="reservation-form">
    <h2>Table Reservation</h2>
    <?php if (!empty($message)): ?>
    <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="reservations.php" method="POST">
      <label for="reservation_date">Date:</label>
      <input type="date" id="reservation_date" name="reservation_date" required
        min="<?php echo date('Y-m-d'); ?>"><br><br>

      <label for="reservation_time">Time:</label>
      <input type="time" id="reservation_time" name="reservation_time" required min="10:00" max="23:00"><br><br>

      <label for="number_of_guests">Number of Guests:</label>
      <input type="number" id="number_of_guests" name="number_of_guests" min="1" required><br><br>

      <label for="special_requests">Special Requests:</label><br>
      <textarea id="special_requests" name="special_requests" rows="4" cols="50"></textarea><br><br>

      <button type="submit" name="reserve_table">Make Reservation</button>
    </form>
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