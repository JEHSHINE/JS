<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers - JEH Store Admin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="dashboard">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>JEH STORE</h2>
        <p>Administrator Panel</p>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="icon">📊</span> Dashboard</a>
        <a href="products.php"><span class="icon">📦</span> Products</a>
        <a href="categories.php"><span class="icon">📁</span> Categories</a>
        <a href="orders.php"><span class="icon">📋</span> Orders</a>
        <a href="customers.php" class="active"><span class="icon">👥</span> Customers</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 20px;">
        <a href="javascript:void(0)" onclick="adminLogout()"><span class="icon">🚪</span> Logout</a>
      </nav>
    </aside>

    <main class="main-content">
      <div id="alertContainer"></div>
      <div class="page-header">
        <h1>Customers</h1>
        <div class="header-actions">
          <button class="btn btn-secondary" onclick="loadCustomers()">🔄 Refresh</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Customer Overview</h2>
        </div>
        <div class="card-body">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Customer Name</th>
                  <th>Orders</th>
                  <th>Total Spent</th>
                </tr>
              </thead>
              <tbody id="customersTableBody">
                <tr><td colspan="4"><div class="loading"><div class="spinner"></div>Loading customers...</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      loadCustomers();
    });
  </script>
</body>
</html>