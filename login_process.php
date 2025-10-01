<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['user_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $password_hash, $role, $email);
    $stmt->fetch();
    $stmt->close();

    if ($user_id && password_verify($password, $password_hash)) {
      $_SESSION['user_id'] = $user_id;
      $_SESSION['username'] = $username;
      $_SESSION['role'] = $role;
      $_SESSION['email'] = $email; // Store email for reservation emails

      if ($role === 'customer') {
        header("Location: reservations.php"); // Redirect customer to reservation page
      } elseif ($role === 'employee') {
        header("Location: attendance.php"); // Redirect employee to attendance page
      } elseif ($role === 'manager') {
        header("Location: manager_dashboard.php"); // Redirect manager to a dashboard
      }
      exit();
    } else {
      $_SESSION['login_error'] = "Invalid username or password.";
      header("Location: Hagerbet-login.php");
      exit();
    }
  } elseif (isset($_POST['employee_login'])) {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role, username, email FROM users WHERE user_id = ? AND (role = 'employee' OR role = 'manager')");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $password_hash, $role, $username, $email);
    $stmt->fetch();
    $stmt->close();

    if ($user_id && password_verify($password, $password_hash)) {
      $_SESSION['user_id'] = $user_id;
      $_SESSION['username'] = $username;
      $_SESSION['role'] = $role;
      $_SESSION['email'] = $email;

      if ($role === 'customer') {
        header("Location: reservations.php");
      } elseif ($role === 'employee') {
        header("Location: attendance.php");
      } elseif ($role === 'manager') {
        header("Location: manager_dashboard.php");
      }
      exit();
    } else {
      $_SESSION['login_error'] = "Invalid Employee ID or password.";
      header("Location: Hagerbet-login.php");
      exit();
    }
  } elseif (isset($_POST['user_signup'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email']; // Assuming email is added to signup form

    if (empty($username) || empty($password) || empty($email)) {
      $_SESSION['signup_error'] = "Please fill in all fields.";
      header("Location: Hagerbet-login.php");
      exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, 'customer')");
    $stmt->bind_param("sss", $username, $password_hash, $email);

    if ($stmt->execute()) {
      $_SESSION['signup_success'] = "Account created successfully! Please login.";
      header("Location: Hagerbet-login.php");
      exit();
    } else {
      $_SESSION['signup_error'] = "Error creating account: " . $conn->error;
      header("Location: Hagerbet-login.php");
      exit();
    }
    $stmt->close();
  } elseif (isset($_POST['employee_signup'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    if (empty($username) || empty($password) || empty($email)) {
      $_SESSION['signup_error'] = "Please fill in all fields.";
      header("Location: Hagerbet-login.php");
      exit();
    }
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $_SESSION['signup_error'] = "Username or email already exists.";
      $stmt->close();
      header("Location: Hagerbet-login.php");
      exit();
    }
    $stmt->close();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, 'employee')");
    $stmt->bind_param("sss", $username, $password_hash, $email);
    if ($stmt->execute()) {
      $_SESSION['signup_success'] = "Employee account created successfully! Please login.";
      header("Location: Hagerbet-login.php");
      exit();
    } else {
      $_SESSION['signup_error'] = "Error creating employee account: " . $conn->error;
      header("Location: Hagerbet-login.php");
      exit();
    }
    $stmt->close();
  }
}

header("Location: Hagerbet-login.php"); // Default redirect if no valid POST
exit();
?>