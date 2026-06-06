<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JEH Store Admin Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
  <div class="login-container">
    <h1>JEH STORE</h1>
    <p class="subtitle">Administrator Panel</p>
    <div id="alertContainer"></div>
    <div id="errorMessage" class="error-message"></div>
    <form id="loginForm">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required placeholder="Enter admin username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter password">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <p style="text-align:center; margin-top: 16px; font-size: 13px; color: var(--gray);">
      Secure admin access only
    </p>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;
      const errorEl = document.getElementById('errorMessage');

      try {
        const result = await adminLogin(username, password);
        if (result.token) {
          window.location.href = 'dashboard.php';
        } else {
          errorEl.textContent = result.error || 'Login failed';
          errorEl.style.display = 'block';
        }
      } catch (err) {
        errorEl.textContent = err.message;
        errorEl.style.display = 'block';
      }
    });
  </script>
</body>
</html>