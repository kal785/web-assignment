<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$about_content = '';

// Fetch existing about content
$stmt = $conn->prepare("SELECT content FROM restaurant_info WHERE section = 'about_us' LIMIT 1");
$stmt->execute();
$stmt->bind_result($about_content);
$stmt->fetch();
$stmt->close();

// Handle updating about content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
  $new_content = $_POST['about_content'];

  if (empty($about_content)) {
    // Insert if no content exists
    $stmt = $conn->prepare("INSERT INTO restaurant_info (section, content) VALUES ('about_us', ?)");
    $stmt->bind_param("s", $new_content);
  } else {
    // Update if content exists
    $stmt = $conn->prepare("UPDATE restaurant_info SET content = ? WHERE section = 'about_us'");
    $stmt->bind_param("s", $new_content);
  }

  if ($stmt->execute()) {
    $message = "About Us content updated successfully!";
    $about_content = $new_content; // Update local variable
  } else {
    $message = "Error updating About Us content: " . $conn->error;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - About Us Editor</title>
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

  <section class="about-editor">
    <h2>About Us Content Editor (Manager Only)</h2>
    <?php if (!empty($message)): ?>
      <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="about_editor.php" method="POST">
      <label for="about_content">About Us Content:</label><br>
      <textarea id="about_content" name="about_content" rows="15"
        cols="80"><?php echo htmlspecialchars($about_content); ?></textarea><br><br>
      <button type="submit" name="update_about">Update About Us</button>
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