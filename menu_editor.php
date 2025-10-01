<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
  header("Location: Hagerbet-login.html");
  exit();
}

$message = '';
$menu_items = [];

// Handle adding/editing/deleting menu items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_menu_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url']; // For simplicity, using URL. In real app, handle file uploads.

    if (empty($name) || empty($description) || empty($price) || empty($category)) {
      $message = "Please fill in all required fields.";
    } elseif ($price <= 0) {
      $message = "Price must be positive.";
    } else {
      $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_url);
      if ($stmt->execute()) {
        $message = "Menu item added successfully!";
      } else {
        $message = "Error adding menu item: " . $conn->error;
      }
      $stmt->close();
    }
  } elseif (isset($_POST['edit_menu_item'])) {
    $item_id = $_POST['item_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'];

    if (empty($name) || empty($description) || empty($price) || empty($category) || empty($item_id)) {
      $message = "Please fill in all required fields.";
    } elseif ($price <= 0) {
      $message = "Price must be positive.";
    } else {
      $stmt = $conn->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, category = ?, image_url = ? WHERE item_id = ?");
      $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_url, $item_id);
      if ($stmt->execute()) {
        $message = "Menu item updated successfully!";
      } else {
        $message = "Error updating menu item: " . $conn->error;
      }
      $stmt->close();
    }
  } elseif (isset($_POST['delete_menu_item'])) {
    $item_id = $_POST['item_id'];
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    if ($stmt->execute()) {
      $message = "Menu item deleted successfully!";
    } else {
      $message = "Error deleting menu item: " . $conn->error;
    }
    $stmt->close();
  }
}

// Fetch all menu items
$stmt = $conn->prepare("SELECT * FROM menu_items ORDER BY category, name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $menu_items[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagerbet - Menu Editor</title>
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

  <section class="menu-editor">
    <h2>Menu Editor (Manager Only)</h2>
    <?php if (!empty($message)): ?>
      <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3>Add New Menu Item</h3>
    <form action="menu_editor.php" method="POST">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" required><br><br>

      <label for="description">Description:</label><br>
      <textarea id="description" name="description" rows="3" cols="50" required></textarea><br><br>

      <label for="price">Price ($):</label>
      <input type="number" id="price" name="price" step="0.01" min="0" required><br><br>

      <label for="category">Category:</label>
      <input type="text" id="category" name="category" required><br><br>

      <label for="image_url">Image URL:</label>
      <input type="text" id="image_url" name="image_url"><br><br>

      <button type="submit" name="add_menu_item">Add Item</button>
    </form>

    <h3>Existing Menu Items</h3>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Category</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($menu_items)): ?>
          <tr>
            <td colspan="6">No menu items found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($menu_items as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td><?php echo htmlspecialchars($item['description']); ?></td>
              <td>$<?php echo number_format($item['price'], 2); ?></td>
              <td><?php echo htmlspecialchars($item['category']); ?></td>
              <td>
                <?php if (!empty($item['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                    style="width: 50px; height: 50px; object-fit: cover;">
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td>
                <form action="menu_editor.php" method="POST" style="display: inline;">
                  <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                  <button type="button"
                    onclick="editMenuItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">Edit</button>
                  <button type="submit" name="delete_menu_item"
                    onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Edit Form (hidden by default) -->
    <div id="editMenuItemForm" class="hidden">
      <h3>Edit Menu Item</h3>
      <form action="menu_editor.php" method="POST">
        <input type="hidden" id="edit_item_id" name="item_id">
        <label for="edit_name">Name:</label>
        <input type="text" id="edit_name" name="name" required><br><br>

        <label for="edit_description">Description:</label><br>
        <textarea id="edit_description" name="description" rows="3" cols="50" required></textarea><br><br>

        <label for="edit_price">Price ($):</label>
        <input type="number" id="edit_price" name="price" step="0.01" min="0" required><br><br>

        <label for="edit_category">Category:</label>
        <input type="text" id="edit_category" name="category" required><br><br>

        <label for="edit_image_url">Image URL:</label>
        <input type="text" id="edit_image_url" name="image_url"><br><br>

        <button type="submit" name="edit_menu_item">Update Item</button>
        <button type="button"
          onclick="document.getElementById('editMenuItemForm').classList.add('hidden');">Cancel</button>
      </form>
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

  <script href="script.js"></script>

</body>

</html>