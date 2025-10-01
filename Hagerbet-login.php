<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="hagerBet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <title>Hagerbet login</title>
</head>

<body>
  <header class="loginpage other">
    <div class="left-header">
      <p>&#8962;</p>
      <p>HagerBet</p>
    </div>
    <div class="right-header">
      <a href="hagerBet.html" class="home" target="_blank">Home</a>
      <a href="aboutHagerBet.html" class="about" target="_blank">About</a>
      <a href="menuHagerBet.html" class="menu" target="_blank">Menu</a>
      <a href="Hagerbet-login.php" class="login">Login</a>
    </div>
  </header>
  <section class="login">
    <div class="container">
      <h2>Are you here to:</h2>
      <button onclick="showLogin('user')">Reserve a Table</button>
      <button onclick="showLogin('employee')">Employee Sign In</button>

      <?php
      session_start();
      if (isset($_SESSION['login_error'])) {
        echo '<p style="color: red;">' . $_SESSION['login_error'] . '</p>';
        unset($_SESSION['login_error']);
      }
      if (isset($_SESSION['signup_error'])) {
        echo '<p style="color: red;">' . $_SESSION['signup_error'] . '</p>';
        unset($_SESSION['signup_error']);
      }
      if (isset($_SESSION['signup_success'])) {
        echo '<p style="color: green;">' . $_SESSION['signup_success'] . '</p>';
        unset($_SESSION['signup_success']);
      }
      ?>

      <!-- User Login -->
      <div id="userLogin" class="hidden">
        <h3>User Login</h3>
        <form action="login_process.php" method="POST">
          <input type="text" placeholder="Username" name="username" required /><br />
          <input type="password" placeholder="Password" name="password" required /><br />
          <button type="submit" name="user_login">Login</button>
        </form>
        <p>
          New user?
          <a href="javascript:void(0);" onclick="showSignup('user')">Sign Up</a>
        </p>
      </div>

      <!-- User Signup -->
      <div id="userSignup" class="hidden">
        <h3>User Sign Up</h3>
        <form action="login_process.php" method="POST">
          <input type="text" placeholder="Username" name="username" required /><br />
          <input type="email" placeholder="Email" name="email" required /><br />
          <input type="password" placeholder="Password" name="password" required /><br />
          <button type="submit" name="user_signup">Sign Up</button>
        </form>
        <p>
          Already have an account?
          <a href="javascript:void(0);" onclick="showLogin('user')">Login</a>
        </p>
      </div>

      <!-- Employee Login -->
      <div id="employeeLogin" class="hidden">
        <h3>Employee Sign In</h3>
        <form action="login_process.php" method="POST">
          <input type="text" placeholder="Employee ID" name="employee_id" required /><br />
          <input type="password" placeholder="Password" name="password" required /><br />
          <button type="submit" name="employee_login">Login</button>
        </form>
        <p>
          New Employee?
          <a href="javascript:void(0);" onclick="showSignup('employee')">Employee Sign Up</a>
        </p>
      </div>

      <!-- Employee Signup -->
      <div id="employeeSignup" class="hidden">
        <h3>Employee Sign Up</h3>
        <form action="login_process.php" method="POST">
          <input type="text" placeholder="Username" name="username" required /><br />
          <input type="email" placeholder="Email" name="email" required /><br />
          <input type="password" placeholder="Password" name="password" required /><br />
          <button type="submit" name="employee_signup">Sign Up</button>
        </form>
        <p>
          Already have an account?
          <a href="javascript:void(0);" onclick="showLogin('employee')">Login</a>
        </p>
      </div>
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
</body>

</html>
<script src="script.js"></script>