<?php
session_start();
require_once 'db_connect.php';

$message = '';
$feedback_entries = [];

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $message = "You must be logged in as a customer to submit feedback.";
  } else {
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'] ?? null;
    $comments = $_POST['comments'];

    if (empty($comments)) {
      $message = "Please provide your comments.";
    } else {
      $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, comments) VALUES (?, ?, ?)");
      $stmt->bind_param("iis", $user_id, $rating, $comments);
      if ($stmt->execute()) {
        $message = "Thank you for your feedback!";
      } else {
        $message = "Error submitting feedback: " . $conn->error;
      }
      $stmt->close();
    }
  }
}

// Fetch feedback entries (for managers)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager') {
  $stmt = $conn->prepare("SELECT f.*, u.username FROM feedback f JOIN users u ON f.user_id = u.user_id ORDER BY f.submitted_at DESC");
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $feedback_entries[] = $row;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Feedback</title>
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

  <section class="feedback-section">
    <h2>Customer Feedback</h2>
    <?php if (!empty($message)): ?>
      <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
      <h3>Submit Your Feedback</h3>
      <form action="feedback.php" method="POST">
        <label for="rating">Rating (1-5):</label>
        <input type="number" id="rating" name="rating" min="1" max="5"><br><br>

        <label for="comments">Comments/Suggestions:</label><br>
        <textarea id="comments" name="comments" rows="6" cols="50" required></textarea><br><br>

        <button type="submit" name="submit_feedback">Submit Feedback</button>
      </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
      <h3>All Customer Feedback (Manager Only)</h3>
      <table>
        <thead>
          <tr>
            <th>Customer</th>
            <th>Rating</th>
            <th>Comments</th>
            <th>Submitted At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($feedback_entries)): ?>
            <tr>
              <td colspan="4">No feedback entries found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($feedback_entries as $feedback): ?>
              <tr>
                <td><?php echo htmlspecialchars($feedback['username']); ?></td>
                <td><?php echo htmlspecialchars($feedback['rating'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                <td><?php echo htmlspecialchars($feedback['submitted_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
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